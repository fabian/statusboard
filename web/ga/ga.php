<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$auth = '';
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $auth .= $_SERVER['PHP_AUTH_USER'];
    if (!empty($_SERVER['PHP_AUTH_PW'])) {
        $auth .= ':';
        $auth .= $_SERVER['PHP_AUTH_PW'];
    }
    $auth .= '@';
}
$server = $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

$config['server_url'] = 'http://' . $auth . $server;
$config['redirect_uri'] = 'http://' . $server . '/callback.php';

$authClient = new Guzzle\Http\Client($config['auth_url']);
$client = new Guzzle\Http\Client($config['base_url']);
