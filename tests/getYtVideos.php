<?php

if (!$_GET["playlistId"]) {
    echo "Set playlistId";
    die();
}

$playlistId = $_GET["playlistId"];

require_once "../entity/YtVideo.php";
require_once "../persistence/getYtVideos.php";
require_once "../persistence/getYtTags.php";
require_once '../vendor/autoload.php';

use SimeonBorko\WpYoutubeAgent\Persistence;

$developerKey = \trim(\file_get_contents('./developerKey.txt'));

$client = new \Google_Client();
$client->setApplicationName("WP Youtube Agent");
$client->setDeveloperKey($developerKey);

$service = new \Google_Service_YouTube($client);

$videos = Persistence\getYtVideos($service, $playlistId);

foreach ($videos as $video) {
    $video->tags = Persistence\getYtTags($service, $video->id);
}

?>

<style>
.cut-text { 
  text-overflow: ellipsis;
  overflow: hidden; 
  max-width: 400px; 
  height: 1.2em; 
  white-space: nowrap;
}
</style>

<table border="1">
<?php
foreach ($videos as $v):
?>
    <tr>
        <td><a href="https://youtu.be/<?= $v->id ?>" target="_blank"><?= $v->id ?></a></td>
        <td><?= $v->title ?></td>
        <td><div class="cut-text"><?= $v->description ?></div></td>
        <td><div class="cut-text"><?= join(", ", $v->tags) ?></div></td>
        <td>
            <?php if ($v->imageUrl): ?>
            <img src="<?= $v->imageUrl ?>" height="100">
            <?php endif; ?>
        </td>
    </tr>
<?php
endforeach;
?>
</table>

<p>Total: <?= \count($videos) ?></p>
