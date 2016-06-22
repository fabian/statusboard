<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$client = new GuzzleHttp\Client(['base_uri' => 'https://codeship.com/api/v1/']);

$response = $client->get('projects.json', ['query' => [
    'api_key' => $config['codeship_api_key']
]]);

$json = json_decode($response->getBody(), true);

$branch = isset($_GET['branch']) ? $_GET['branch'] : 'master';

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Projects />');

foreach ($json['projects'] as $project) {

    $name = $project['repository_name'];
    $activity = 'Sleeping';
    $lastBuildStatus = null;
    $lastBuildLabel = null;
    $webUrl = 'https://www.codeship.io/projects/' . $project['id'];

    if ($branch != 'master') {
        $name .= ' ' . $branch;
    }

    foreach ($project['builds'] as $build) {

        if ($build['branch'] == $branch) {

            if ($lastBuildStatus === null) {
                $lastBuildStatus = 'Unknown';
            }

            if ($lastBuildStatus === 'Unknown') {

                if ($build['status'] == 'success') {
                    $lastBuildStatus = 'Success';
                    $lastBuildLabel = $build['id'];
                }

                if ($build['status'] == 'error') {
                    $lastBuildStatus = 'Failure';
                    $lastBuildLabel = $build['id'];
                }
            }

            if ($build['status'] == 'testing') {
                $activity = 'Building';
            }
        }
    }

    if ($lastBuildStatus !== null) {

        $projectXML = $xml->addChild('Project');
        $projectXML->addAttribute('name', $name);
        $projectXML->addAttribute('activity', $activity);
        $projectXML->addAttribute('lastBuildStatus', $lastBuildStatus);
        $projectXML->addAttribute('lastBuildLabel', $lastBuildLabel);
        $projectXML->addAttribute('lastBuildTime', date('c'));
        $projectXML->addAttribute('webUrl', $webUrl);
    }
}

header('Content-type: application/xml;charset=utf-8');
echo $xml->asXML();
