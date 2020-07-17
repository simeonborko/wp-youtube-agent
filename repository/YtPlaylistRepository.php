<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

use SimeonBorko\WpYoutubeAgent\Entity;

// param service: Google Service Youtube instance
// param channelId: channel id
// return: list of YtPlaylist
function getYtPlaylists($service, $channelId) {
  $queryParams = array(
    'channelId' => $channelId,
    'maxResults' => 100
  );
  $playlists = array();
  $response = getResponse($service, $queryParams);
  while ($response->nextPageToken) {
    processItems($playlists, $response->items);
    $queryParams['pageToken'] = $response->nextPageToken;
    $response = getResponse($service, $queryParams);
  }
  processItems($playlists, $response->items);
  return $playlists;
}

function getResponse($service, $queryParams) {
  return $service->playlists->listPlaylists('snippet,contentDetails', $queryParams);
}

// param playlists: list where YtPlaylist objects will be saved
// param items: list of items from response
function processItems(&$playlists, $items) {
  foreach ($items as $item) {
    $p = new Entity\YtPlaylist();
    $p->id = $item->id;
    $p->title = $item->snippet->title;
    $p->description = $item->snippet->description;
    $p->itemCount = $item->contentDetails->itemCount;
    $playlists[] = $p;
  }
}
