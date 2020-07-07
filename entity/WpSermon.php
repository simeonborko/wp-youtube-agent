<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class WpSermon {
  public $id;
  public $title;
  public $description;
  public $imageUrl;
  
  public $speaker; // string
  public $tags;    // list of strings

  public $videoId; // id of YtVideo

  public $audioId; // id of Audio
  public $audioUrl;
}
