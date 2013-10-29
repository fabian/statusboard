<?php

require_once __DIR__ . '/../../vendor/autoload.php';

class HarvestQueryStringAggregator implements Guzzle\Http\QueryAggregator\QueryAggregatorInterface
{
    public function aggregate($key, $value, Guzzle\Http\QueryString $query)
    {
        $key .= '[]';

        if ($query->isUrlEncoding()) {
            return array($query->encodeValue($key) => array_map(array($query, 'encodeValue'), $value));
        } else {
            return array($key => $value);
        }

        return $ret;
    }
}

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
$query->setAggregator(new HarvestQueryStringAggregator());
$query->set('from', $from);
$query->set('names', array('Agent/MetricsReported/count', 'Apdex'));
$response = $request->send();

$metricDataJson = $response->json();

$responseTime = array();
$apdex = array();
foreach ($metricDataJson['metric_data']['metrics'] as $metricsJson) {

    foreach ($metricsJson['timeslices'] as $timesliceJson) {

        if ($metricsJson['name'] == 'Agent/MetricsReported/count') {
            $responseTime[] = array(
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['average_value'],
            );
        }

        if ($metricsJson['name'] == 'Apdex') {
            $apdex[] = array(
                'title' => date('H:i', strtotime($timesliceJson['to'])),
                'value' => $timesliceJson['values']['value'],
            );
        }
    }
}

$graphJson = array(
    'graph' => array(
        'title' => 'New Relic',
        'type' => 'line',
        'yAxis' => array(
            'units' => array(
                'suffix' => 's',
            ),
        ),
        'refreshEveryNSeconds' => 15,
        'datasequences' => array(
            array(
                'title' => 'Response',
                'color' => 'orange',
                'datapoints' => $responseTime,
            ),
            array(
                'title' => 'Apdex',
                'color' => 'blue',
                'datapoints' => $apdex,
            ),
        ),
    ),
);

file_put_contents(__DIR__ . '/data.json', json_encode($graphJson));

$grapUrl = $config['server_url'] . '/data.json';

?>

<a href="panicboard://?url=<?php echo htmlentities(urlencode($grapUrl)); ?>&panel=graph&sourceDisplayName=New%20Relic">Add to Status Board</a>
