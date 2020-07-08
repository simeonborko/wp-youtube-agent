<?php

namespace SimeonBorko\WpYoutubeAgent\Persistence;

use SimeonBorko\WpYoutubeAgent\Entity;

// param mysqli: mysqli object
// return: list of WpPlaylist
function getWpPlaylists($mysqli) {
  $query = <<<'SQL'
    SELECT
      TAX.term_id AS id,
      `name` AS title,
      `description`,
      OPT.option_value AS image_url
    FROM wp_term_taxonomy TAX
    INNER JOIN wp_terms TER
      ON TAX.taxonomy = "sermons-category" AND TAX.term_id = TER.term_id
    LEFT JOIN wp_options OPT
      ON OPT.option_name = CONCAT("sermons-category", TER.term_id, "_image_term_id")
SQL;
  
  $result = $mysqli->query($query);
  if (!$result) {
    throw new \Exception("WP playlists could not be queried");
  }
  
  $playlists = [];
  while ($row = $result->fetch_assoc()) {
      $p = new Entity\WpPlaylist();
      $p->id = $row["id"];
      $p->title = $row["title"];
      $p->description = $row["description"];
      $p->imageUrl = $row["image_url"];
      $playlists[] = $p;
  }
  $result->close();
  
  return $playlists;
  
}
