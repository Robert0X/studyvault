<?php
require_once __DIR__ . '/../models/CpProblem.php';
require_once __DIR__ . '/../models/User.php';

class CpController {
    private CpProblem $model;
    private User $userModel;

    public function __construct() {
        $this->model     = new CpProblem();
        $this->userModel = new User();
    }

    public function index(): void {
        requireLogin();
        $userId   = (int) $_SESSION['user_id'];
        $status   = $_GET['status'] ?? null;
        $search   = trim($_GET['q'] ?? '');
        $problems = $this->model->getByUser($userId, $status, $search);
        $stats    = $this->model->getStats($userId);
        $tags     = $this->model->tagBreakdown($userId);
        $cf       = $this->userModel->getCfInfo($userId);
        require __DIR__ . '/../views/cp/index.php';
    }

    public function saveHandle(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $handle = trim($_POST['cf_handle'] ?? '');
        if ($handle === '' || !preg_match('/^[A-Za-z0-9_\.\-]{1,64}$/', $handle)) {
            json_response(['success' => false, 'message' => 'Handle inválido.']);
        }
        $this->userModel->setCfHandle($userId, $handle);
        json_response(['success' => true, 'message' => 'Handle guardado.']);
    }

    /** Sincroniza problemas resueltos y rating desde la API pública de Codeforces. */
    public function sync(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $cf     = $this->userModel->getCfInfo($userId);
        $handle = $cf['cf_handle'] ?? '';

        if ($handle === '') {
            json_response(['success' => false, 'message' => 'Primero configura tu handle de Codeforces.']);
        }

        $info = $this->cfGet('https://codeforces.com/api/user.info?handles=' . urlencode($handle));
        if ($info === null) {
            json_response(['success' => false, 'message' => 'No se pudo conectar con Codeforces o el handle no existe.']);
        }
        $rating = $info[0]['rating'] ?? null;

        $subs = $this->cfGet('https://codeforces.com/api/user.status?handle=' . urlencode($handle) . '&from=1&count=3000');
        if ($subs === null) {
            json_response(['success' => false, 'message' => 'No se pudieron obtener tus envíos.']);
        }

        $seen = [];
        $imported = 0;
        foreach ($subs as $sub) {
            if (($sub['verdict'] ?? '') !== 'OK') {
                continue;
            }
            $p = $sub['problem'] ?? [];
            if (empty($p['contestId']) || empty($p['index'])) {
                continue;
            }
            $key = $p['contestId'] . '-' . $p['index'];
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;

            $url = "https://codeforces.com/problemset/problem/{$p['contestId']}/{$p['index']}";
            $res = $this->model->upsertSolved($userId, [
                'problem_url' => $url,
                'name'        => $p['name'] ?? ($p['contestId'] . $p['index']),
                'rating'      => $p['rating'] ?? null,
                'tags'        => !empty($p['tags']) ? implode(',', $p['tags']) : null,
                'platform'    => 'codeforces',
            ]);
            if ($res === 'inserted') {
                $imported++;
            }
        }

        $this->userModel->setCfSync($userId, $rating);
        log_activity('cp.sync', 'user', $userId);
        json_response([
            'success'  => true,
            'message'  => "Sincronizado. {$imported} problemas nuevos importados.",
            'rating'   => $rating,
            'imported' => $imported,
            'unique'   => count($seen),
        ]);
    }

    public function store(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $name   = trim($_POST['name'] ?? '');
        if ($name === '') {
            json_response(['success' => false, 'message' => 'El nombre es obligatorio.']);
        }
        $id = $this->model->create([
            'user_id'        => $userId,
            'problem_url'    => trim($_POST['problem_url'] ?? '') ?: null,
            'name'           => $name,
            'rating'         => ($r = (int) ($_POST['rating'] ?? 0)) > 0 ? $r : null,
            'tags'           => trim($_POST['tags'] ?? '') ?: null,
            'status'         => in_array($_POST['status'] ?? 'todo', ['todo', 'attempted', 'solved', 'upsolved'], true) ? $_POST['status'] : 'todo',
            'editorial_note' => trim($_POST['editorial_note'] ?? '') ?: null,
        ]);
        log_activity('cp.create', 'cp_problem', $id);
        json_response(['success' => true, 'id' => $id, 'message' => 'Problema agregado.']);
    }

    public function setStatus(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $ok = $this->model->setStatus((int) ($_POST['id'] ?? 0), $userId, $_POST['status'] ?? '');
        json_response(['success' => $ok, 'message' => $ok ? 'Estado actualizado.' : 'Error.']);
    }

    public function destroy(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $ok = $this->model->delete((int) ($_POST['id'] ?? 0), $userId);
        json_response(['success' => $ok, 'message' => $ok ? 'Problema eliminado.' : 'Error.']);
    }

    private function cfGet(string $url): ?array {
        $ctx = stream_context_create(['http' => ['timeout' => 15, 'user_agent' => 'StudyVault/1.0']]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) {
            return null;
        }
        $json = json_decode($resp, true);
        if (!is_array($json) || ($json['status'] ?? '') !== 'OK') {
            return null;
        }
        return $json['result'] ?? null;
    }
}
