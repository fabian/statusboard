<?php

require_once __DIR__ . '/ga.php';

// idea: map

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {

    try  {

        $dataSequences = array();

        $params = array(
            'ids' => 'ga:' . $config['view_id'],
            'start-date' => date('Y-m-d', strtotime('-1 month')),
            'end-date' => date('Y-m-d', strtotime('today')),
            'metrics' => 'ga:visits,ga:pageviews',
            'dimensions' => 'ga:date',
        );

        $request = $client->get('data/ga', array(), array('query' => $params));
        $request->setHeader('Authorization', 'Bearer ' . $json['access_token']);

        $response = $request->send();

        $dataJson = $response->json();
        var_dump($dataJson);
        $success = true;

        $visits = array();
        $pageViews = array();
        foreach ($dataJson['rows'] as $row) {
            $visits[] = array(
                'title' => date('j.n.', strtotime($row[0])),
                'value' => $row[1],
            );
            $pageViews = array(
                'title' => date('j.n.', strtotime($row[0])),
                'value' => $row[2],
            );
        }
        $dataSequences[] = array(
            'title' => 'Visitors',
            'color' => 'green',
            'datapoints' => $visits,
        );
        $dataSequences[] = array(
            'title' => 'Page Views',
            'color' => 'yellow',
            'datapoints' => $pageViews,
        );

        $graphJson = array(
            'graph' => array(
                'title' => 'Google Analytics',
                'type' => 'line',
                'yAxis' => array(
                ),
                'refreshEveryNSeconds' => 15,
                'datasequences' => $dataSequences
            ),
        );

        file_put_contents(__DIR__ . '/data.json', json_encode($graphJson));

    } catch (Guzzle\Http\Exception\RequestException $e) {
        // update failed
    }
}

$grapUrl = $config['server_url'] . '/data.json';

$authorizeQuery = array(
    'scope' => $config['scope'],
    'client_id' => $config['client_id'],
    'redirect_uri' => $config['redirect_uri'],
    'access_type' => 'offline',
    'response_type' => 'code',
);
$authorizeUrl = $config['auth_url'] . '/auth?' . http_build_query($authorizeQuery);

?>

<?php if ($success): ?>
    <a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=Google%20Analytics">Add to Status Board</a>
<?php else: ?>
    <a href="<?php echo htmlentities($authorizeUrl); ?>">Login with Google</a>
<?php endif; ?>
