<?php

require_once __DIR__.'/harvest.php';

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {
    try {
        $from = date('Ymd', strtotime('last sunday', strtotime('tomorrow')));
        $to = date('Ymd', strtotime('last sunday +5 days', strtotime('tomorrow')));

        $hours = [];
        foreach ($config['project_ids'] as $projectId) {
            $response = $client->get('projects/'.$projectId.'/entries', ['headers' => ['Accept' => 'application/json'], 'query' => [
                'from'         => $from,
                'to'           => $to,
                'access_token' => $json['access_token'],
            ]]);

            $entriesJson = json_decode($response->getBody(), true);
            $success = true;

            foreach ($entriesJson as $entryJson) {
                $dayEntry = $entryJson['day_entry'];

                if (isset($dayEntry['hours_with_timer'])) {
                    $entryHours = $dayEntry['hours_with_timer'];
                } else {
                    $entryHours = $dayEntry['hours'];
                }

                $weekday = date('w', strtotime($dayEntry['spent_at']));
                if (!isset($hours[$weekday])) {
                    $hours[$weekday] = 0;
                }
                $hours[$weekday] += $entryHours;
            }
        }

        $previous = 0;
        for ($i = 0; $i < 7; $i++) {
            if (isset($hours[$i])) {
                $previous += $hours[$i];
            }
            $hours[$i] = $previous;
        }

        $graphJson = [
            'graph' => [
                'title' => 'Harvest',
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
    } catch (GuzzleHttp\Exception\RequestException $e) {
        // update failed
        echo $e->getResponse()->getBody(true);
    }
}

$authorizeQuery = [
    'client_id'     => $config['client_id'],
    'redirect_uri'  => $config['redirect_uri'],
    'response_type' => 'code',
];
$authorizeUrl = $config['base_url'].'/oauth2/authorize?'.http_build_query($authorizeQuery);

$grapUrl = $config['server_url'].'/data.json';

if (!$success) {
    echo '<a href="'.htmlentities($authorizeUrl).'">Login with Harvest</a>'."\n";
}
