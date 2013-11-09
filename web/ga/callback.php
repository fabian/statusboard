<?php

require_once __DIR__ . '/ga.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

if ($code) {

    $request = $authClient->post('token', null, array (
        'code' => $code,
        'client_id' => $config['client_id'],
        'client_secret' => $config['client_secret'],
        'redirect_uri' => $config['redirect_uri'],
        'grant_type' => 'authorization_code',
    ));
    $response = $request->send();
    $json = $response->json();

    file_put_contents($config['token_file'], $response->getBody());

    if (!empty($json['refresh_token'])) {
        file_put_contents($config['refresh_token_file'], $response->getBody());
    }
}

header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
