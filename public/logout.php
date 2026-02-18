<?php
require __DIR__ . '/../app/helpers.php';
session_unset();
session_destroy();
session_start();
flash('success', 'Sessão encerrada.');
redirect('/public/index.php');
