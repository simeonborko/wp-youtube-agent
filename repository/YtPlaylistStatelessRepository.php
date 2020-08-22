<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/../entity/YtPlaylist.php";

use SimeonBorko\WpYoutubeAgent\Entity;

class YtPlaylistStatelessRepository
{
  protected $service;
  
  // param service: Google Service Youtube instance
  public function __construct($service)
  {
    $this->service = $service;
  }

  // param channelId: channel id
  // return: list of YtPlaylist
  public function findByChannelId($channelId)
  {
    $queryParams = array(
      'channelId' => $channelId,
      'maxResults' => 100
    );
    return $this->find($queryParams);
  }
  
  public function getById($playlistId)
  {
    $queryParams = array('id' => $playlistId);
    $result = $this->find($queryParams);
    if (\count($result) != 1) {
      throw new \Exception("Number of playlists for ID ".$playlistId." is ".\count($result));
    }
    return current($result);
  }
  
  private function find($queryParams)
  {
    $playlists = array();
    $response = $this->getResponse($queryParams);
    while ($response->nextPageToken) {
      $this->processItems($playlists, $response->items);
      $queryParams['pageToken'] = $response->nextPageToken;
      $response = $this->getResponse($queryParams);
    }
    $this->processItems($playlists, $response->items);
    return $playlists;
  }

  private function getResponse($queryParams)
  {
    return $this->service->playlists->listPlaylists('snippet,contentDetails', $queryParams);
  }

  // param playlists: list where YtPlaylist objects will be saved
  // param items: list of items from response
  private function processItems(&$playlists, $items)
  {
    foreach ($items as $item) {
      $p = new Entity\YtPlaylist();
      $p->id = $item->id;
      $p->title = $item->snippet->title;
      $p->description = $item->snippet->description;
      $p->itemCount = $item->contentDetails->itemCount;
      $playlists[] = $p;
    }
  }
}
