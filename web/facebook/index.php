<?php

require_once __DIR__.'/facebook.php';

$success = false;

$accessToken = file_get_contents($config['token_file']);
if ($accessToken) {
    try {
        $since = strtotime('-7days');

        $response = $client->get($config['page_id'].'/insights/page_fan_adds', ['query' => [
            'since'        => $since,
            'access_token' => $accessToken,
        ]]);

        $dataJson = json_decode($response->getBody(), true);

        $likes = [];
        foreach ($dataJson['data'][0]['values'] as $valueJson) {
            $likes[] = [
                'title' => date('D', strtotime($valueJson['end_time'].' -1day')),
                'value' => $valueJson['value'],
            ];
        }

        $response = $client->get($config['page_id'].'/insights/page_fan_removes', ['query' => [
            'since'        => $since,
            'access_token' => $accessToken,
        ]]);

        $dataJson = json_decode($response->getBody(), true);
        $success = true;

        $unlikes = [];
        foreach ($dataJson['data'][0]['values'] as $valueJson) {
            $unlikes[] = [
                'title' => date('D', strtotime($valueJson['end_time'].' -1day')),
                'value' => $valueJson['value'],
            ];
        }

        $graphJson = [
            'graph' => [
                'title' => 'Facebook',
                'type'  => 'bar',
                'xAxis' => [
                    'showEveryLabel' => true,
                ],
                'refreshEveryNSeconds' => 15,
                'datasequences'        => [
                    [
                        'title'      => 'Likes',
                        'color'      => 'blue',
                        'datapoints' => $likes,
                    ],
                    [
                        'title'      => 'Unlikes',
                        'color'      => 'red',
                        'datapoints' => $unlikes,
                    ],
                ],
            ],
        ];

        file_put_contents(__DIR__.'/data.json', json_encode($graphJson));
    } catch (GuzzleHttp\Exception\RequestException $e) {
        // update failed
    }
}

$authorizeQuery = [
    'client_id'    => $config['app_id'],
    'redirect_uri' => $config['redirect_uri'],
    'scope'        => 'manage_pages,read_insights',
];
$authorizeUrl = 'https://www.facebook.com/dialog/oauth?'.http_build_query($authorizeQuery);

$grapUrl = $config['server_url'].'/data.json';

if (!$success) {
    echo '<a href="'.htmlentities($authorizeUrl).'">Login with Facebook</a>'."\n";
}
