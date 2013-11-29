<?php

require_once __DIR__ . '/harvest.php';

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {

    try {

        $from = date('Ymd', strtotime('last monday', strtotime('tomorrow')));
        $to = date('Ymd', strtotime('last monday +5 days', strtotime('tomorrow')));

        $request = $client->get('projects/' . $config['project_id'] . '/entries', array(
            'Accept' => 'application/json',
        ));
        $query = $request->getQuery();
        $query->set('from', $from);
        $query->set('to', $to);
        $query->set('access_token', $json['access_token']);
        $response = $request->send();

        $entriesJson = $response->json();
        $success = true;

        $total = 0;
        $hours = array(0 => 0);
        foreach ($entriesJson as $entryJson) {

            $dayEntry = $entryJson['day_entry'];

            if (isset($dayEntry['hours_with_timer'])) {
                $total += $dayEntry['hours_with_timer'];
            } else {
                $total += $dayEntry['hours'];
            }

            $weekday = date('w', strtotime($dayEntry['spent_at']));
            $hours[$weekday] = $total;
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
                                'value' => isset($hours[0]) ? $hours[0] : $total,
                            ),
                            array(
                                'title' => 'Mon',
                                'value' => isset($hours[1]) ? $hours[1] : $total,
                            ),
                            array(
                                'title' => 'Tue',
                                'value' => isset($hours[2]) ? $hours[2] : $total,
                            ),
                            array(
                                'title' => 'Wed',
                                'value' => isset($hours[3]) ? $hours[3] : $total,
                            ),
                            array(
                                'title' => 'Thu',
                                'value' => isset($hours[4]) ? $hours[4] : $total,
                            ),
                            array(
                                'title' => 'Fri',
                                'value' => isset($hours[5]) ? $hours[5] : $total,
                            ),
                            array(
                                'title' => 'Sat',
                                'value' => isset($hours[6]) ? $hours[6] : $total,
                            ),
                        ),
                    ),
                    array(
                        'title' => 'Required',
                        'color' => 'lightGray',
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
    }
}

$authorizeQuery = array(
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'response_type' => 'code',
);
$authorizeUrl = $config['base_url'] . '/oauth2/authorize?' . http_build_query($authorizeQuery);

$grapUrl = $config['server_url'] . '/data.json';

?>

<?php if ($success): ?>
    <a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=Harvest">Add to Status Board</a>
<?php else: ?>
    <a href="<?php echo htmlentities($authorizeUrl); ?>">Login with Harvest</a>
<?php endif; ?>
