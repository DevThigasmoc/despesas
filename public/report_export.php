<?php
require __DIR__ . '/../app/helpers.php';
require __DIR__ . '/../app/simple_pdf.php';
require_auth();

$userId = current_user()['id'];
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';
$categoryId = (int)($_GET['category_id'] ?? 0);
$format = $_GET['format'] ?? 'csv';

$where = ['e.user_id = :user_id'];
$params = ['user_id' => $userId];
if ($start) {
    $where[] = 'e.date >= :start';
    $params['start'] = $start;
}
if ($end) {
    $where[] = 'e.date <= :end';
    $params['end'] = $end;
}
if ($categoryId > 0) {
    $where[] = 'e.category_id = :category_id';
    $params['category_id'] = $categoryId;
}

$sql = 'SELECT e.date, c.name category_name, e.note, e.amount, e.receipt_path, e.receipt_mime
        FROM expenses e
        INNER JOIN categories c ON c.id = e.category_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY e.date ASC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$sumExpStmt = db()->prepare('SELECT COALESCE(SUM(e.amount),0) total FROM expenses e WHERE ' . implode(' AND ', $where));
$sumExpStmt->execute($params);
$totalExp = (float)$sumExpStmt->fetch()['total'];

$advSql = 'SELECT COALESCE(SUM(amount),0) total FROM advances WHERE user_id = :user_id';
$advParams = ['user_id' => $userId];
if ($start) { $advSql .= ' AND date >= :start'; $advParams['start'] = $start; }
if ($end) { $advSql .= ' AND date <= :end'; $advParams['end'] = $end; }
$sumAdvStmt = db()->prepare($advSql);
$sumAdvStmt->execute($advParams);
$totalAdv = (float)$sumAdvStmt->fetch()['total'];
$saldo = $totalAdv - $totalExp;

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="despesas.csv"');
    $out = fopen('php://output', 'wb');
    fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, ['data', 'tipo', 'observacao', 'valor', 'comprovante']);
    foreach ($rows as $row) {
        fputcsv($out, [$row['date'], $row['category_name'], $row['note'], $row['amount'], receipt_url($row['receipt_path'])]);
    }
    fputcsv($out, []);
    fputcsv($out, ['TOTAL ADIANTAMENTOS', $totalAdv]);
    fputcsv($out, ['TOTAL DESPESAS', $totalExp]);
    fputcsv($out, ['SALDO', $saldo]);
    fclose($out);
    exit;
}

$lines = [];
$lines[] = 'Periodo: ' . ($start ?: 'inicio') . ' ate ' . ($end ?: 'hoje');
$lines[] = 'Data emissao: ' . date('d/m/Y H:i');
$lines[] = '-------------------------------------------';
foreach ($rows as $row) {
    $lines[] = sprintf('%s | %s | %s | R$ %.2f', $row['date'], $row['category_name'], mb_strimwidth($row['note'], 0, 35, '...'), (float)$row['amount']);
    $lines[] = 'Comprovante: ' . receipt_url($row['receipt_path']) . (str_contains($row['receipt_mime'], 'pdf') ? ' (PDF)' : ' (imagem)');
}
$lines[] = '-------------------------------------------';
$lines[] = 'Total adiantamentos: ' . format_money($totalAdv);
$lines[] = 'Total despesas: ' . format_money($totalExp);
$lines[] = 'Saldo: ' . format_money($saldo);

simple_pdf_output('Relatorio de Despesas de Viagem', $lines, 'relatorio_despesas.pdf');
