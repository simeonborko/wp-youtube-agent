<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class WpPlaylist {
  public $id;
  public $title;
  public $description;
  public $imageUrl;
  
  public $youtubeId; // id of YtPlaylist
  
  // waiveMatch uses three-state logic:
  // - true: we acknowledge no match with YouTube playlist and it is no problem (1 is saved in DB)
  // - false: 0 is saved in DB
  // - null: nothing is saved in DB
  public $waiveMatch;
  
  public $sermons; // list of WpSermon
}
