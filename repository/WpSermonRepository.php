<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/WpSermonDirectRepository.php";
require_once __DIR__."/ImageTool.php";

class WpSermonRepository extends WpSermonDirectRepository
{
  const SERMON_POST_TYPE = 'sermons';
  const TARGET_IMAGE_WIDTH = 600;
  
  public $resizeImage = true;
  
  public function save($sermon, $publish=true)
  {
    // id, title, description
    $result = \wp_insert_post(array(
        'ID' => $sermon->id,
        'post_title' => $sermon->title,
        'post_name' => \sanitize_title($sermon->title),
        'post_content' => $sermon->description,
        'post_type' => self::SERMON_POST_TYPE,
        'post_status' => $publish ? 'publish' :  'draft'
    ));
    if (!$result) {
        throw new \Exception("Sermon could not be saved");
    }
    $sermon->id = $result;
    
    // imageUrl
    if ($sermon->ytImageUrl && !\has_post_thumbnail($sermon->id)) {
      $post = \get_post($sermon->id);
      $this->setThumbnailFromUrl($sermon->id, $post->post_name, $sermon->ytImageUrl);
      $sermon->imageUrl = $this->getImageUrl($post);
    }
    
    // speaker
    if (\is_string($sermon->speaker)) {
        \wp_set_object_terms($sermon->id, $sermon->speaker, WpSermonDirectRepository::TAX_SPEAKER);
    }
    
    // tags
    if (\is_array($sermon->tags)) {
        \wp_set_object_terms($sermon->id, $sermon->tags, WpSermonDirectRepository::TAX_TAG);
    }
    
    // video id/url
    if ($sermon->videoId) {
      \add_post_meta(
        $sermon->id,
        WpSermonDirectRepository::META_KEY_VIDEO,
        $sermon->getVideoUrl(),
        true
      );
    }
    
    // audioUrl
    if ($sermon->audioUrl) {
      \add_post_meta(
        $sermon->id,
        WpSermonDirectRepository::META_KEY_AUDIO,
        $sermon->audioUrl,
        true
      );
    }
  }
  
  public function addToPlaylist($sermonId, $playlistId)
  {
    \wp_set_object_terms((int) $sermonId, (int) $playlistId, WP_PLAYLIST_TAXONOMY);
  }
  
  public function getImageUrl($post)
  {
    return \get_the_post_thumbnail_url($post);
  }
  
  protected function setThumbnailFromUrl($sermonId, $slug, $imageUrl)
  {
    $imgTool = new ImageTool($imageUrl);
    if ($this->resizeImage) {
      $imgTool->scale(self::TARGET_IMAGE_WIDTH);
    }
    // upload attachment
    $attachmentId = $imgTool->uploadAttachment($slug, $sermonId);
    // finally, associate attachment id to post id
    $success = \set_post_thumbnail( $sermonId, $attachmentId );
    return $success;
  }
}
