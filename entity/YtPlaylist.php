<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

class YtPlaylist {
  public $id;
  public $title;
  public $description;
  public $itemCount; // number of videos
  public $videos; // list of YtVideo
}
