<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$config['server_url'] = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$config['redirect_uri'] = $config['server_url'] . '/callback.php';

$client = new Guzzle\Http\Client('https://graph.facebook.com');
