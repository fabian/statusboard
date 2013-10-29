<?php

require_once __DIR__ . '/facebook.php';

$success = false;

$accessToken = file_get_contents($config['token_file']);
if ($accessToken) {

    try {

        $since = strtotime('-7days');

        $request = $client->get($config['page_id'] . '/insights/page_fan_adds');
        $query = $request->getQuery();
        $query->set('since', $since);
        $query->set('access_token', $accessToken);
        $response = $request->send();

        $dataJson = $response->json();

        $likes = array();
        foreach ($dataJson['data'][0]['values'] as $valueJson) {

            $likes[] = array(
                'title' => date('D', strtotime($valueJson['end_time'] . ' -1day')),
                'value' => $valueJson['value'],
            );
        }

        $request = $client->get($config['page_id'] . '/insights/page_fan_removes');
        $query = $request->getQuery();
        $query->set('since', $since);
        $query->set('access_token', $accessToken);
        $response = $request->send();

        $dataJson = $response->json();
        $success = true;

        $unlikes = array();
        foreach ($dataJson['data'][0]['values'] as $valueJson) {
        
            $unlikes[] = array(
                'title' => date('D', strtotime($valueJson['end_time'] . ' -1day')),
                'value' => $valueJson['value'],
            );
        }

        $graphJson = array(
            'graph' => array(
                'title' => 'Facebook',
                'type' => 'bar',
                'xAxis' => array(
                    'showEveryLabel' => true,
                ),
                'refreshEveryNSeconds' => 15,
                'datasequences' => array(
                    array(
                        'title' => 'Likes',
                        'color' => 'blue',
                        'datapoints' => $likes,
                    ),
                    array(
                        'title' => 'Unlikes',
                        'color' => 'red',
                        'datapoints' => $unlikes,
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
    'client_id' => $config['app_id'],
    'redirect_uri' => $config['redirect_uri'],
    'scope' => 'manage_pages,read_insights',
);
$authorizeUrl = 'https://www.facebook.com/dialog/oauth?' . http_build_query($authorizeQuery);

$grapUrl = $config['server_url'] . '/data.json';

?>

<?php if ($success): ?>
    <a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=Facebook">Add to Status Board</a>
<?php else: ?>
    <a href="<?php echo htmlentities($authorizeUrl); ?>">Login with Facebook</a>
<?php endif; ?>
