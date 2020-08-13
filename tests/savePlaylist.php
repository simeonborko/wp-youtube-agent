<?php

use SimeonBorko\WpYoutubeAgent\Entity\WpPlaylist;
use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistNativeRepository;

require_once __DIR__."/saveSermon.php";
require_once __DIR__."/../entity/WpPlaylist.php";
require_once __DIR__."/../repository/WpPlaylistNativeRepository.php";

$playlist = new WpPlaylist();
$playlist->title = "My Playlist";
$playlist->description = "This is my sample playlist.";
$playlist->imageUrl = "https://maxcdn.icons8.com/iOS7/PNG/512/Music/playlist-512.png";
$playlist->youtubeId = "12345";
$playlist->sermons = array($sermon);

$playlistRepo = new WpPlaylistNativeRepository();
$playlistRepo->save($playlist);
$repo->addToPlaylist($sermon->id, $playlist->id);

echo "New playlist ID is ".$playlist->id;
