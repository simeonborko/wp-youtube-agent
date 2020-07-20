<?php

require_once __DIR__."/../entity/YtPlaylist.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";
require_once __DIR__.'/../vendor/autoload.php';

use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;

$developerKey = \trim(\file_get_contents('./developerKey.txt'));
$channelId = \trim(\file_get_contents('./channelId.txt'));

$client = new \Google_Client();
$client->setApplicationName("WP Youtube Agent");
$client->setDeveloperKey($developerKey);

$service = new \Google_Service_YouTube($client);

$playlists = (new YtPlaylistRepository($service))->findByChannelId($channelId);

?>

<table border="1">
<?php
foreach ($playlists as $p):
?>
    <tr>
        <td><a href="./getYtVideos.php?playlistId=<?= $p->id ?>" target="_blank"><?= $p->id ?></a></td>
        <td><?= $p->title ?></td>
        <td><?= $p->itemCount ?></td>
    </tr>
<?php
endforeach;
?>
</table>

<p>Total: <?= \count($playlists) ?></p>
