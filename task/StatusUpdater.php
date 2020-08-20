<?php

namespace SimeonBorko\WpYoutubeAgent\Task;

require_once __DIR__."/../settings.php";
require_once __DIR__."/../entity/Status.php";
require_once __DIR__."/../entity/PlaylistStatus.php";
require_once __DIR__."/../repository/StatusRepository.php";
require_once __DIR__."/../repository/WpPlaylistRepository.php";
require_once __DIR__."/../repository/YtPlaylistRepository.php";
require_once __DIR__."/../repository/WpSermonRepository.php";
require_once __DIR__."/../repository/YtVideoRepository.php";
require_once __DIR__."/../matching/PlaylistMatching.php";
require_once __DIR__."/../matching/VideoMatching.php";

use SimeonBorko\WpYoutubeAgent\Settings;
use SimeonBorko\WpYoutubeAgent\Entity\Status;
use SimeonBorko\WpYoutubeAgent\Entity\PlaylistStatus;
use SimeonBorko\WpYoutubeAgent\Repository\StatusRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtPlaylistRepository;
use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;
use SimeonBorko\WpYoutubeAgent\Repository\YtVideoRepository;
use SimeonBorko\WpYoutubeAgent\Matching\PlaylistMatching;
use SimeonBorko\WpYoutubeAgent\Matching\VideoMatching;

class StatusUpdater
{
  private $statusRepo;
  private $wpPlaylistRepo;
  private $ytPlaylistRepo;
  private $wpSermonRepo;
  private $ytVideoRepo;
  private $playlistMatching;
  private $videoMatching;
  
  public function __construct($service, $mysqli)
  {
    $this->statusRepo = new StatusRepository;
    $this->wpPlaylistRepo = new WpPlaylistRepository($mysqli);
    $this->ytPlaylistRepo = new YtPlaylistRepository($service);
    $this->wpSermonRepo = new WpSermonRepository($mysqli);
    $this->ytVideoRepo = new YtVideoRepository($service);
    $this->playlistMatching = new PlaylistMatching;
    $this->videoMatching = new VideoMatching;
  }
  
  public function updateStatus()
  {
    $wpPlaylists = $this->wpPlaylistRepo->findAll();
    $ytPlaylists = $this->ytPlaylistRepo->findByChannelId(Settings\CHANNEL_ID);
    $result = $this->playlistMatching->match($wpPlaylists, $ytPlaylists);
    $status = new Status;
    $status->matchedPlaylistStatusList = \array_map(array($this, 'getPlaylistStatus'), $result['matches']);
    $status->wpOnlyPlaylistIdList = \array_map(array($this, 'getId'), $result['wpOnly']);
    $status->ytOnlyPlaylistIdList = \array_map(array($this, 'getId'), $result['ytOnly']);
    $this->statusRepo->save($status);
    return $status;
  }
  
  private function getPlaylistStatus($match)
  {
    $wpPlaylist = $match['wp'];
    $ytPlaylist = $match['yt'];
    $wpSermons = $this->wpSermonRepo->findByPlaylistId($wpPlaylist->id);
    $ytVideos = $this->ytVideoRepo->findByPlaylistId($ytPlaylist->id);
    $result = $this->videoMatching->match($wpSermons, $ytVideos);
    $plStatus = new PlaylistStatus;
    $plStatus->wpId = $wpPlaylist->id;
    $plStatus->ytId = $ytPlaylist->id;
    $plStatus->matchedVideoCount = \count($result['matches']);
    $plStatus->wpOnlyVideoIdList = \array_map(array($this, 'getId'), $result['wpOnly']);
    $plStatus->ytOnlyVideoIdList = \array_map(array($this, 'getId'), $result['ytOnly']);
    return $plStatus;
  }
  
  private function getId($entity)
  {
    return $entity->id;
  }
}
