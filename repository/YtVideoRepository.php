<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/../entity/YtVideo.php";

use SimeonBorko\WpYoutubeAgent\Entity;

class YtVideoRepository
{
  protected $service;
  
  // param service: Google Service Youtube instance
  public function __construct($service)
  {
    $this->service = $service;
  }
  
  // param playlistId: playlist id
  // return: list of YtVideo
  public function findByPlaylistId($playlistId) {
    $queryParams = array(
      'playlistId' => $playlistId,
      'maxResults' => 100
    );
    $videos = array();
    $response = $this->getResponse($queryParams);
    while ($response->nextPageToken) {
      $this->processItems($videos, $response->items);
      $queryParams['pageToken'] = $response->nextPageToken;
      $response = $this->getResponse($queryParams);
    }
    $this->processItems($videos, $response->items);
    return $videos;
  }

  protected function getResponse($queryParams) {
    return $this->service->playlistItems->listPlaylistItems('snippet,status', $queryParams);
  }

  // param videos: list where YtVideo objects will be saved
  // param items: list of items from response
  protected function processItems(&$videos, $items) {
    foreach ($items as $item) {
      if ($item->status->privacyStatus == 'public') {
        $v = new Entity\YtVideo();
        $v->id = $item->snippet->resourceId->videoId;
        $v->title = $item->snippet->title;
        $v->description = $item->snippet->description;
        if (isset($item->snippet->thumbnails->standard)) {
          $v->imageUrl = $item->snippet->thumbnails->standard->url;
        }
        $videos[] = $v;
      }
    }
  }
}
