<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/../entity/WpSermon.php";

use SimeonBorko\WpYoutubeAgent\Entity;

class WpSermonRepository
{
  protected $mysqli;
  
  const TAX_SPEAKER = 'sermons-speakers';
  const TAX_TAG     = 'sermons-tag';
  const META_KEY_VIDEO = 'imic_sermons_url';
  const META_KEY_AUDIO = 'imic_sermons_url_audio';
  
  public function __construct($mysqli)
  {
    $this->mysqli = $mysqli;
  }
  
  // param mysqli: mysqli object
  // param playlistId: id of WpPlaylist
  // return: list of WpSermon
  public function findByPlaylistId($playlistId)
  {
    $playlistId = $this->mysqli->real_escape_string($playlistId);
    $query = <<<SQL
      SELECT REL.`object_id` AS `sermon_id`
      FROM wp_term_relationships REL
      INNER JOIN wp_term_taxonomy TT
        ON REL.term_taxonomy_id = TT.term_taxonomy_id
      WHERE
        TT.term_id = "$playlistId"
SQL;

    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("WP sermons could not be queried");
    }
    
    $sermons = array();
    while ($row = $result->fetch_assoc()) {
      $sermons[] = $this->getOneWpSermon($row["sermon_id"]);
    }
    $result->close();
    
    return $sermons;
  }
  
  protected function getOneWpSermon($sermonId)
  {
    $sermonId = $this->mysqli->real_escape_string($sermonId);
    $query = <<<SQL
      SELECT
        post_title AS title,
        post_content AS `description`
      FROM wp_posts
      WHERE `ID` = "$sermonId"
SQL;
    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("Sermon could not be queried");
    }
    if ($result->num_rows != 1) {
      throw new \Exception("Number of rows for sermon is not 1, probably 0");
    }
    $row = $result->fetch_assoc();
    $result->close();
    
    // id, title, description
    $sermon = new Entity\WpSermon();
    $sermon->id = $sermonId;
    $sermon->title = $row["title"];
    $sermon->description = $row["description"];
    
    // speaker
    $speakers = $this->getRelationship($sermonId, self::TAX_SPEAKER);
    if (count($speakers) > 0) {
      $sermon->speaker = $speakers[0]["name"];
    }
    
    // tags
    $tags = $this->getRelationship($sermonId, self::TAX_TAG);
    $sermon->tags = array_map(function($t){ return $t["name"]; }, $tags);
    
    // videoId
    $sermon->videoId = $this->getVideoId($this->getPostMeta($sermonId, self::META_KEY_VIDEO));
    
    // audioUrl
    $sermon->audioUrl = $this->getPostMeta($sermonId, self::META_KEY_AUDIO);
    
    return $sermon;
  }
  
  // param mysqli: mysqli object
  // param sermonId: id of sermon (post)
  // param taxonomy: 'sermons-speakers' or 'sermons-tag'
  // return: list of rows, each row is an associative array with keys 'name' and 'slug'
  protected function getRelationship($sermonId, $taxonomy)
  {
    $sermonId = $this->mysqli->real_escape_string($sermonId);
    $taxonomy = $this->mysqli->real_escape_string($taxonomy);
    $query = <<<SQL
      SELECT
        TER.name,
        TER.slug
      FROM wp_posts P
      INNER JOIN wp_term_relationships REL
        ON REL.`object_id` = P.`ID`
      INNER JOIN wp_term_taxonomy TT
        ON TT.`term_taxonomy_id` = REL.`term_taxonomy_id` AND TT.`taxonomy` = "$taxonomy"
      INNER JOIN wp_terms TER
        ON TER.`term_id` = TT.`term_id`
      WHERE P.`ID` = "$sermonId";
SQL;
    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("Relation could not be queried");
    }
    $rows = array();
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }
    return $rows;
  }

  // param metaKey: 'imic_sermons_url' for video url or 'imic_sermons_url_audio' for audio url
  // return: video url, audio url or null
  protected function getPostMeta($sermonId, $metaKey) {
    $sermonId = $this->mysqli->real_escape_string($sermonId);
    $metaKey = $this->mysqli->real_escape_string($metaKey);
    $query = <<<SQL
      SELECT meta_value
      FROM wp_postmeta
      WHERE post_id = "$sermonId" AND meta_key = "$metaKey"
SQL;
    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("Post-meta could not be queried");
    }
    $value = ($result->num_rows > 0) ? $result->fetch_assoc()["meta_value"] : null;
    $result->close();
    return $value;
  }

  protected function getVideoId($videoUrl) {
    $videoId = null;
    // https://stackoverflow.com/questions/2936467/parse-youtube-video-id-using-preg-match/6382259#6382259
    if ($videoUrl != null && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $videoUrl, $match)) {
      $videoId = $match[1];
    }
    return $videoId;
  }
}
