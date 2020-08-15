<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../entity/WpPlaylist.php";
require_once __DIR__."/../repository/WpPlaylistDirectRepository.php";

use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistDirectRepository;

$mysqli = getMysqli();

$playlists = (new WpPlaylistDirectRepository($mysqli))->findAll();

?>

<table border="1">
<?php
foreach ($playlists as $p):
?>
    <tr>
        <td>
            <a href="./getWpSermons.php?playlistId=<?= $p->id ?>" target="_blank"><?= $p->id ?></a>
            <?php if ($p->youtubeId): ?>(<?= $p->youtubeId ?>)<?php endif ?>
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
