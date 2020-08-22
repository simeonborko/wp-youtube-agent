<?php

namespace SimeonBorko\WpYoutubeAgent\Translator;

require_once __DIR__."/../entity/WpSermon.php";
require_once __DIR__."/../entity/WpPlaylist.php";

use SimeonBorko\WpYoutubeAgent\Entity\WpPlaylist;
use SimeonBorko\WpYoutubeAgent\Entity\WpSermon;

class EntityTranslator
{
  // translate YtVideo to WpSermon
  public function translateVideo($yt)
  {
    $wp = new WpSermon();
    $wp->title    = $yt->title;
    $wp->description = $yt->description;
    $wp->ytImageUrl  = $yt->imageUrl;
    $wp->tags     = $yt->tags;
    $wp->videoId  = $yt->id;
    return $wp;
  }
  
  // translate YtPlaylist to WpPlaylist
  public function translatePlaylist($yt)
  {
    $wp = new WpPlaylist();
    $wp->title = $yt->title;
    $wp->description = $yt->description;
    $wp->youtubeId = $yt->id;
    if ($yt->videos) {
      $wp->sermons = \array_map(array($this, 'translateVideo'), $yt->videos);
    }
    return $wp;
  }
}
