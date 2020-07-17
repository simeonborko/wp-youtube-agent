<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

use SimeonBorko\WpYoutubeAgent\Entity;

class AudioRepository
{
  // param rssUrl: string
  // return: list of Audio
  public function findAll($rssUrl)
  {
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
}
