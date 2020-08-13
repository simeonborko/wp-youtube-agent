<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class WpPlaylist {
  public $id;
  public $title;
  public $description;
  public $imageUrl;
  
  public $youtubeId; // id of YtPlaylist
  
  public $sermons; // list of WpSermon
}
