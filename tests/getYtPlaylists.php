<?php

require_once "../entity/YtPlaylist.php";
require_once "../persistence/getYtPlaylists.php";
require_once '../vendor/autoload.php';

use SimeonBorko\WpYoutubeAgent\Persistence;

$developerKey = \trim(\file_get_contents('./developerKey.txt'));
$channelId = \trim(\file_get_contents('./channelId.txt'));

$client = new \Google_Client();
$client->setApplicationName("WP Youtube Agent");
$client->setDeveloperKey($developerKey);

$service = new \Google_Service_YouTube($client);

$playlists = Persistence\getYtPlaylists($service, $channelId);

?>

<table border="1">
<?php
foreach ($playlists as $p):
?>
    <tr>
        <td><?= $p->id ?></td>
        <td><?= $p->title ?></td>
        <td><?= $p->itemCount ?></td>
    </tr>
<?php
endforeach;
?>
</table>

<p>Total: <?= \count($playlists) ?></p>
