<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/WpPlaylistRepository.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";
require_once __DIR__."/../matching/PlaylistMatching.php";

use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Matching\PlaylistMatching;

$mysqli = getMysqli();
$wpPlaylists = (new WpPlaylistRepository($mysqli))->findAll();

$service = getYoutubeService();
$channelId = getChannelId();
$ytPlaylists = (new YtPlaylistRepository($service))->findByChannelId($channelId);

$result = (new PlaylistMatching)->match($wpPlaylists, $ytPlaylists);

print_r($result);
