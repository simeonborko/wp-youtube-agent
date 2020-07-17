<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

use SimeonBorko\WpYoutubeAgent\Entity;

// param service: Google Service Youtube instance
// param playlistId: playlist id
// return: list of YtVideo
function getYtVideos($service, $playlistId) {
  $queryParams = array(
    'playlistId' => $playlistId,
    'maxResults' => 100
  );
  $videos = array();
  $response = getResponse($service, $queryParams);
  while ($response->nextPageToken) {
    processItems($videos, $response->items);
    $queryParams['pageToken'] = $response->nextPageToken;
    $response = getResponse($service, $queryParams);
  }
  processItems($videos, $response->items);
  return $videos;
}

function getResponse($service, $queryParams) {
  return $service->playlistItems->listPlaylistItems('snippet,status', $queryParams);
}

// param videos: list where YtVideo objects will be saved
// param items: list of items from response
function processItems(&$videos, $items) {
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
