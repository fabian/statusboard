<?php

require_once __DIR__ . '/facebook.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

if ($code) {

    $request = $client->post('oauth/access_token', null, array (
        'code' => $code,
        'client_id' => $config['app_id'],
        'client_secret' => $config['app_secret'],
        'redirect_uri' => $config['redirect_uri'],
    ));
    $response = $request->send();

    parse_str($response->getBody(), $array);

    $request = $client->post('oauth/access_token', null, array (
        'grant_type' => 'fb_exchange_token',
        'client_id' => $config['app_id'],
        'client_secret' => $config['app_secret'],
        'fb_exchange_token' => $array['access_token'],
    ));
    $response = $request->send();

    parse_str($response->getBody(), $array);
    $accessToken = $array['access_token'];

    $request = $client->get('me/accounts');
    $query = $request->getQuery();
    $query->set('access_token', $accessToken);

    $response = $request->send();

    $json = $response->json();

    foreach ($json['data'] as $account) {
        if ($account['id'] == $config['page_id']){
            $accessToken = $account['access_token'];
            break;
        }
    }

    file_put_contents($config['token_file'], $accessToken);
}

header('Location: ' . dirname($_SERVER['SCRIPT_NAME']));
