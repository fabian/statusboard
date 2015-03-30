<?php

require_once __DIR__ . '/harvest.php';

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {

    try {

        $from = date('Ymd', strtotime('last sunday', strtotime('tomorrow')));
        $to = date('Ymd', strtotime('last sunday +5 days', strtotime('tomorrow')));

        $hours = array();
        foreach ($config['project_ids'] as $projectId) {

            $request = $client->get('projects/' . $projectId . '/entries', array(
                'Accept' => 'application/json',
            ));
            $query = $request->getQuery();
            $query->set('from', $from);
            $query->set('to', $to);
            $query->set('access_token', $json['access_token']);
            $response = $request->send();

            $entriesJson = $response->json();
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

        $graphJson = array(
            'graph' => array(
                'title' => 'Harvest',
                'type' => 'line',
                'xAxis' => array(
                    'showEveryLabel' => true,
                ),
                'refreshEveryNSeconds' => 15,
                'datasequences' => array(
                    array(
                        'title' => 'Hours',
                        'color' => 'green',
                        'datapoints' => array(
                            array(
                                'title' => 'Sun',
                                'value' => $hours[0],
                            ),
                            array(
                                'title' => 'Mon',
                                'value' => $hours[1],
                            ),
                            array(
                                'title' => 'Tue',
                                'value' => $hours[2],
                            ),
                            array(
                                'title' => 'Wed',
                                'value' => $hours[3],
                            ),
                            array(
                                'title' => 'Thu',
                                'value' => $hours[4],
                            ),
                            array(
                                'title' => 'Fri',
                                'value' => $hours[5],
                            ),
                            array(
                                'title' => 'Sat',
                                'value' => $hours[6],
                            ),
                        ),
                    ),
                    array(
                        'title' => 'Required',
                        'color' => 'mediumGray',
                        'datapoints' => array(
                            array(
                                'title' => 'Sun',
                                'value' => $config['weekly_hours'] / 5 * 0,
                            ),
                            array(
                                'title' => 'Mon',
                                'value' => $config['weekly_hours'] / 5 * 1,
                            ),
                            array(
                                'title' => 'Tue',
                                'value' => $config['weekly_hours'] / 5 * 2,
                            ),
                            array(
                                'title' => 'Wed',
                                'value' => $config['weekly_hours'] / 5 * 3,
                            ),
                            array(
                                'title' => 'Thu',
                                'value' => $config['weekly_hours'] / 5 * 4,
                            ),
                            array(
                                'title' => 'Fri',
                                'value' => $config['weekly_hours'] / 5 * 5,
                            ),
                            array(
                                'title' => 'Sat',
                                'value' => $config['weekly_hours'] / 5 * 5,
                            ),
                        ),
                    ),
                ),
            ),
        );

        file_put_contents(__DIR__ . '/data.json', json_encode($graphJson));

    } catch (Guzzle\Http\Exception\RequestException $e) {
        // update failed
        echo $e->getResponse()->getBody(true);
    }
}

$authorizeQuery = array(
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
);
$authorizeUrl = $config['base_url'] . '/oauth2/authorize?' . http_build_query($authorizeQuery);

$grapUrl = $config['server_url'] . '/data.json';

if (!$success) {
    echo '<a href="' . htmlentities($authorizeUrl) . '">Login with Harvest</a>' . "\n";
}
