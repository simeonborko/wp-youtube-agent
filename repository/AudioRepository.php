<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

use SimeonBorko\WpYoutubeAgent\Entity;

// param rssUrl: string
// return: list of Audio
function getAudios($rssUrl) {
  $rssStr = \file_get_contents($rssUrl);
  $root = new \SimpleXMLElement($rssStr);
  $list = array();
  foreach ($root->channel->item as $item) {
    $audio = new Entity\Audio();
    $audio->id = (string) $item->guid;
    $audio->title = (string) $item->title;
    $audio->audioUrl = (string) $item->enclosure['url'];
    $audio->description = (string) $item->description;
    $list[] = $audio;
  }
  return $list;
}
