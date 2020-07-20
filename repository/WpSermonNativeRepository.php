<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/WpSermonRepository.php";

class WpSermonNativeRepository
{
  public function save($sermon)
  {
    // id, title, description
    $result = \wp_insert_post(array(
        'ID' => $sermon->id,
        'post_title' => $sermon->title,
        'post_content' => $sermon->description
    ));
    if (!$result) {
        throw new \Exception("Sermon could not be saved");
    }
    $sermon->id = $result;
    // TODO post_status is default draft
    // https://developer.wordpress.org/reference/functions/wp_insert_post/
    
    // imageUrl
    if ($sermon->imageUrl && !\has_post_thumbnail($sermon->id)) {
      $post = \get_post($sermon->id);
      $this->setThumbnailFromUrl($sermon->id, $post->post_name, $sermon->imageUrl);
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
  
  protected function setThumbnailFromUrl($sermonId, $slug, $imageUrl)
  {
    $imageContents = \file_get_contents(\urlencode($imageUrl));

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
  
  protected function addMetaIfNotExists($sermonId, $metaKey) {
      
  }
  
  protected function saveTags($sermonId, $tags)
  {
    
  }
  
  protected function saveSpeaker($sermonId, $speaker)
  {
    
  }
  
  protected function create_term($tag_name, $taxonomy)
  {
    $id = \term_exists( $tag_name, $taxonomy );
    if ( $id ) {
        return $id;
    }
    return \wp_insert_term( $tag_name, $taxonomy );
  }
}
