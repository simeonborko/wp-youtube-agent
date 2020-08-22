<?php

namespace SimeonBorko\WpYoutubeAgent\Entity;

// both $wpId and $ytId are non-null
// as PlaylistStatus is created only for matched playlists

class PlaylistStatus {
  public $wpId;
  public $ytId;
  public $matchedVideoCount;
  public $wpOnlyVideoIdList; // sermons
  public $ytOnlyVideoIdList;
}
