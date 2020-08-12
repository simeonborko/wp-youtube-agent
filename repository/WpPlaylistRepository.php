<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";

use SimeonBorko\WpYoutubeAgent\Entity;

class WpPlaylistRepository
{
  protected $mysqli;
  
  public function __construct($mysqli)
  {
    $this->mysqli = $mysqli;
  }
  
  // param mysqli: mysqli object
  // return: list of WpPlaylist
  public function findAll()
  {
    $playlist_taxonomy = WP_PLAYLIST_TAXONOMY;
    $image_option_suffix = WP_PLAYLIST_OPTION_IMAGE_URL_SUFFIX;
    $youtube_id_suffix = WP_PLAYLIST_OPTION_YOUTUBE_ID_SUFFIX;
    $query = <<<SQL
      SELECT
        TAX.term_id AS id,
        `name` AS title,
        `description`,
        OPT_IMG.option_value AS image_url,
        OPT_YT.option_value AS playlist_id
      FROM wp_term_taxonomy TAX
      INNER JOIN wp_terms TER
        ON TAX.taxonomy = $playlist_taxonomy AND TAX.term_id = TER.term_id
      LEFT JOIN wp_options OPT_IMG
        ON OPT.option_name = CONCAT($playlist_taxonomy, TER.term_id, $image_option_suffix)
      LEFT JOIN wp_options OPT_YT
        ON OPT.option_name = CONCAT($playlist_taxonomy, TER.term_id, $youtube_id_suffix)
SQL;
    
    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("WP playlists could not be queried");
    }
    
    $playlists = array();
    while ($row = $result->fetch_assoc()) {
        $p = new Entity\WpPlaylist();
        $p->id = $row["id"];
        $p->title = $row["title"];
        $p->description = $row["description"];
        $p->imageUrl = $row["image_url"];
        $p->playlistId = $row["playlist_id"];
        $playlists[] = $p;
    }
    $result->close();
    
    return $playlists;
  }
}
