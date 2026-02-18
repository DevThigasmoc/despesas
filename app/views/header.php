<?php $appName = app_config()['app']['name']; ?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> - <?= e($appName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/public/dashboard.php">Despesas Viagem</a>
        <?php if ($user): ?>
            <div class="d-flex gap-2 align-items-center text-white">
                <span><?= e($user['email']) ?></span>
                <a class="btn btn-sm btn-outline-light" href="/public/logout.php">Sair</a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<div class="container pb-5">
    <?php foreach ($flashes as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
