<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class WpSermon {
  public $id;
  public $title;
  public $description;
  
  public $ytImageUrl; // YouTube image URL
  public $imageUrl;   // saved WordPress image URL
  
  public $speaker; // string
  public $tags;    // list of strings

  public $videoId; // id of YtVideo

  public $audioId; // id of Audio
  public $audioUrl;
  
  public function getVideoUrl()
  {
    return 'https://youtu.be/'.$this->videoId;
  }
}
