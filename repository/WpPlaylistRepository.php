<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/WpPlaylistDirectRepository.php";
require_once __DIR__."/ImageTool.php";

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
    // image url
    $this->saveImageUrl($playlist);
    // youtube playlist id
    $this->saveYoutubeId($playlist);
    // waive match
    $this->saveWaiveMatch($playlist);
  }
  
  public function saveImageUrl($playlist, $scale = true)
  {
    if ($playlist->imageUrl) {
      if ($scale) {
        $this->scaleImage($playlist);
      }
      $this->setOption($playlist, WP_PLAYLIST_OPTION_IMAGE_URL_SUFFIX, $playlist->imageUrl, "Image URL");
    }
  }
  
  public function saveYoutubeId($playlist)
  {
    if ($playlist->youtubeId) {
      $this->setOption($playlist, WP_PLAYLIST_OPTION_YOUTUBE_ID_SUFFIX, $playlist->youtubeId, "Youtube ID");
    }
  }
  
  public function saveWaiveMatch($playlist)
  {
    // to unwaive, waiveMatch has to be set to false
    if ($playlist->waiveMatch !== null) {
      $this->setOption($playlist, WP_PLAYLIST_OPTION_WAIVE_MATCH_SUFFIX, (int) $playlist->waiveMatch, "Waive match");
    }
  }
    
  private function setOption($playlist, $option_suffix, $option_value, $msg_name)
  {
    $option_name = WP_PLAYLIST_TAXONOMY . $playlist->id . $option_suffix;
    if (!\update_option($option_name, $option_value)) {
      echo \sprintf("Warning: %s for playlist %s could not been added", $msg_name, $playlist->title);
    }
  }
  
  private function scaleImage($playlist)
  {
    $imgTool = new ImageTool($playlist->imageUrl);
    $modified = $imgTool->scale(WP_PLAYLIST_IMAGE_WIDTH, WP_PLAYLIST_IMAGE_HEIGHT);
    if ($modified) {
      $term = \get_term((int) $playlist->id);
      $attachmentId = $imgTool->uploadAttachment($term->slug);
      $url = \wp_get_attachment_url($attachmentId);
      if (!$url) {
        throw new \Exception("Could not scale image");
      }
      $playlist->imageUrl = $url;
    }
  }
  
}
