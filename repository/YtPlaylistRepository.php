<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

use SimeonBorko\WpYoutubeAgent\Entity;

class YtPlaylistRepository
{
  protected $service;
  
  // param service: Google Service Youtube instance
  public function __construct($service)
  {
    $this->service = $service;
  }

  // param channelId: channel id
  // return: list of YtPlaylist
  public function findByChannelId($channelId) {
    $queryParams = array(
      'channelId' => $channelId,
      'maxResults' => 100
    );
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

  protected function getResponse($queryParams) {
    return $this->service->playlists->listPlaylists('snippet,contentDetails', $queryParams);
  }

  // param playlists: list where YtPlaylist objects will be saved
  // param items: list of items from response
  protected function processItems(&$playlists, $items) {
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
