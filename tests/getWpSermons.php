<?php

if (!$_GET["playlistId"]) {
    echo "Set playlistId";
    die();
}

$playlistId = $_GET["playlistId"];

require_once "../entity/WpSermon.php";
require_once "../persistence/getWpSermons.php";

use SimeonBorko\WpYoutubeAgent\Persistence;

$mysqli = new mysqli("127.0.0.1", "wpuser", "wppassword", "wpdb", 3307);

$sermons = Persistence\getWpSermons($mysqli, $playlistId);

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
