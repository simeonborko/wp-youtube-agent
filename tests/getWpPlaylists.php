<?php

require_once "../entity/WpPlaylist.php";
require_once "../persistence/getWpPlaylists.php";

use SimeonBorko\WpYoutubeAgent\Persistence;

$mysqli = new mysqli("127.0.0.1", "wpuser", "wppassword", "wpdb", 3307);

$playlists = Persistence\getWpPlaylists($mysqli);

?>

<table border="1">
<?php
foreach ($playlists as $p):
?>
    <tr>
        <td>
            <a href="./getWpSermons.php?playlistId=<?= $p->id ?>" target="_blank"><?= $p->id ?></a>
        </td>
        <td><?= $p->title ?></td>
        <td><?= $p->description ?></td>
        <td>
            <?php if ($p->imageUrl): ?>
                <img width="150" src="<?= $p->imageUrl ?>">
            <?php endif; ?>
        <td>
    </tr>
<?php
endforeach;
?>
</table>


<?php

$mysqli->close();
