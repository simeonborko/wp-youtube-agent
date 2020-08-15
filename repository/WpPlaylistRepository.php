<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/WpPlaylistDirectRepository.php";

class WpPlaylistRepository extends WpPlaylistDirectRepository
{
  // save a new playlist as a term
  public function save($playlist)
  {
    $args = array("description" => $playlist->description);
    $result = \wp_insert_term($playlist->title, WP_PLAYLIST_TAXONOMY, $args);
    if (!\is_array($result)) {
      throw new \Exception("Playlist could not be saved");
    }
    $playlist->id = $result["term_id"];
    // image
    if ($playlist->imageUrl) {
      $option_name = WP_PLAYLIST_TAXONOMY . $playlist->id . WP_PLAYLIST_OPTION_IMAGE_URL_SUFFIX;
      if (!\update_option($option_name, $playlist->imageUrl)) {
        echo "Warning: Image for playlist " . $playlist->title . "could not been added";
      }
    }
    // youtube playlist id
    $this->saveYoutubeId($playlist);
  }
  
  public function saveYoutubeId($playlist)
  {
    if ($playlist->youtubeId) {
      $option_name = WP_PLAYLIST_TAXONOMY . $playlist->id . WP_PLAYLIST_OPTION_YOUTUBE_ID_SUFFIX;
      if(!\update_option($option_name, $playlist->youtubeId)) {
        echo "Warning: Youtube ID for playlist " . $playlist->title . "could not been added";
      }
    }
  }
  
  
  
  
}
