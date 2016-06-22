<?php

require_once __DIR__.'/ga.php';

// idea: map

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {
    try {
        $dataSequences = [];

        $params = [
            'ids'        => 'ga:'.$config['view_id'],
            'start-date' => date('Y-m-d', strtotime('-1 month')),
            'end-date'   => date('Y-m-d', strtotime('today')),
            'metrics'    => 'ga:visits,ga:newVisits,ga:pageviews',
            'dimensions' => 'ga:date',
        ];

        $response = $client->get('data/ga', ['query' => $params, 'headers' => ['Authorization' => 'Bearer '.$json['access_token']]]);

        $dataJson = json_decode($response->getBody(), true);
        $success = true;

        $visitors = [];
        $newVisitors = [];
        $pageViews = [];
        foreach ($dataJson['rows'] as $row) {
            $visitors[] = [
                'title' => date('j. M', strtotime($row[0])),
                'value' => $row[1],
            ];
            $newVisitors[] = [
                'title' => date('j. M', strtotime($row[0])),
                'value' => $row[2],
            ];
            $pageViews[] = [
                'title' => date('j. M', strtotime($row[0])),
                'value' => $row[3],
            ];
        }
        $dataSequences[] = [
            'title'      => 'Visitors',
            'color'      => 'yellow',
            'datapoints' => $visitors,
        ];
        $dataSequences[] = [
            'title'      => 'New Visitors',
            'color'      => 'green',
            'datapoints' => $newVisitors,
        ];
        $dataSequences[] = [
            'title'      => 'Page Views',
            'color'      => 'purple',
            'datapoints' => $pageViews,
        ];

        $graphJson = [
            'graph' => [
                'title' => 'Google Analytics',
                'type'  => 'line',
                'yAxis' => [
                ],
                'refreshEveryNSeconds' => 15,
                'datasequences'        => $dataSequences,
            ],
        ];

        file_put_contents(__DIR__.'/data.json', json_encode($graphJson));
    } catch (GuzzleHttp\Exception\RequestException $e) {
        // update failed
        echo $e->getResponse()->getBody(true);
    }
}

$grapUrl = $config['server_url'].'/data.json';

$authorizeQuery = [
    'scope'           => $config['scope'],
    'client_id'       => $config['client_id'],
    'redirect_uri'    => $config['redirect_uri'],
    'access_type'     => 'offline',
    'approval_prompt' => 'force',
    'response_type'   => 'code',
];
$authorizeUrl = $config['auth_url'].'auth?'.http_build_query($authorizeQuery);

if (!$success) {
    echo '<a href="'.htmlentities($authorizeUrl).'">Login with Google</a>'."\n";
}
