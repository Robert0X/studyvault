<?php
require_once __DIR__ . '/../models/CpProblem.php';
require_once __DIR__ . '/../models/CpTemplate.php';
require_once __DIR__ . '/../models/User.php';

class CpController {
    private CpProblem $model;
    private User $userModel;
    private CpTemplate $templates;

    public function __construct() {
        $this->model     = new CpProblem();
        $this->userModel = new User();
        $this->templates = new CpTemplate();
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

        $dueReview        = $this->model->countDueReview($userId);
        $problemsetCached = $this->model->problemsetCount() > 0;
        $suggestions      = ($problemsetCached && !empty($cf['cf_rating']))
            ? $this->model->suggestNext($userId, (int) $cf['cf_rating'], 6)
            : [];
        $contests = $this->upcomingContests();

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
            if ($this->model->upsertSolved($userId, [
                'problem_url' => $url,
                'name'        => $p['name'] ?? ($p['contestId'] . $p['index']),
                'rating'      => $p['rating'] ?? null,
                'tags'        => !empty($p['tags']) ? implode(',', $p['tags']) : null,
                'platform'    => 'codeforces',
            ]) === 'inserted') {
                $imported++;
            }
        }
        $this->userModel->setCfSync($userId, $rating);
        log_activity('cp.sync', 'user', $userId);
        json_response(['success' => true, 'message' => "Sincronizado. {$imported} problemas nuevos importados.", 'rating' => $rating, 'imported' => $imported]);
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

    // ── Repaso espaciado ──
    public function scheduleReview(): void {
        requireLogin();
        csrf_verify();
        $ok = $this->model->scheduleReview((int) ($_POST['id'] ?? 0), (int) $_SESSION['user_id']);
        json_response(['success' => $ok, 'message' => $ok ? 'Programado para repaso.' : 'Error.']);
    }

    public function review(): void {
        requireLogin();
        $userId   = (int) $_SESSION['user_id'];
        $problems = $this->model->getDueReview($userId);
        require __DIR__ . '/../views/cp/review.php';
    }

    public function reviewSubmit(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $ok = $this->model->reviewProblem((int) ($_POST['id'] ?? 0), $userId, (int) ($_POST['quality'] ?? 0));
        json_response(['success' => $ok, 'remaining' => $this->model->countDueReview($userId)]);
    }

    // ── Sugerir-siguiente: sincronizar catálogo ──
    public function syncProblemset(): void {
        requireLogin();
        csrf_verify();
        $res = $this->cfGet('https://codeforces.com/api/problemset.problems');
        if (!$res || empty($res['problems'])) {
            json_response(['success' => false, 'message' => 'No se pudo obtener el catálogo de Codeforces.']);
        }
        $n = $this->model->cacheProblemset($res['problems']);
        json_response(['success' => true, 'message' => "Catálogo actualizado ({$n} problemas)."]);
    }

    // ── Plantillas / snippets ──
    public function templates(): void {
        requireLogin();
        $userId    = (int) $_SESSION['user_id'];
        $templates = $this->templates->getByUser($userId);
        require __DIR__ . '/../views/cp/templates.php';
    }

    public function storeTemplate(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $title  = trim($_POST['title'] ?? '');
        $code   = $_POST['code'] ?? '';
        if ($title === '' || trim($code) === '') {
            json_response(['success' => false, 'message' => 'Título y código son obligatorios.']);
        }
        $id = $this->templates->create($userId, $title, trim($_POST['language'] ?? 'cpp'), $code);
        json_response(['success' => true, 'id' => $id, 'message' => 'Plantilla guardada.']);
    }

    public function destroyTemplate(): void {
        requireLogin();
        csrf_verify();
        $ok = $this->templates->delete((int) ($_POST['id'] ?? 0), (int) $_SESSION['user_id']);
        json_response(['success' => $ok, 'message' => $ok ? 'Plantilla eliminada.' : 'Error.']);
    }

    private function upcomingContests(int $limit = 5): array {
        $list = $this->cfGet('https://codeforces.com/api/contest.list?gym=false');
        if (!$list) {
            return [];
        }
        $up = array_filter($list, fn($c) => ($c['phase'] ?? '') === 'BEFORE');
        usort($up, fn($a, $b) => ($a['startTimeSeconds'] ?? 0) <=> ($b['startTimeSeconds'] ?? 0));
        return array_slice(array_values($up), 0, $limit);
    }

    private function cfGet(string $url): ?array {
        $ctx = stream_context_create(['http' => ['timeout' => 20, 'user_agent' => 'StudyVault/1.0']]);
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
