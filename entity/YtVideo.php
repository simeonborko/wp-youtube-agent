<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class YtVideo {
  public $id;
  public $title;
  public $imageUrl;
  public $description;
  public $publishedAt;
  public $tags; // list of strings
  
  public static function compare($a, $b)
  {
    return \strcmp($a->publishedAt, $b->publishedAt);
  }
}
