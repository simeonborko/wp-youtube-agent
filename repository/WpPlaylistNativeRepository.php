<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";

class WpPlaylistNativeRepository
{
  // save playlist as a term
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
      if (!\add_option($option_name, $playlist->imageUrl)) {
        echo "Warning: Image for playlist " . $playlist["title"] . "could not been added";
      }
    }
    // youtube playlist id
    if ($playlist->playlistId) {
      $option_name = WP_PLAYLIST_TAXONOMY . $playlist->id . WP_PLAYLIST_OPTION_YOUTUBE_ID_SUFFIX;
      if(!\add_option($option_name, $playlist->playlistId)) {
        echo "Warning: Youtube ID for playlist " . $playlist["title"] . "could not been added";
      }
    }
  }
  
  
}