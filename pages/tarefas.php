<?php session_start();

use App\Repositories\TaskRepository;

require_once __DIR__ . '/../vendor/autoload.php';

// Require authentication
if (empty($_SESSION['user']['id'])) {
    $_SESSION['flash'] = 'Faça login para acessar suas tarefas.';
    header('Location: index.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

$taskRepo = new TaskRepository();
$tasks = $taskRepo->allByUser($userId);

$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['action']) && $_POST['action'] === 'add') {
        $title = trim($_POST['title'] ?? '');
        $dueAtRaw = trim($_POST['due_at'] ?? '');
        $dueAt = null;
        if ($dueAtRaw !== '') {
            $dueAtClean = str_replace('T', ' ', $dueAtRaw);
            if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $dueAtClean)) {
                $dueAtClean .= ':00';
            }
            $ts = strtotime($dueAtClean);
            if ($ts !== false) {
                $dueAt = date('Y-m-d H:i:s', $ts);
            } else {
                $dueAt = null;
            }
        }
        if ($title !== '') {
            $taskRepo->add($userId, $title, $dueAt);
            $flash = 'Tarefa adicionada.';
        } else {
            $flash = 'O título da tarefa não pode ficar vazio.';
        }
    }
    if (!empty($_POST['action']) && $_POST['action'] === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id !== null) {
            $taskRepo->delete($userId, (int)$id);
            $flash = 'Tarefa removida.';
        }
    }
    $_SESSION['flash'] = $flash;
    header('Location: tarefas.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarefas - Biblioteca</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <main class="page-content">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-8">
                    <div class="mb-4 text-center">
                        <h2 class="mb-0">Minhas tarefas</h2>
                        <hr />
                    </div>

                    <?php if (!empty($_SESSION['flash'])): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
                    <?php endif; ?>

                    <form action="tarefas.php" method="post" class="mb-4 task-add-form mx-auto">
                        <input type="hidden" name="action" value="add">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-7">
                                <input name="title" class="form-control" placeholder="Nova tarefa..." aria-label="Nova tarefa">
                            </div>
                            <div class="col-12 col-md-3">
                                <input name="due_at" type="datetime-local" class="form-control" aria-label="Data e hora" />
                            </div>
                            <div class="col-12 col-md-2 d-grid">
                                <button class="btn btn-pink" type="submit">Adicionar</button>
                            </div>
                        </div>
                    </form>

                    <?php if (empty($tasks)): ?>
                        <div class="alert alert-secondary">Nenhuma tarefa encontrada.</div>
                    <?php else: ?>
                        <div class="list-group mb-3">
                            <?php foreach ($tasks as $t): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($t['title']); ?></strong>
                                        <div class="small text-muted">
                                            Criada: <?php
                                                $created = $t['created_at'] ?? null;
                                                echo htmlspecialchars($created ? date('d/m/Y H:i', strtotime($created)) : '-');
                                            ?>
                                            <?php if (!empty($t['due_at'])): ?>
                                                <br />Prazo: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($t['due_at']))); ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <form method="post" action="tarefas.php" onsubmit="return confirm('Remover essa tarefa?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($t['id']); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
