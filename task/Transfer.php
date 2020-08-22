<?php

namespace SimeonBorko\WpYoutubeAgent\Task;

use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtTagRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Translator\EntityTranslator;

require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../repository/YtTagRepository.php";
require_once __DIR__."/../repository/WpSermonRepository.php";
require_once __DIR__."/../translator/EntityTranslator.php";
require_once __DIR__."/StatusUpdater.php";

class Transfer
{
  private $ytVideoRepo;
  private $ytTagRepo;
  private $wpSermonRepo;
  private $entityTranslator;
  private $statusUpdater;
  
  public function __construct($service, $mysqli)
  {
    $this->ytVideoRepo = new YtVideoRepository($service);
    $this->ytTagRepo = new YtTagRepository($service);
    $this->wpSermonRepo = new WpSermonRepository($mysqli);
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
      $transferredVideos += $this->transferVideos($plStatus);
    }
    return array('video' => $transferredVideos, 'playlist' => 0);
  }
  
  private function transferVideos($plStatus)
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
      // add tags
      foreach ($ytVideos as $yt) {
        $yt->tags = $this->ytTagRepo->findByVideoId($yt->id);
      }
      // translate YT videos to WP sermons
      $wpSermons = \array_map(array($this->entityTranslator, 'translateVideo'), $ytVideos);
      // check number of sermons
      if (\count($wpSermons) != \count($plStatus->ytOnlyVideoIdList)) {
        throw new \Exception(\sprintf(
          "Count of WP sermons (%d) does not match with count of ytOnlyVideoIdList (%d)",
          \count($wpSermons), \count($plStatus->ytOnlyVideoIdList)
        ));
      }
      // save sermons
      foreach ($wpSermons as $wp) {
        $this->wpSermonRepo->save($wp, false);
        $this->wpSermonRepo->addToPlaylist($wp->id, $plStatus->wpId);
      }
      return \count($wpSermons);
    }
  }
}
