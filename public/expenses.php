<?php
require __DIR__ . '/../app/helpers.php';
require_auth();
$userId = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $find = db()->prepare('SELECT receipt_path FROM expenses WHERE id = :id AND user_id = :user_id');
        $find->execute(['id' => $id, 'user_id' => $userId]);
        $old = $find->fetch();
        if ($old) {
            $del = db()->prepare('DELETE FROM expenses WHERE id = :id AND user_id = :user_id');
            $del->execute(['id' => $id, 'user_id' => $userId]);
            delete_receipt($old['receipt_path']);
            flash('success', 'Despesa excluída.');
        }
        redirect('/public/expenses.php');
    }

    $id = (int)($_POST['id'] ?? 0);
    $date = $_POST['date'] ?? '';
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $note = trim((string)($_POST['note'] ?? ''));
    $amount = money_to_decimal((string)($_POST['amount'] ?? '0'));

    if (!$date || $categoryId <= 0 || $note === '' || (float)$amount <= 0) {
        flash('danger', 'Preencha todos os campos obrigatórios da despesa.');
        redirect('/public/expenses.php' . ($id ? '?edit=' . $id : ''));
    }

    try {
        if ($action === 'create') {
            $upload = upload_receipt($_FILES['receipt'] ?? []);
            $stmt = db()->prepare('INSERT INTO expenses (date, category_id, note, amount, receipt_path, receipt_mime, user_id, created_at) VALUES (:date, :category_id, :note, :amount, :receipt_path, :receipt_mime, :user_id, NOW())');
            $stmt->execute([
                'date' => $date,
                'category_id' => $categoryId,
                'note' => $note,
                'amount' => $amount,
                'receipt_path' => $upload['path'],
                'receipt_mime' => $upload['mime'],
                'user_id' => $userId,
            ]);
            flash('success', 'Despesa criada com sucesso.');
        } elseif ($action === 'update') {
            $find = db()->prepare('SELECT receipt_path, receipt_mime FROM expenses WHERE id = :id AND user_id = :user_id');
            $find->execute(['id' => $id, 'user_id' => $userId]);
            $current = $find->fetch();
            if (!$current) {
                throw new RuntimeException('Despesa não encontrada.');
            }

            $path = $current['receipt_path'];
            $mime = $current['receipt_mime'];
            if (!empty($_FILES['receipt']['name'])) {
                $upload = upload_receipt($_FILES['receipt']);
                delete_receipt($path);
                $path = $upload['path'];
                $mime = $upload['mime'];
            }

            $stmt = db()->prepare('UPDATE expenses SET date = :date, category_id = :category_id, note = :note, amount = :amount, receipt_path = :receipt_path, receipt_mime = :receipt_mime WHERE id = :id AND user_id = :user_id');
            $stmt->execute([
                'date' => $date,
                'category_id' => $categoryId,
                'note' => $note,
                'amount' => $amount,
                'receipt_path' => $path,
                'receipt_mime' => $mime,
                'id' => $id,
                'user_id' => $userId,
            ]);
            flash('success', 'Despesa atualizada.');
        }
    } catch (Throwable $e) {
        flash('danger', $e->getMessage());
    }

    redirect('/public/expenses.php');
}

$filterStart = $_GET['start_date'] ?? '';
$filterEnd = $_GET['end_date'] ?? '';
$filterCategory = (int)($_GET['category_id'] ?? 0);
$filterSearch = trim((string)($_GET['q'] ?? ''));

$where = ['e.user_id = :user_id'];
$params = ['user_id' => $userId];
if ($filterStart) {
    $where[] = 'e.date >= :start';
    $params['start'] = $filterStart;
}
if ($filterEnd) {
    $where[] = 'e.date <= :end';
    $params['end'] = $filterEnd;
}
if ($filterCategory > 0) {
    $where[] = 'e.category_id = :category_id';
    $params['category_id'] = $filterCategory;
}
if ($filterSearch !== '') {
    $where[] = 'e.note LIKE :q';
    $params['q'] = '%' . $filterSearch . '%';
}

$sql = 'SELECT e.id, e.date, e.note, e.amount, e.receipt_path, e.receipt_mime, c.name category_name
        FROM expenses e
        INNER JOIN categories c ON c.id = e.category_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY e.date DESC, e.id DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$catStmt = db()->prepare('SELECT id, name FROM categories ORDER BY name');
$catStmt->execute();
$categories = $catStmt->fetchAll();

$editId = (int)($_GET['edit'] ?? 0);
$edit = null;
if ($editId > 0) {
    $edStmt = db()->prepare('SELECT * FROM expenses WHERE id = :id AND user_id = :user_id');
    $edStmt->execute(['id' => $editId, 'user_id' => $userId]);
    $edit = $edStmt->fetch();
}

render_header('Despesas');
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h4">Despesas</h1>
    <a class="btn btn-outline-secondary" href="/public/dashboard.php">Voltar</a>
</div>
<div class="card card-body mb-4">
    <h2 class="h6"><?= $edit ? 'Editar Despesa' : 'Nova Despesa' ?></h2>
    <form method="post" enctype="multipart/form-data" class="row g-2">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'update' : 'create' ?>">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$edit['id'] ?>"><?php endif; ?>
        <div class="col-md-2"><input class="form-control" type="date" name="date" required value="<?= e($edit['date'] ?? date('Y-m-d')) ?>"></div>
        <div class="col-md-2">
            <select class="form-select" name="category_id" required>
                <option value="">Categoria</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= (int)$category['id'] ?>" <?= (int)($edit['category_id'] ?? 0) === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3"><input class="form-control" name="note" required placeholder="Observação" value="<?= e($edit['note'] ?? '') ?>"></div>
        <div class="col-md-2"><input class="form-control" name="amount" required placeholder="Valor" value="<?= e($edit['amount'] ?? '') ?>"></div>
        <div class="col-md-3"><input class="form-control" type="file" name="receipt" <?= $edit ? '' : 'required' ?> accept=".jpg,.jpeg,.png,.webp,.pdf"></div>
        <div class="col-12">
            <button class="btn btn-primary"><?= $edit ? 'Atualizar' : 'Salvar' ?></button>
            <?php if ($edit): ?><a href="/public/expenses.php" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
        </div>
    </form>
</div>
<form method="get" class="row g-2 mb-3">
    <div class="col-md-2"><input type="date" class="form-control" name="start_date" value="<?= e($filterStart) ?>"></div>
    <div class="col-md-2"><input type="date" class="form-control" name="end_date" value="<?= e($filterEnd) ?>"></div>
    <div class="col-md-2">
        <select class="form-select" name="category_id">
            <option value="0">Todas categorias</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= (int)$category['id'] ?>" <?= $filterCategory === (int)$category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4"><input class="form-control" name="q" value="<?= e($filterSearch) ?>" placeholder="Buscar observação"></div>
    <div class="col-md-2"><button class="btn btn-outline-primary w-100">Filtrar</button></div>
</form>
<table class="table table-striped align-middle">
    <thead><tr><th>Data</th><th>Tipo</th><th>Observação</th><th class="text-end">Valor</th><th>Comprovante</th><th class="text-end">Ações</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= e($row['date']) ?></td>
            <td><?= e($row['category_name']) ?></td>
            <td><?= e($row['note']) ?></td>
            <td class="text-end"><?= e(format_money((float)$row['amount'])) ?></td>
            <td>
                <?php if (str_starts_with($row['receipt_mime'], 'image/')): ?>
                    <a href="<?= e(receipt_url($row['receipt_path'])) ?>" target="_blank"><img src="<?= e(receipt_url($row['receipt_path'])) ?>" alt="comprovante" width="60"></a>
                <?php else: ?>
                    <a href="<?= e(receipt_url($row['receipt_path'])) ?>" target="_blank">PDF</a>
                <?php endif; ?>
            </td>
            <td class="text-end">
                <a class="btn btn-sm btn-warning" href="/public/expenses.php?edit=<?= (int)$row['id'] ?>">Editar</a>
                <form method="post" class="d-inline" onsubmit="return confirm('Excluir esta despesa?')">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                    <button class="btn btn-sm btn-danger">Excluir</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php render_footer(); ?>
