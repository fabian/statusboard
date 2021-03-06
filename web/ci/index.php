<?php

require_once __DIR__.'/../../vendor/autoload.php';

$config = require __DIR__.'/config.php';

$projects = [];
foreach ($config['cc_urls'] as $url) {
    $xml = simplexml_load_file($url);
    foreach ($xml->Project as $project) {
        $projects[] = $project;
    }
}

?>
<table>
    <?php foreach ($projects as $project) { ?>
        <tr>
            <td><?php echo htmlentities($project['name']); ?></td>
            <td style="width: 10px; background-color: <?php echo $project['lastBuildStatus'] == 'Success' ? 'rgb(0, 186, 0)' : 'rgb(255, 48, 0)'; ?>">&nbsp;</td>
        </tr>
    <?php } ?>
</table>
