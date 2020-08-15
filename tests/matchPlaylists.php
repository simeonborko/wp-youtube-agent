<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/WpPlaylistDirectRepository.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";
require_once __DIR__."/../matching/PlaylistMatching.php";

use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistDirectRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Matching\PlaylistMatching;

$mysqli = getMysqli();
$wpPlaylists = (new WpPlaylistDirectRepository($mysqli))->findAll();

$service = getYoutubeService();
$channelId = getChannelId();
$ytPlaylists = (new YtPlaylistRepository($service))->findByChannelId($channelId);

$result = (new PlaylistMatching)->match($wpPlaylists, $ytPlaylists);

print_r($result);
