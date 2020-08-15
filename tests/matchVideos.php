<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/WpSermonRepository.php";
require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../matching/VideoMatching.php";

use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Matching\VideoMatching;

$wpPlaylistId = 8;
$ytPlaylistId = "PLw8-7yWlpvFT5f3v03jmFLwd3a0kJldG_";

$mysqli = getMysqli();
$wpSermons = (new WpSermonRepository($mysqli))->findByPlaylistId($wpPlaylistId);

$service = getYoutubeService();
$channelId = getChannelId();
$ytVideos = (new YtVideoRepository($service))->findByPlaylistId($ytPlaylistId);

$result = (new VideoMatching)->match($wpSermons, $ytVideos);

print_r($result);
