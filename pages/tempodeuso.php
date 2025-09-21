<?php if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once __DIR__ . '/../vendor/autoload.php';
use App\Repositories\ReadingStatRepository;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tempo de Uso - Biblioteca</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Indie+Flower&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../CSS/style.css">
</head>
<body>
    <?php include '../partials/header.php'; ?>

    <main>
        <div class="page-content">
            <div class="container py-4">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-9">
                        <h2 class="mb-3 text-center"><i class="bi bi-clock-fill me-2"></i>Tempo de Uso</h2>

                        <div class="card mb-4">
                            <div class="card-body text-center">
                                <h5 class="card-title">Tempo total neste navegador</h5>
                                <div id="usage-total" style="font-size:2rem;font-weight:700;color:#2c3e50">00:00:00</div>
                                <div class="mt-3">
                                    <button id="reset-usage" class="btn btn-outline-danger btn-sm me-2"><i class="bi bi-arrow-counterclockwise"></i> Resetar</button>
                                    <button id="export-usage" class="btn btn-outline-secondary btn-sm"><i class="bi bi-download"></i> Exportar JSON</button>
                                </div>
                            </div>
                        </div>


                        <?php
                        $userId = $_SESSION['user']['id'] ?? null;
                        $dbTotal = 0; $dbDays = []; $topBooks = []; $allBooks = [];
                        if ($userId) {
                            try {
                                $repo = new ReadingStatRepository();
                                $dbTotal = $repo->totalSeconds((int)$userId);
                                $dbDays = $repo->lastDaysByDate((int)$userId, 7);
                                $topBooks = $repo->topBooks((int)$userId, 10);
                                $allBooks = $repo->listAllBooksWithUserTime((int)$userId);
                            } catch (Throwable $e) {
                                $dbTotal = 0; $dbDays = []; $topBooks = []; $allBooks = [];
                            }
                        }
                        ?>

                        <div class="card mb-4 border-success">
                            <div class="card-body">
                                <h5 class="card-title"><i class="bi bi-cloud-arrow-down-fill me-2"></i>Tempo de leitura salvo no banco</h5>
                                <?php if (!$userId): ?>
                                    <div class="alert alert-warning">Entre na sua conta para ver seu tempo de leitura sincronizado.</div>
                                <?php else: ?>
                                    <div class="row g-3">
                                        <div class="col-12 col-md-5">
                                            <div class="p-3 bg-light rounded border">
                                                <div class="text-muted small">Total (todos os livros)</div>
                                                <div style="font-size:1.6rem;font-weight:700;" id="db-total">00:00:00</div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-md-7">
                                            <div>
                                                <div class="text-muted small mb-2">Últimos 7 dias</div>
                                                <div id="db-history"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr />
                                    <div>
                                        <div class="text-muted small mb-2">Top livros por tempo</div>
                                        <div id="db-top-books"></div>
                                    </div>
                                    <hr />
                                    <div>
                                        <div class="text-muted small mb-2">Todos os livros (tempo total)</div>
                                        <div id="db-all-books"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card mb-4 bg-light">
                            <div class="card-body">
                                <h5 class="card-title">Dicas de uso saudável</h5>
                                <ul>
                                    <li>Faça pausas de 5–10 minutos a cada 50 minutos de leitura/uso.</li>
                                    <li>Aplique a regra 20-20-20: a cada 20 minutos, olhe para algo a 20 pés (6 m) durante 20 segundos.</li>
                                    <li>Use iluminação adequada e ajuste o brilho da tela.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="text-muted small">Os dados são armazenados localmente no seu navegador (localStorage). Não enviamos dados ao servidor.</div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include '../partials/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .usage-bar { height: 16px; background: linear-gradient(90deg,#4e73df,#1cc88a); border-radius: 6px; }
        .usage-bar-bg { background: #eee; border-radius: 6px; overflow: hidden; }
    </style>

    <script>
    // Tempo de uso - salva segundos totais e histórico diário no localStorage
    (function(){
        const KEY_TOTAL = 'usageSeconds';
        const KEY_HIST = 'usageHistory'; // array de {date: 'YYYY-MM-DD', seconds: n}
        const DISPLAY = document.getElementById('usage-total');
        const HIST = document.getElementById('usage-history'); // pode não existir
        const BTN_RESET = document.getElementById('reset-usage');
        const BTN_EXPORT = document.getElementById('export-usage');

        function fmt(sec){
            const h = Math.floor(sec/3600), m = Math.floor((sec%3600)/60), s = sec%60;
            return String(h).padStart(2,'0')+ ':' + String(m).padStart(2,'0') + ':' + String(s).padStart(2,'0');
        }

        function readTotal(){
            try{ return parseInt(localStorage.getItem(KEY_TOTAL),10)||0; }catch(e){return 0}
        }
        function writeTotal(n){ localStorage.setItem(KEY_TOTAL, String(n)); }

        function readHist(){
            try{ return JSON.parse(localStorage.getItem(KEY_HIST)) || []; }catch(e){ return []; }
        }
        function writeHist(arr){ localStorage.setItem(KEY_HIST, JSON.stringify(arr)); }

        // Atualiza display e histórico
        function render(){
            const total = readTotal();
            DISPLAY.textContent = fmt(total);
            const raw = readHist();
            // normalize
            const map = {};
            raw.forEach(it => { if(it && it.date){ map[it.date] = (map[it.date]||0) + (Number(it.seconds)||0); } });
            const days = [];
            for(let i=6;i>=0;i--){ const d=new Date(); d.setDate(d.getDate()-i); const ds=d.toISOString().slice(0,10); days.push({date: ds, seconds: map[ds]||0}); }
            const toShow = days.slice(1); // remove o primeiro (mais antigo)
            if (HIST) {
                const max = Math.max(1, ...toShow.map(d=>d.seconds));
                HIST.innerHTML = toShow.map(d => `
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small"> <div>${d.date.replace(/^\d{4}-/,'')}</div> <div>${fmt(d.seconds)}</div> </div>
                        <div class="usage-bar-bg mt-1"><div class="usage-bar" style="width:${Math.round(100*d.seconds/max)}%"></div></div>
                    </div>
                `).join('');
            }
        }

        // registra segundos passados desde que a aba ficou visível
        let tickInterval = null;
        function startTick(){
            if(tickInterval) return;
            let last = Date.now();
            tickInterval = setInterval(()=>{
                const now = Date.now();
                const delta = Math.round((now-last)/1000);
                if(delta>0){
                    // atualiza total
                    const total = readTotal() + delta;
                    writeTotal(total);
                    // adiciona ao histórico do dia atual
                    const hist = readHist();
                    const today = new Date().toISOString().slice(0,10);
                    hist.push({date: today, seconds: delta});
                    writeHist(hist);
                    render();
                    last = now;
                }
            }, 1000);
            // expose for reset
            window.__usageTimer = { reset: stopTick };
        }
        function stopTick(){ if(tickInterval){ clearInterval(tickInterval); tickInterval = null; } }

        // visibilidade da página
        document.addEventListener('visibilitychange', function(){ if(document.hidden) stopTick(); else startTick(); });
        // iniciar agora
        render();
        startTick();

        BTN_RESET.addEventListener('click', function(){ if(confirm('Deseja zerar o tempo de uso neste navegador?')){ writeTotal(0); writeHist([]); render(); } });
        BTN_EXPORT.addEventListener('click', function(){ const payload = { total: readTotal(), history: readHist() }; const blob = new Blob([JSON.stringify(payload, null, 2)], {type:'application/json'}); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = 'tempo_uso.json'; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); });

        // salvar periodicamente para evitar perdas
        window.addEventListener('beforeunload', stopTick);
    })();
    </script>

    <?php if ($userId): ?>
    <script>
    // Render dados do banco (fornecidos pelo PHP via JSON embutido)
    (function(){
        const total = <?php echo json_encode((int)$dbTotal); ?>;
    const days = <?php echo json_encode($dbDays, JSON_UNESCAPED_UNICODE); ?>;
    const top = <?php echo json_encode($topBooks, JSON_UNESCAPED_UNICODE); ?>;
    const allBooks = <?php echo json_encode($allBooks, JSON_UNESCAPED_UNICODE); ?>;

        function fmt(sec){ sec = Math.max(0, Math.floor(sec||0)); const h=Math.floor(sec/3600), m=Math.floor((sec%3600)/60), s=sec%60; return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(s).padStart(2,'0'); }
        const dbTotalEl = document.getElementById('db-total'); if (dbTotalEl) dbTotalEl.textContent = fmt(total);
        const histEl = document.getElementById('db-history');
        if (histEl) {
            const toShow = days.slice(1); // remove o primeiro (mais antigo)
            const max = Math.max(1, ...toShow.map(d=>d.seconds||0));
            histEl.innerHTML = toShow.map(d => `
                <div class="mb-2">
                    <div class="d-flex justify-content-between small"><div>${String(d.date).replace(/^\d{4}-/,'')}</div><div>${fmt(d.seconds||0)}</div></div>
                    <div class="usage-bar-bg mt-1"><div class="usage-bar" style="width:${Math.round(100*(d.seconds||0)/max)}%"></div></div>
                </div>
            `).join('');
        }
        const topEl = document.getElementById('db-top-books');
        if (topEl) {
            if (!top || !top.length) { topEl.innerHTML = '<div class="text-muted">Sem leituras registradas ainda.</div>'; return; }
            const max = Math.max(1, ...top.map(t=>t.seconds||0));
            topEl.innerHTML = top.map(t => `
                <div class="mb-2">
                    <div class="d-flex justify-content-between small"><div>${(t.title||('Livro #' + t.book_id))}</div><div>${fmt(t.seconds||0)}</div></div>
                    <div class="usage-bar-bg mt-1"><div class="usage-bar" style="width:${Math.round(100*(t.seconds||0)/max)}%"></div></div>
                </div>
            `).join('');
        }

        const allEl = document.getElementById('db-all-books');
        if (allEl) {
            if (!allBooks || !allBooks.length) { allEl.innerHTML = '<div class="text-muted">Sem itens cadastrados.</div>'; }
            else {
                const max = Math.max(1, ...allBooks.map(t=>t.seconds||0));
                allEl.innerHTML = allBooks.map(t => `
                    <div class="mb-2">
                        <div class="d-flex justify-content-between small"><div>${(t.title||('Livro #' + t.book_id))}</div><div>${fmt(t.seconds||0)}</div></div>
                        <div class="usage-bar-bg mt-1"><div class="usage-bar" style="width:${Math.round(100*(t.seconds||0)/max)}%"></div></div>
                    </div>
                `).join('');
            }
        }
    })();
    </script>
    <?php endif; ?>
</body>
</html>
