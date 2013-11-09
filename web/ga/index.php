<?php

require_once __DIR__ . '/ga.php';

// idea: map

$success = false;

$json = json_decode(file_get_contents($config['token_file']), true);
if ($json) {

    try  {

        $dataSequences = array();

        foreach ($config['views'] as $viewId) {

            $params = array(
                'ids' => 'ga:' . $viewId,
                'start-date' => date('Y-m-d', strtotime('-1 month')),
                'end-date' => date('Y-m-d', strtotime('today')),
                'metrics' => 'ga:visits',
                'dimensions' => 'ga:date',
            );

            $request = $client->get('data/ga', array(), array('query' => $params));
            $request->setHeader('Authorization', 'Bearer ' . $json['access_token']);

            $response = $request->send();

            $dataJson = $response->json();
            $success = true;

            $visits = array();
            foreach ($dataJson['rows'] as $row) {
                $visits[date('j.n.', strtotime($row[0]))] = $row[1];
            }
            $dataSequences[] = array(
                'title' => $dataJson['profileInfo']['profileName'],
                'color' => 'green',
                'datapoints' => $visits,
            );
        }

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
    'approval_prompt' => 'force',
    'response_type' => 'code',
);
$authorizeUrl = $config['auth_url'] . '/auth?' . http_build_query($authorizeQuery);

?>

<?php if ($success): ?>
    <a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=Google%20Analytics">Add to Status Board</a>
<?php else: ?>
    <a href="<?php echo htmlentities($authorizeUrl); ?>">Login with Google</a>
<?php endif; ?>
