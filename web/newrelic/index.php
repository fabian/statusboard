<?php

require_once __DIR__.'/../../vendor/autoload.php';

$config = require __DIR__.'/config.php';

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

$client = new GuzzleHttp\Client(['base_uri' => 'https://api.newrelic.com/v2/']);


$from = date('c', strtotime('-5 hours'));

$response = $client->get('applications/'.$config['application_id'].'/metrics/data.json', ['headers' => [
    'X-Api-Key' => $config['api_key'],
], 'query' => 'from='.$from.'&names[]=Apdex'.'&names[]=WebTransaction'.'&names[]=External/all']);

$metricDataJson = json_decode($response->getBody(), true);

$responseTime = [];
$requests = [];
$apdex = [];
$external = [];
foreach ($metricDataJson['metric_data']['metrics'] as $metricsJson) {
    foreach ($metricsJson['timeslices'] as $timesliceJson) {
        if ($metricsJson['name'] == 'WebTransaction') {
            $responseTime[] = [
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['average_response_time'] / 1000,
            ];
            /*
            $requests[] = array(
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['call_count'],
            );
            */
        }

        if ($metricsJson['name'] == 'Apdex') {
            $apdex[] = [
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['value'] * 10,
            ];
        }

        if ($metricsJson['name'] == 'External/all') {
            $external[] = [
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['average_response_time'] / 1000,
            ];
        }
    }
}

$graphJson = [
    'graph' => [
        'title' => 'New Relic',
        'type'  => 'line',
        'yAxis' => [
        ],
        'refreshEveryNSeconds' => 15,
        'datasequences'        => [
            [
                'title'      => 'Apdex',
                'color'      => 'purple',
                'datapoints' => $apdex,
            ],
            [
                'title'      => 'Response',
                'color'      => 'green',
                'datapoints' => $responseTime,
            ],
            [
                'title'      => 'External',
                'color'      => 'yellow',
                'datapoints' => $external,
            ],
            /*
            array(
                'title' => 'Requests',
                'color' => 'orange',
                'datapoints' => $requests,
            ),
            */
        ],
    ],
];

file_put_contents(__DIR__.'/data.json', json_encode($graphJson));

$grapUrl = $config['server_url'].'/data.json';

?>

<a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=New%20Relic">Add to Status Board</a>
