<?php

require_once __DIR__.'/../../vendor/autoload.php';

$config = require __DIR__.'/config.php';

$client = new GuzzleHttp\Client(['base_uri' => 'https://toggl.com/reports/api/v2/']);

$since = date('Y-m-d', strtotime('last sunday', strtotime('tomorrow')));
$until = date('Y-m-d', strtotime('last sunday +5 days', strtotime('tomorrow')));

$hours = [];
$response = $client->get('weekly', [
    'auth' => [$config['api_token'], 'api_token'],
    'query' => [
        'user_agent' => 'Statusboard',
        'workspace_id' => $config['workspace_id'],
        'since' => $since,
        'until' => $until,
    ]
]);

$reportJson = json_decode($response->getBody(), true);

foreach ($reportJson['data'] as $dataJson) {
    $hours = $dataJson['totals'];
}

$previous = 0;
for ($i = 0; $i < 7; $i++) {
    if (isset($hours[$i])) {
        $previous += $hours[$i] / 60 / 60 / 1000;
    }
    $hours[$i] = $previous;
}

$graphJson = [
    'graph' => [
        'title' => 'Toggl',
        'type'  => 'line',
        'xAxis' => [
            'showEveryLabel' => true,
        ],
        'refreshEveryNSeconds' => 15,
        'datasequences'        => [
            [
                'title'      => 'Hours',
                'color'      => 'green',
                'datapoints' => [
                    [
                        'title' => 'Sun',
                        'value' => $hours[0],
                    ],
                    [
                        'title' => 'Mon',
                        'value' => $hours[1],
                    ],
                    [
                        'title' => 'Tue',
                        'value' => $hours[2],
                    ],
                    [
                        'title' => 'Wed',
                        'value' => $hours[3],
                    ],
                    [
                        'title' => 'Thu',
                        'value' => $hours[4],
                    ],
                    [
                        'title' => 'Fri',
                        'value' => $hours[5],
                    ],
                    [
                        'title' => 'Sat',
                        'value' => $hours[6],
                    ],
                ],
            ],
            [
                'title'      => 'Required',
                'color'      => 'mediumGray',
                'datapoints' => [
                    [
                        'title' => 'Sun',
                        'value' => $config['weekly_hours'] / 5 * 0,
                    ],
                    [
                        'title' => 'Mon',
                        'value' => $config['weekly_hours'] / 5 * 1,
                    ],
                    [
                        'title' => 'Tue',
                        'value' => $config['weekly_hours'] / 5 * 2,
                    ],
                    [
                        'title' => 'Wed',
                        'value' => $config['weekly_hours'] / 5 * 3,
                    ],
                    [
                        'title' => 'Thu',
                        'value' => $config['weekly_hours'] / 5 * 4,
                    ],
                    [
                        'title' => 'Fri',
                        'value' => $config['weekly_hours'] / 5 * 5,
                    ],
                    [
                        'title' => 'Sat',
                        'value' => $config['weekly_hours'] / 5 * 5,
                    ],
                ],
            ],
        ],
    ],
];

file_put_contents(__DIR__.'/data.json', json_encode($graphJson));

