<?php
require __DIR__ . '/../app/helpers.php';
require_auth();
$userId = current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $date = $_POST['date'] ?? '';
    $amount = money_to_decimal((string)($_POST['amount'] ?? '0'));
    $note = trim((string)($_POST['note'] ?? ''));

    if (!$date || (float)$amount <= 0) {
        flash('danger', 'Data e valor válido são obrigatórios.');
    } else {
        $stmt = db()->prepare('INSERT INTO advances (date, amount, note, user_id, created_at) VALUES (:date, :amount, :note, :user_id, NOW())');
        $stmt->execute([
            'date' => $date,
            'amount' => $amount,
            'note' => $note,
            'user_id' => $userId,
        ]);
        flash('success', 'Adiantamento registrado.');
    }
    redirect('/public/advances.php');
}

$stmt = db()->prepare('SELECT id, date, amount, note FROM advances WHERE user_id = :user_id ORDER BY date DESC, id DESC');
$stmt->execute(['user_id' => $userId]);
$rows = $stmt->fetchAll();

render_header('Adiantamentos');
?>
<div class="d-flex justify-content-between mb-3">
    <h1 class="h4">Adiantamentos</h1>
    <a class="btn btn-outline-secondary" href="/public/dashboard.php">Voltar</a>
</div>
<form method="post" class="card card-body mb-4">
    <?= csrf_input() ?>
    <div class="row g-2">
        <div class="col-md-3"><input type="date" class="form-control" name="date" required value="<?= date('Y-m-d') ?>"></div>
        <div class="col-md-3"><input class="form-control" name="amount" placeholder="Valor (ex: 120,50)" required></div>
        <div class="col-md-4"><input class="form-control" name="note" placeholder="Observação (opcional)"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Salvar</button></div>
    </div>
</form>
<table class="table table-striped">
    <thead><tr><th>Data</th><th>Observação</th><th class="text-end">Valor</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
        <tr>
            <td><?= e($row['date']) ?></td>
            <td><?= e($row['note']) ?></td>
            <td class="text-end"><?= e(format_money((float)$row['amount'])) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php render_footer(); ?>
