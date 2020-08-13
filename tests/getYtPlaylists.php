<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../entity/YtPlaylist.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";

use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;

$service = getYoutubeService();
$channelId = getChannelId();
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
