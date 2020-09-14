<?php

namespace SimeonBorko\WpYoutubeAgent\Task;

use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtTagRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;
use SimeonBorko\WpYoutubeAgent\Translator\EntityTranslator;

require_once __DIR__."/../repository/YtPlaylistRepository.php";
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
  
  private $transferredVideos;
  private $transferredPlaylists;
  
  public function __construct($service, $mysqli)
  {
    $this->ytPlaylistRepo = new YtPlaylistRepository($service);
    $this->ytVideoRepo = new YtVideoRepository($service);
    $this->ytTagRepo = new YtTagRepository($service);
    $this->wpSermonRepo = new WpSermonRepository($mysqli);
    $this->wpPlaylistRepo = new WpPlaylistRepository($mysqli);
    $this->entityTranslator = new EntityTranslator;
    $this->statusUpdater = new StatusUpdater($service, $mysqli);
    
    $this->transferredVideos = 0;
    $this->transferredPlaylists = 0;
  }
  
  public function doTransfer($status = null)
  {
    if ($status == null) {
      $status = $this->statusUpdater->updateStatus();
    }
    foreach ($status->matchedPlaylistStatusList as $plStatus) {
      $this->transferVideosFromPlStatus($plStatus);
    }
    $this->transferAllPlaylists($status);
    $status = $this->statusUpdater->updateStatus();
    return array(
      'video' => $this->transferredVideos,
      'playlist' => $this->transferredPlaylists,
      'status' => $status
    );
  }
  
  public function transferPlaylist($ytPlaylist)
  {
    $ytVideos = $this->ytVideoRepo->findByPlaylistId($ytPlaylist->id);
    if ($ytVideos) {
      $wpPl = $this->entityTranslator->translatePlaylist($ytPlaylist);
      // ytVideos were sorted by publishedAt,
      // we use the first video to get image for the whole playlist
      $wpPl->imageUrl = $ytVideos[0]->imageUrl;
      // save playlist
      $this->wpPlaylistRepo->save($wpPl);
      $this->transferredPlaylists += 1;
      // transfer videos
      $wpSermons = $this->transferVideos($ytVideos, $wpPl->id);
      return \count($wpSermons);
    } else {
      return null;
    }
  }
  
  private function transferAllPlaylists($status)
  {
    // wpOnly are those that are only in WordPress and not on Youtube
    // all of them have to be waived before playlists can be transferred
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
        $this->transferPlaylist($ytPl);
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
    $this->transferredVideos += \count($wpSermons);
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
      $this->transferVideos($ytVideos, $plStatus->wpId);
    }
  }
}
