<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

use SimeonBorko\WpYoutubeAgent\Entity;

// param mysqli: mysqli object
// param playlistId: id of WpPlaylist
// return: list of WpSermon
function getWpSermons($mysqli, $playlistId) {
  $playlistId = $mysqli->real_escape_string($playlistId);
  $query = <<<SQL
    SELECT REL.`object_id` AS `sermon_id`
    FROM wp_term_relationships REL
    INNER JOIN wp_term_taxonomy TT
      ON REL.term_taxonomy_id = TT.term_taxonomy_id
    WHERE
      TT.term_id = "$playlistId"
SQL;

  $result = $mysqli->query($query);
  if (!$result) {
    throw new \Exception("WP sermons could not be queried");
  }
  
  $sermons = array();
  while ($row = $result->fetch_assoc()) {
    $sermons[] = getOneWpSermon($mysqli, $row["sermon_id"]);
  }
  $result->close();
  
  return $sermons;
      
}

function getOneWpSermon($mysqli, $sermonId) {
  $sermonId = $mysqli->real_escape_string($sermonId);
  $query = <<<SQL
    SELECT
      post_title AS title,
      post_content AS `description`
    FROM wp_posts
    WHERE `ID` = "$sermonId"
SQL;
  $result = $mysqli->query($query);
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
  $speakers = _getRelationship($mysqli, $sermonId, 'sermons-speakers');
  if (count($speakers) > 0) {
    $sermon->speaker = $speakers[0]["name"];
  }
  
  // tags
  $tags = _getRelationship($mysqli, $sermonId, 'sermons-tag');
  $sermon->tags = array_map(function($t){ return $t["name"]; }, $tags);
  
  // videoId
  $sermon->videoId = _getVideoId(_getPostMeta($mysqli, $sermonId, 'imic_sermons_url'));
  
  // audioUrl
  $sermon->audioUrl = _getPostMeta($mysqli, $sermonId, 'imic_sermons_url_audio');
  
  return $sermon;
}

// param mysqli: mysqli object
// param sermonId: id of sermon (post)
// param taxonomy: 'sermons-speakers' or 'sermons-tag'
// return: list of rows, each row is an associative array with keys 'name' and 'slug'
function _getRelationship($mysqli, $sermonId, $taxonomy) {
  $sermonId = $mysqli->real_escape_string($sermonId);
  $taxonomy = $mysqli->real_escape_string($taxonomy);
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
  $result = $mysqli->query($query);
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
function _getPostMeta($mysqli, $sermonId, $metaKey) {
  $sermonId = $mysqli->real_escape_string($sermonId);
  $metaKey = $mysqli->real_escape_string($metaKey);
  $query = <<<SQL
    SELECT meta_value
    FROM wp_postmeta
    WHERE post_id = "$sermonId" AND meta_key = "$metaKey"
SQL;
  $result = $mysqli->query($query);
  if (!$result) {
    throw new \Exception("Post-meta could not be queried");
  }
  $value = ($result->num_rows > 0) ? $result->fetch_assoc()["meta_value"] : null;
  $result->close();
  return $value;
}

function _getVideoId($videoUrl) {
  $videoId = null;
  // https://stackoverflow.com/questions/2936467/parse-youtube-video-id-using-preg-match/6382259#6382259
  if ($videoUrl != null && preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $videoUrl, $match)) {
    $videoId = $match[1];
  }
  return $videoId;
}
