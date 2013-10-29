<?php

require_once __DIR__ . '/harvest.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

if ($code) {

    $request = $client->post('oauth2/token', null, array (
        'code' => $code,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code',
    ));
    $response = $request->send();
    file_put_contents($config['token_file'], $response->getBody());

}

header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
