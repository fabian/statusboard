<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$config['server_url'] = 'http://';
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $config['server_url'] .= $_SERVER['PHP_AUTH_USER'];
    if (isset($_SERVER['PHP_AUTH_PW'])) {
        $config['server_url'] .= ':';
        $config['server_url'] .= $_SERVER['PHP_AUTH_PW'];
    }
    $config['server_url'] .= '@';
}
$config['server_url'] .= $_SERVER['HTTP_HOST'];
$config['server_url'] .= dirname($_SERVER['SCRIPT_NAME']);

$client = new Guzzle\Http\Client('https://api.newrelic.com/v2');


$from = date('c', strtotime('-5 hours'));

$request = $client->get('applications/' . $config['application_id'] . '/metrics/data.json', array(
    'X-Api-Key' => $config['api_key'],
));
$query = $request->getQuery();
$query->set('from', $from);
$query->set('names', 'Agent/MetricsReported/count');
$response = $request->send();

$metricsJson = $response->json();

$timeslices = array();
foreach ($metricsJson['metric_data']['metrics'][0]['timeslices'] as $timesliceJson) {

    $timeslices[] = array(
        'title' => date('H:i', strtotime($timesliceJson['to'] . ' -1day')),
        'value' => $timesliceJson['values']['average_response_time'],
    );
}

$graphJson = array(
    'graph' => array(
        'title' => 'New Relic',
        'type' => 'line',
        'yAxis' => array(
            'units' => array(
                'suffix' => 'ms',
            ),
        ),
        'refreshEveryNSeconds' => 15,
        'datasequences' => array(
            array(
                'title' => 'Hours',
                'color' => 'green',
                'datapoints' => $timeslices,
            ),
        ),
    ),
);

file_put_contents(__DIR__ . '/data.json', json_encode($graphJson));

$grapUrl = $config['server_url'] . '/data.json';

?>

<a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=New%20Relic">Add to Status Board</a>
