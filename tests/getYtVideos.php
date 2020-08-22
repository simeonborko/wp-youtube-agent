<?php

if (!$_GET["playlistId"]) {
    echo "Set playlistId";
    die();
}

$playlistId = $_GET["playlistId"];

require_once __DIR__."/common.php";
require_once __DIR__."/../entity/YtVideo.php";
require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../repository/YtTagRepository.php";

use SimeonBorko\WpYoutubeAgent\Repository;

$start = microtime(true);

$service = getYoutubeService();
$videoRepo = new Repository\YtVideoRepository($service);
$tagRepo = new Repository\YtTagRepository($service);

$videos = $videoRepo->findByPlaylistId($playlistId);

foreach ($videos as $video) {
    $video->tags = $tagRepo->findByVideoId($video->id);
}

$timeElapsedSecs = microtime(true) - $start;

?>

<p>Time elapsed secs: <?= $timeElapsedSecs ?></p>

<style>
.cut-text { 
  text-overflow: ellipsis;
  overflow: hidden; 
  max-width: 300px; 
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
        <td><?= $v->publishedAt ?></td>
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
