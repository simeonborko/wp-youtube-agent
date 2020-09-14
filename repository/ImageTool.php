<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

class ImageTool
{
  private $img;
  
  public function __construct($imageUrl)
  {
    $this->img = \imagecreatefromjpeg($imageUrl);
  }
  
  public function scale($maxWidth, $maxHeight = null)
  {
    $w = \imagesx($this->img);
    $h = \imagesy($this->img);
    $widthRatio = $maxWidth / $w;
    $heightRatio = $maxHeight ? ($maxHeight / $h) : 1;
    $scaleByWidth = false;
    $scaleByHeight = false;
    if ($widthRatio < 1 && $heightRatio < 1) {
      if ($widthRatio < $heightRatio) {
        $scaleByWidth = true;
      } else {
        $scaleByHeight = true;
      }
    } elseif ($widthRatio < 1) {
      $scaleByWidth = true;
    } elseif ($heightRatio < 1) {
      $scaleByHeight = true;
    }
    $scaledImg = null;
    if ($scaleByWidth) {
      $newHeight = \round($h * $maxWidth / $w);
      $scaledImg = \imagescale($this->img, $maxWidth, $newHeight);
    } elseif ($scaleByHeight) {
      $newWidth = \round($w * $maxHeight / $h);
      $scaledImg = \imagescale($this->img, $newWidth, $maxHeight);
    }
    if ($scaledImg) {
      \imagedestroy($this->img);
      $this->img = $scaledImg;
      return true;
    } else {
      return false;
    }
  }
  
  public function uploadAttachment($slug, $parent = 0)
  {
    \ob_start();
    \imagejpeg($this->img);
    $imageContents = \ob_get_clean();
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
    $attachmentId = \wp_insert_attachment( $attachment, $imageFile, $parent );
    return $attachmentId;
  }
  
  public function display()
  {
    \header('Content-Type: image/jpeg');
    \imagejpeg($this->img);
  }
  
  public function __destruct()
  {
    \imagedestroy($this->img);
  }

}
