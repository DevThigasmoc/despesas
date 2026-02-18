<?php
require __DIR__ . '/../app/helpers.php';
require_auth();

$userId = current_user()['id'];
$start = $_GET['start_date'] ?? date('Y-m-01');
$end = $_GET['end_date'] ?? date('Y-m-d');

$stmtAdv = db()->prepare('SELECT COALESCE(SUM(amount),0) total FROM advances WHERE user_id = :user_id AND date BETWEEN :start AND :end');
$stmtAdv->execute(['user_id' => $userId, 'start' => $start, 'end' => $end]);
$totalAdv = (float)$stmtAdv->fetch()['total'];

$stmtExp = db()->prepare('SELECT COALESCE(SUM(amount),0) total FROM expenses WHERE user_id = :user_id AND date BETWEEN :start AND :end');
$stmtExp->execute(['user_id' => $userId, 'start' => $start, 'end' => $end]);
$totalExp = (float)$stmtExp->fetch()['total'];
$saldo = $totalAdv - $totalExp;

render_header('Dashboard');
?>
<div class="d-flex flex-wrap gap-2 mb-3">
    <a class="btn btn-primary" href="/public/expenses.php">Nova Despesa</a>
    <a class="btn btn-secondary" href="/public/advances.php">Novo Adiantamento</a>
    <a class="btn btn-outline-dark" href="/public/categories.php">Categorias</a>
    <a class="btn btn-success" href="/public/reports.php">Relatórios</a>
</div>
<form class="row g-2 mb-4" method="get">
    <div class="col-md-3"><input class="form-control" type="date" name="start_date" value="<?= e($start) ?>"></div>
    <div class="col-md-3"><input class="form-control" type="date" name="end_date" value="<?= e($end) ?>"></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>
<div class="row g-3">
    <div class="col-md-4"><div class="card"><div class="card-body"><h2 class="h6">Total Adiantamentos</h2><p class="h4"><?= e(format_money($totalAdv)) ?></p></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><h2 class="h6">Total Despesas</h2><p class="h4"><?= e(format_money($totalExp)) ?></p></div></div></div>
    <div class="col-md-4"><div class="card border-<?= $saldo < 0 ? 'danger' : 'success' ?>"><div class="card-body"><h2 class="h6">Saldo</h2><p class="h4 text-<?= $saldo < 0 ? 'danger' : 'success' ?>"><?= e(format_money($saldo)) ?></p></div></div></div>
</div>
<?php render_footer(); ?>
