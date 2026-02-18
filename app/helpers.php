<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$config = require __DIR__ . '/../config/config.php';
date_default_timezone_set($config['app']['timezone']);

function app_config(): array
{
    static $cfg = null;
    if ($cfg === null) {
        $cfg = require __DIR__ . '/../config/config.php';
    }
    return $cfg;
}

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = require __DIR__ . '/../config/db.php';
    }
    return $pdo;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    $base = rtrim(app_config()['app']['base_url'], '/');
    header('Location: ' . $base . $path);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function get_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!$token || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Token CSRF inválido.');
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_auth(): void
{
    if (!current_user()) {
        flash('danger', 'Faça login para continuar.');
        redirect('/index.php');
    }
}

function money_to_decimal(string $value): string
{
    $value = trim($value);
    $value = str_replace(['R$', ' '], '', $value);
    if (str_contains($value, ',')) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    }
    return number_format((float)$value, 2, '.', '');
}

function format_money(float $value): string
{
    return 'R$ ' . number_format($value, 2, ',', '.');
}

function upload_receipt(array $file): array
{
    $cfg = app_config()['app'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Comprovante é obrigatório.');
    }

    if (($file['size'] ?? 0) > $cfg['max_upload_size']) {
        throw new RuntimeException('Arquivo excede o limite de 8MB.');
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $cfg['allowed_extensions'], true)) {
        throw new RuntimeException('Extensão não permitida.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = (string)$finfo->file($file['tmp_name']);
    if (!in_array($mime, $cfg['allowed_mimes'], true)) {
        throw new RuntimeException('Tipo MIME inválido.');
    }

    $dir = $cfg['receipt_storage'];
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException('Não foi possível criar diretório de upload.');
    }

    $name = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $dest = $dir . '/' . $name;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Falha no upload do arquivo.');
    }

    return ['path' => 'uploads/' . $name, 'mime' => $mime];
}

function delete_receipt(?string $path): void
{
    if (!$path) {
        return;
    }
    $fullPath = __DIR__ . '/../' . ltrim($path, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

function receipt_url(string $path): string
{
    $base = rtrim(app_config()['app']['base_url'], '/');
    return $base . '/' . ltrim($path, '/');
}

function render_header(string $title): void
{
    $user = current_user();
    $flashes = get_flashes();
    include __DIR__ . '/views/header.php';
}

function render_footer(): void
{
    include __DIR__ . '/views/footer.php';
}
