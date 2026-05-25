<?php
require_once __DIR__ . '/../models/Flashcard.php';

class FlashcardController {
    private Flashcard $model;

    public function __construct() {
        $this->model = new Flashcard();
    }

    public function index(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $deck   = $_GET['deck'] ?? null;
        $cards  = $this->model->getByUser($userId, $deck);
        $stats  = $this->model->getStats($userId);
        require __DIR__ . '/../views/flashcards/index.php';
    }

    public function study(): void {
        requireLogin();
        $userId = (int) $_SESSION['user_id'];
        $deck   = $_GET['deck'] ?? null;
        $cards  = $this->model->getDue($userId, $deck, 50);
        require __DIR__ . '/../views/flashcards/study.php';
    }

    public function store(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];

        $front = trim($_POST['front'] ?? '');
        $back  = trim($_POST['back'] ?? '');
        if ($front === '' || $back === '') {
            json_response(['success' => false, 'message' => 'Anverso y reverso son obligatorios.']);
        }

        $id = $this->model->create([
            'user_id'    => $userId,
            'deck'       => in_array($_POST['deck'] ?? 'vocab', ['vocab', 'cp'], true) ? $_POST['deck'] : 'vocab',
            'front'      => $front,
            'back'       => $back,
            'example'    => trim($_POST['example'] ?? '') ?: null,
            'extra'      => trim($_POST['extra'] ?? '') ?: null,
            'source'     => 'manual',
            'cefr_level' => trim($_POST['cefr_level'] ?? '') ?: null,
        ]);
        log_activity('flashcard.create', 'flashcard', $id);
        json_response(['success' => true, 'id' => $id, 'message' => 'Tarjeta creada.']);
    }

    /** Crea una tarjeta a partir de una palabra buscada en el diccionario. */
    public function storeFromDictionary(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];

        $word = trim($_POST['word'] ?? '');
        $back = trim($_POST['back'] ?? '');
        if ($word === '' || $back === '') {
            json_response(['success' => false, 'message' => 'Faltan datos de la palabra.']);
        }

        $id = $this->model->create([
            'user_id' => $userId,
            'deck'    => 'vocab',
            'front'   => $word,
            'back'    => $back,
            'example' => trim($_POST['example'] ?? '') ?: null,
            'extra'   => trim($_POST['phonetic'] ?? '') ?: null,
            'source'  => 'dictionary',
        ]);
        log_activity('flashcard.create_dict', 'flashcard', $id);
        json_response(['success' => true, 'id' => $id, 'message' => "«{$word}» guardada como tarjeta."]);
    }

    public function review(): void {
        requireLogin();
        csrf_verify();
        $userId  = (int) $_SESSION['user_id'];
        $id      = (int) ($_POST['id'] ?? 0);
        $quality = (int) ($_POST['quality'] ?? 0);

        $ok = $this->model->review($id, $userId, $quality);
        json_response(['success' => $ok, 'message' => $ok ? 'Repaso registrado.' : 'No se encontró la tarjeta.']);
    }

    public function destroy(): void {
        requireLogin();
        csrf_verify();
        $userId = (int) $_SESSION['user_id'];
        $id     = (int) ($_POST['id'] ?? 0);
        $ok     = $this->model->delete($id, $userId);
        if ($ok) {
            log_activity('flashcard.delete', 'flashcard', $id);
        }
        json_response(['success' => $ok, 'message' => $ok ? 'Tarjeta eliminada.' : 'Error.']);
    }
}
