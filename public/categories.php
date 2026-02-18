<?php
require __DIR__ . '/../app/helpers.php';
require_auth();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = trim((string)($_POST['name'] ?? ''));
        if ($name === '') {
            flash('danger', 'Nome da categoria é obrigatório.');
        } else {
            $stmt = db()->prepare('INSERT INTO categories (name) VALUES (:name)');
            $stmt->execute(['name' => $name]);
            flash('success', 'Categoria criada com sucesso.');
        }
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('DELETE FROM categories WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash('success', 'Categoria excluída.');
    }

    redirect('/public/categories.php');
}

$stmt = db()->prepare('SELECT id, name FROM categories ORDER BY name');
$stmt->execute();
$items = $stmt->fetchAll();

render_header('Categorias');
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h4">Categorias</h1>
    <a class="btn btn-outline-secondary" href="/public/dashboard.php">Voltar</a>
</div>
<form method="post" class="row g-2 mb-4">
    <?= csrf_input() ?>
    <input type="hidden" name="action" value="create">
    <div class="col-md-8"><input class="form-control" name="name" placeholder="Nome da categoria" required></div>
    <div class="col-md-4"><button class="btn btn-primary w-100">Adicionar</button></div>
</form>
<table class="table table-striped">
    <thead><tr><th>Nome</th><th class="text-end">Ações</th></tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= e($item['name']) ?></td>
            <td class="text-end">
                <form method="post" class="d-inline" onsubmit="return confirm('Excluir categoria?')">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                    <button class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php render_footer(); ?>
