<?php
return [
    'db' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'despesas_viagem',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Controle de Despesas de Viagem',
        'base_url' => '', // Ex: https://seudominio.com/public
        'timezone' => 'America/Sao_Paulo',
        'max_upload_size' => 8 * 1024 * 1024,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'image/webp',
            'application/pdf',
        ],
        'receipt_storage' => __DIR__ . '/../uploads',
    ],
];
