<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$config['server_url'] = 'http://';
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $config['server_url'] .= $_SERVER['PHP_AUTH_USER'];
    if (isset($_SERVER['PHP_AUTH_PW'])) {
        $config['server_url'] .= ':';
        $config['server_url'] .= $_SERVER['PHP_AUTH_PW'];
    }
    $config['server_url'] .= '@';
}
$config['server_url'] .= $_SERVER['HTTP_HOST'];
$config['server_url'] .= dirname($_SERVER['SCRIPT_NAME']);

$config['redirect_uri'] = $config['server_url'] . '/callback.php';

$client = new Guzzle\Http\Client('https://graph.facebook.com');
