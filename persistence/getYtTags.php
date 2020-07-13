<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

// param service: Google Service Youtube instance
// param playlistId: video id
// return: list of tags
function getYtTags($service, $videoId) {
  $queryParams = array(
    'id' => $videoId
  );
  $response = $service->videos->listVideos('snippet', $queryParams);
  if (\count($response->items) > 0) {
      $tags = $response->items[0]->snippet->tags;
  } else {
      $tags = array();
  }
  return $tags;
}
