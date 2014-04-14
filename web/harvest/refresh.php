<?php

require_once __DIR__ . '/harvest.php';

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {

    try {
        $request = $client->post('oauth2/token', null, array (
            'refresh_token' => $json['refresh_token'],
            'client_id' => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'grant_type' => 'refresh_token',
        ));
        $response = $request->send();
        file_put_contents($config['token_file'], $response->getBody());

        $success = true;

    }  catch (Guzzle\Http\Exception\RequestException $e) {
        // refresh token failed
    }
}

if (!$success) {
    echo "Failed to refresh token\n";
}
