<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/YtPlaylistStatelessRepository.php";

class YtPlaylistRepository extends YtPlaylistStatelessRepository
{
  const NAME = 'YtPlaylistRepository';
  const CH_TO_PL = 'channelToPlaylistIdList';
  const PLAYLIST = 'playlist';
  
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
      $_SESSION[self::NAME] = array(
        self::CH_TO_PL => array(),
        self::PLAYLIST => array()
      );
    }
  }
  
  public function findByChannelId($channelId)
  {
    if ($this->loadFromSession && isset($_SESSION[self::NAME][self::CH_TO_PL][$channelId])) {
      $playlists = \array_map(array($this, 'getById'), $_SESSION[self::NAME][self::CH_TO_PL][$channelId]);
    } else {
      $playlists = parent::findByChannelId($channelId);
      $idList = array();
      foreach ($playlists as $pl) {
        $idList[] = $pl->id;
        $_SESSION[self::NAME][self::PLAYLIST][$pl->id] = $pl;
      }
      $_SESSION[self::NAME][self::CH_TO_PL][$channelId] = $idList;
    }
    return $playlists;
  }
  
  public function getById($playlistId)
  {
    if ($this->loadFromSession && isset($_SESSION[self::NAME][self::PLAYLIST][$playlistId])) {
      $playlist = $_SESSION[self::NAME][self::PLAYLIST][$playlistId];
    } else {
      $playlist = parent::getById($playlistId);
      $_SESSION[self::NAME][self::PLAYLIST][$playlistId] = $playlist;
    }
    return $playlist;
  }
}
