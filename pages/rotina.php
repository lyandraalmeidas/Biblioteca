<?php session_start();

use App\Repositories\TaskRepository;

require_once __DIR__ . '/../vendor/autoload.php';

if (empty($_SESSION['user']['id'])) {
    $_SESSION['flash'] = 'Faça login para ver sua rotina.';
    header('Location: index.php');
    exit;
}

$userId = (int)($_SESSION['user']['id']);

$taskRepo = new TaskRepository();
$allTasks = $taskRepo->allByUser($userId);

usort($allTasks, function($a, $b){
    $ta = !empty($a['due_at']) ? strtotime($a['due_at']) : PHP_INT_MAX;
    $tb = !empty($b['due_at']) ? strtotime($b['due_at']) : PHP_INT_MAX;
    return $ta <=> $tb;
});

$today = date('Y-m-d');
$todayTasks = [];
$upcoming = [];
$noDate = [];

foreach ($allTasks as $t) {
    if (!empty($t['due_at'])) {
        $d = date('Y-m-d', strtotime($t['due_at']));
        if ($d === $today) {
            $todayTasks[] = $t;
        } elseif ($d > $today) {
            $upcoming[] = $t;
        } else {
            $todayTasks[] = $t;
        }
    } else {
        $noDate[] = $t;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rotina - Biblioteca</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>

    <main class="page-content">
        <div class="routine-wrapper">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10">
                    <div class="mb-4 text-center">
                        <h2 class="mb-0">Minha rotina</h2>
                        <p class="small text-muted">Uma visão rápida das tarefas de hoje e dos próximos dias</p>
                        <hr />
                    </div>

                    <?php if (!empty($_SESSION['flash'])): ?>
                        <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['flash']); unset($_SESSION['flash']); ?></div>
                    <?php endif; ?>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Hoje <small class="text-muted">(<?php echo date('d/m/Y'); ?>)</small></h5>
                                    <?php if (empty($todayTasks)): ?>
                                        <div class="alert alert-secondary">Nenhuma tarefa com prazo para hoje.</div>
                                    <?php else: ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($todayTasks as $t): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($t['title']); ?></strong>
                                                        <div class="small text-muted">
                                                            Criada: <?php
                                                                $created = $t['created_at'] ?? null;
                                                                echo htmlspecialchars($created ? date('d/m/Y H:i', strtotime($created)) : '-');
                                                            ?>
                                                            <?php if (!empty($t['due_at'])): ?>
                                                                <br>Prazo: <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($t['due_at']))); ?>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                    <div class="text-end">
                                                        <?php if (!empty($t['due_at']) && strtotime($t['due_at']) < time()): ?>
                                                            <span class="badge bg-danger">Atrasada</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <div class="mt-3 d-flex gap-2">
                                        <a href="tarefas.php" class="btn btn-outline-pink">Gerenciar tarefas</a>
                                        <a href="tempodeuso.php" class="btn btn-pink">Ver tempo de uso</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title">Próximos dias</h5>
                                    <?php if (empty($upcoming)): ?>
                                        <div class="alert alert-secondary">Nenhuma tarefa agendada nos próximos dias.</div>
                                    <?php else: ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($upcoming as $t): ?>
                                                <li class="list-group-item">
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($t['title']); ?></strong>
                                                            <div class="small text-muted">Prazo: <?php echo htmlspecialchars(date('d/m H:i', strtotime($t['due_at']))); ?></div>
                                                        </div>
                                                        <div class="text-end small text-muted">
                                                            <?php echo htmlspecialchars(date('d/m', strtotime($t['due_at']))); ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Sem prazo</h5>
                                    <?php if (empty($noDate)): ?>
                                        <div class="alert alert-secondary">Sem tarefas sem prazo.</div>
                                    <?php else: ?>
                                        <ul class="list-group list-group-flush">
                                            <?php foreach ($noDate as $t): ?>
                                                <li class="list-group-item">
                                                    <?php echo htmlspecialchars($t['title']); ?>
                                                    <div class="small text-muted">Criada: <?php echo htmlspecialchars($t['created_at'] ?? '-'); ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Adicionar tarefa rápida</h5>
                                <p class="small text-muted">Formulário rápido que utiliza a mesma lógica da página de tarefas.</p>
                                <form action="tarefas.php" method="post" class="row g-2">
                                    <input type="hidden" name="action" value="add">
                                    <div class="col-12 col-md-8">
                                        <input name="title" class="form-control" placeholder="Nova tarefa..." required>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <input name="due_at" type="datetime-local" class="form-control" />
                                    </div>
                                    <div class="col-12 col-md-1 d-grid">
                                        <button class="btn btn-pink" type="submit">OK</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
    </div>
    </div>
    </main>

    <?php include __DIR__ . '/../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
