<?php
return [
  'db_host' => 'db',
  'db_name' => 'cvdb',
  'db_user' => 'cvuser',
  'db_pass' => 'cvpass',

  'tika_path' => '/opt/tika/tika-app.jar',

  // Plusieurs chemins séparés par des virgules si besoin plus tard
  'cv_paths' => array_values(array_filter(array_map('trim',
      explode(',', getenv('CV_PATHS') ?: '/cv1')
  ))),
];
