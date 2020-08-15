<?php

if (!$_GET["playlistId"]) {
    echo "Set playlistId";
    die();
}

$playlistId = $_GET["playlistId"];

require_once __DIR__."/common.php";
require_once __DIR__."/../entity/WpSermon.php";
require_once __DIR__."/../repository/WpSermonDirectRepository.php";

use SimeonBorko\WpYoutubeAgent\Repository\WpSermonDirectRepository;

$mysqli = getMysqli();

$sermons = (new WpSermonDirectRepository($mysqli))->findByPlaylistId($playlistId);

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
foreach ($sermons as $s):
?>
    <tr>
        <td><?= $s->id ?></td>
        <td><?= $s->title ?></td>
        <td><?= $s->speaker ?></td>
        <td><div class="cut-text"><?= join(", ", $s->tags) ?></div></td>
        <td>
            <?php if ($s->videoId): ?>
            <a href="https://youtu.be/<?= $s->videoId ?>" target="_blank"><?= $s->videoId ?></a>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($s->audioUrl): ?>
            <a href="<?= $s->audioUrl ?>" target="_blank">Audio</a>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($s->imageUrl): ?>
            <img src="<?= $s->imageUrl ?>">
            <?php endif; ?>
        </td>
    </tr>
<?php
endforeach;
?>
</table>
