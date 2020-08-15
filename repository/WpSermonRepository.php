<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/WpSermonRepository.php";

class WpSermonNativeRepository
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
        \wp_set_object_terms($sermon->id, $sermon->speaker, WpSermonRepository::TAX_SPEAKER);
    }
    
    // tags
    if (\is_array($sermon->tags)) {
        \wp_set_object_terms($sermon->id, $sermon->tags, WpSermonRepository::TAX_TAG);
    }
    
    // video id/url
    if ($sermon->videoId) {
      \add_post_meta(
        $sermon->id,
        WpSermonRepository::META_KEY_VIDEO,
        $sermon->getVideoUrl(),
        true
      );
    }
    
    // audioUrl
    if ($sermon->audioUrl) {
      \add_post_meta(
        $sermon->id,
        WpSermonRepository::META_KEY_AUDIO,
        $sermon->audioUrl,
        true
      );
    }
  }
  
  public function addToPlaylist($sermonId, $playlistId)
  {
    \wp_set_object_terms($sermonId, $playlistId, WP_PLAYLIST_TAXONOMY);
  }
  
  public function getImageUrl($post)
  {
    return \get_the_post_thumbnail_url($post);
  }
  
  protected function setThumbnailFromUrl($sermonId, $slug, $imageUrl)
  {
    if ($this->resizeImage == false) {
      $imageContents = \file_get_contents($imageUrl);
    } else {
      $img = \imagecreatefromjpeg($imageUrl);
      $width = \imagesx($img);
      if ($width > self::TARGET_IMAGE_WIDTH) {
        $newHeight = \round(\imagesy($img) * self::TARGET_IMAGE_WIDTH / $width);
        $img = \imagescale($img, self::TARGET_IMAGE_WIDTH, $newHeight);
      }
      \ob_start();
      \imagejpeg($img);
      $imageContents = \ob_get_clean();
    }
    
    $upload = \wp_upload_bits($slug.'.jpg', null, $imageContents);

    // check and return file type
    $imageFile = $upload['file'];
    $wpFileType = \wp_check_filetype($imageFile, null);

    // Attachment attributes for file
    $attachment = array(
      'post_mime_type' => $wpFileType['type'],  // file type
      'post_title' => \sanitize_file_name($imageFile),  // sanitize and use image name as file name
      'post_content' => '',  // could use the image description here as the content
      'post_status' => 'inherit'
    );

    // insert and return attachment id
    $attachmentId = \wp_insert_attachment( $attachment, $imageFile, $sermonId );
    // finally, associate attachment id to post id
    $success = \set_post_thumbnail( $sermonId, $attachmentId );

    return $success;
  }
}
