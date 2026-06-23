<?php
return [
    'driver'   => $_ENV['MAIL_DRIVER'] ?? 'smtp',
    'host'     => $_ENV['MAIL_HOST'] ?? 'localhost',
    'port'     => (int)($_ENV['MAIL_PORT'] ?? 587),
    'username' => $_ENV['MAIL_USERNAME'] ?? '',
    'password' => $_ENV['MAIL_PASSWORD'] ?? '',
    'from'     => $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@phantomsmoking.ae/',
    'from_name'=> $_ENV['MAIL_FROM_NAME'] ?? "Phantom Smoking",
];
