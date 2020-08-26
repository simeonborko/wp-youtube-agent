<?php

namespace SimeonBorko\WpYoutubeAgent\Task;

use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtTagRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;
use SimeonBorko\WpYoutubeAgent\Translator\EntityTranslator;

require_once __DIR__."/../repository/YtPlaylistRepository";
require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../repository/YtTagRepository.php";
require_once __DIR__."/../repository/WpPlaylistRepository.php";
require_once __DIR__."/../repository/WpSermonRepository.php";
require_once __DIR__."/../translator/EntityTranslator.php";
require_once __DIR__."/StatusUpdater.php";

class Transfer
{
  private $ytPlaylistRepo;
  private $ytVideoRepo;
  private $ytTagRepo;
  private $wpPlaylistRepo;
  private $wpSermonRepo;
  private $entityTranslator;
  private $statusUpdater;
  
  public function __construct($service, $mysqli)
  {
    $this->ytPlaylistRepo = new YtPlaylistRepository($service);
    $this->ytVideoRepo = new YtVideoRepository($service);
    $this->ytTagRepo = new YtTagRepository($service);
    $this->wpSermonRepo = new WpSermonRepository($mysqli);
    $this->wpPlaylistRepo = new WpPlaylistRepository($mysqli);
    $this->entityTranslator = new EntityTranslator;
    $this->statusUpdater = new StatusUpdater($service, $mysqli);
  }
  
  public function doTransfer($status = null)
  {
    if ($status == null) {
      $status = $this->statusUpdater->updateStatus();
    }
    $transferredVideos = 0;
    foreach ($status->matchedPlaylistStatusList as $plStatus) {
      $transferredVideos += $this->transferVideosFromPlStatus($plStatus);
    }
    $status = $this->statusUpdater->updateStatus();
    return array(
      'video' => $transferredVideos,
      'playlist' => 0,
      'status' => $status
    );
  }
  
  private function transferPlaylists($status)
  {
    $allWpWaived = \array_reduce(
      $this->wpPlaylistRepo->getByIdList($status->wpOnlyPlaylistIdList),
      function($carry, $wpPl) {
        return $carry && $wpPl->waiveMatch;
      },
      true
    );
    if ($allWpWaived) {
      $ytPlaylists = \array_map(array($this->ytPlaylistRepo, 'getById'), $status->ytOnlyPlaylistIdList);
      foreach ($ytPlaylists as $ytPl) {
        $ytVideos = $this->ytVideoRepo->findByPlaylistId($ytPl->id);
        if ($ytVideos) {
          $wpPl = $this->entityTranslator->translatePlaylist($ytPl);
          // TODO add image url of the first video
          $this->wpPlaylistRepo->save($wpPl);
          $wpSermons = $this->transferVideos($ytVideos, $wpPl->id);
          // ytVideos were sorted by publishedAt,
          // transfering didn't change order, so wpSermons are ordered as well
          // we use the first video to get image for the whole playlist
        }
      }
    }
  }
  
  // transfer videos, return saved sermons
  private function transferVideos($ytVideos, $wpPlaylistId)
  {
    // add tags
    foreach ($ytVideos as $yt) {
      $yt->tags = $this->ytTagRepo->findByVideoId($yt->id);
    }
    // translate YT videos to WP sermons
    $wpSermons = \array_map(array($this->entityTranslator, 'translateVideo'), $ytVideos);
    // save sermons
    foreach ($wpSermons as $wp) {
      $this->wpSermonRepo->save($wp, false);
      $this->wpSermonRepo->addToPlaylist($wp->id, $wpPlaylistId);
    }
    return $wpSermons;
  }
  
  private function transferVideosFromPlStatus($plStatus)
  {
    if (\count($plStatus->wpOnlyVideoIdList) == 0 && \count($plStatus->ytOnlyVideoIdList) > 0) {
      // now we know that there is no sermon in WP which is not on YT
      // and so we can transfer all videos from YT to WP
      $ytVideos = $this->ytVideoRepo->findByPlaylistId($plStatus->ytId);
      $ytIdList = $plStatus->ytOnlyVideoIdList;
      $ytVideos = \array_filter($ytVideos, (
        function ($yt) use ($ytIdList) {
          return \in_array($yt->id, $ytIdList);
        }
      ));
      // check number of videos
      if (\count($ytVideos) != \count($plStatus->ytOnlyVideoIdList)) {
        throw new \Exception(\sprintf(
          "Count of YouTube videos (%d) does not match with ytOnlyVideoIdList count (%d)",
          \count($ytVideos), \count($plStatus->ytOnlyVideoIdList)
        ));
      }
      $wpSermons = $this->transferVideos($ytVideos, $plStatus->wpId);
      return \count($wpSermons);
    }
  }
}
