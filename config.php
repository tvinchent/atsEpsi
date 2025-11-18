<?php

return [
    'db_host' => 'localhost',
    'db_name' => 'AtsEpsi',
    'db_user' => 'root',
    'db_pass' => 'root',

    // chemin vers ton jar Tika
    'tika_path' => __DIR__ . '/assets/tika-app.jar',

    // dossiers
    'cv_paths' => [
        '/Users/thibault/OneDrive/CV/',
        '/Users/emilie/OneDrive/CV/',
        __DIR__ .'/storage/cv_raw'
    ],
    // 'cv_raw_dir' => __DIR__ . '/storage/cv_raw',
];
