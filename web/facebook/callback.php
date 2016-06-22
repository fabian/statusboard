<?php

require_once __DIR__.'/facebook.php';

$code = isset($_GET['code']) ? $_GET['code'] : '';

if ($code) {
    $response = $client->post('oauth/access_token', ['form_params' => [
        'code'          => $code,
        'client_id'     => $config['app_id'],
        'client_secret' => $config['app_secret'],
        'redirect_uri'  => $config['redirect_uri'],
    ]]);

    parse_str($response->getBody(), $array);

    $response = $client->post('oauth/access_token', ['form_params' => [
        'grant_type'        => 'fb_exchange_token',
        'client_id'         => $config['app_id'],
        'client_secret'     => $config['app_secret'],
        'fb_exchange_token' => $array['access_token'],
    ]]);

    parse_str($response->getBody(), $array);
    $accessToken = $array['access_token'];

    $response = $client->get('me/accounts', ['query' => [
        'access_token' => $accessToken,
    ]]);

    $json = json_decode($response->getBody(), true);

    foreach ($json['data'] as $account) {
        if ($account['id'] == $config['page_id']) {
            $accessToken = $account['access_token'];
            break;
        }
    }

    file_put_contents($config['token_file'], $accessToken);
}

header('Location: '.dirname($_SERVER['SCRIPT_NAME']));
