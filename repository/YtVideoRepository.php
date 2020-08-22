<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/YtVideoStatelessRepository.php";

class YtVideoRepository extends YtVideoStatelessRepository
{
  const NAME = 'YtVideoRepository';
  
  private $loadFromSession;
  
  public function __construct($service, $loadFromSession = true)
  {
    parent::__construct($service);
    $this->loadFromSession = $loadFromSession;
    $this->initSession();
  }
  
  private function initSession()
  {
    if (!\session_id()) {
      \session_start();
    }
    if (!isset($_SESSION[self::NAME])) {
      $_SESSION[self::NAME] = array();
    }
  }
  
  public function findByPlaylistId($playlistId)
  {
    if ($this->loadFromSession && isset($_SESSION[self::NAME][$playlistId])) {
      $videos = $_SESSION[self::NAME][$playlistId];
    } else {
      $videos = parent::findByPlaylistId($playlistId);
      $_SESSION[self::NAME][$playlistId] = $videos;
    }
    return $videos;
  }
}
