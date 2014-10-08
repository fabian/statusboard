<?php

require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

$client = new Guzzle\Http\Client('https://www.codeship.io/api/v1');

$request = $client->get('projects.json');
$query = $request->getQuery();
$query->set('api_key', $config['codeship_api_key']);
$response = $request->send();

$json = $response->json();

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><Projects />');

foreach ($json['projects'] as $project) {

    $name = $project['repository_name'];
    $activity = 'Sleeping';
    $lastBuildStatus = 'Unknown';
    $lastBuildLabel = null;
    $webUrl = 'https://www.codeship.io/projects/' . $project['id'];

    foreach ($project['builds'] as $build) {

        if ($build['branch'] == 'master') {

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

    $projectXML = $xml->addChild('Project');
    $projectXML->addAttribute('name', $name);
    $projectXML->addAttribute('activity', $activity);
    $projectXML->addAttribute('lastBuildStatus', $lastBuildStatus);
    $projectXML->addAttribute('lastBuildLabel', $lastBuildLabel);
    $projectXML->addAttribute('lastBuildTime', date('c'));
    $projectXML->addAttribute('webUrl', $webUrl);
}

header('Content-type: application/xml;charset=utf-8');
echo $xml->asXML();