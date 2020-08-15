<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";
require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../repository/YtTagRepository.php";
require_once __DIR__."/../translator/EntityTranslator.php";

use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtTagRepository;
use SimeonBorko\WpYoutubeAgent\Translator\EntityTranslator;

$ytPlaylistId = "PLw8-7yWlpvFT5f3v03jmFLwd3a0kJldG_";

$service = getYoutubeService();
$playlist = (new YtPlaylistRepository($service))->getById($ytPlaylistId);
$playlist->videos = (new YtVideoRepository($service))->findByPlaylistId($ytPlaylistId);
$tagRepo = new YtTagRepository($service);
foreach ($playlist->videos as $video) {
  $video->tags = $tagRepo->findByVideoId($video->id);
}

$wordpressPlaylist = (new EntityTranslator)->translatePlaylist($playlist);

print_r($wordpressPlaylist);
