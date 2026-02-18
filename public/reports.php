<?php
require __DIR__ . '/../app/helpers.php';
require_auth();

$catStmt = db()->prepare('SELECT id, name FROM categories ORDER BY name');
$catStmt->execute();
$categories = $catStmt->fetchAll();

render_header('Relatórios');
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h4">Relatórios</h1>
    <a class="btn btn-outline-secondary" href="/public/dashboard.php">Voltar</a>
</div>
<form class="card card-body" method="get" action="/public/report_export.php">
    <div class="row g-2">
        <div class="col-md-3"><label class="form-label">Data inicial</label><input class="form-control" type="date" name="start_date"></div>
        <div class="col-md-3"><label class="form-label">Data final</label><input class="form-control" type="date" name="end_date"></div>
        <div class="col-md-3"><label class="form-label">Categoria</label>
            <select class="form-select" name="category_id">
                <option value="0">Todas</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>"><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button class="btn btn-danger w-50" name="format" value="pdf">Gerar PDF</button>
            <button class="btn btn-success w-50" name="format" value="csv">Exportar CSV</button>
        </div>
    </div>
</form>
<?php render_footer(); ?>
