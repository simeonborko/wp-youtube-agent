<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

class YtTagRepository
{
  protected $service;
  
  // param service: Google Service Youtube instance
  public function __construct($service)
  {
    $this->service = $service;
  }

  // param videoId: video id
  // return: list of tags
  public function findByVideoId($videoId) {
    $queryParams = array(
      'id' => $videoId
    );
    $response = $this->service->videos->listVideos('snippet', $queryParams);
    if (\count($response->items) > 0) {
      $tags = $response->items[0]->snippet->tags;
    } else {
      $tags = array();
    }
    return $tags;
  }
}
