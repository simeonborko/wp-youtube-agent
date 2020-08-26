<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/../entity/WpPlaylist.php";

use SimeonBorko\WpYoutubeAgent\Entity;

abstract class WpPlaylistDirectRepository
{
  protected $mysqli;
  
  // param mysqli: mysqli object
  public function __construct($mysqli)
  {
    $this->mysqli = $mysqli;
  }
  
  public function findAll()
  {
    return $this->find();
  }
  
  public function getById($playlistId)
  {
    $result = $this->find(array($playlistId));
    if (\count($result) != 1) {
      throw new \Exception("Number of playlists for ID ".$playlistId." is ".\count($result));
    }
    return current($result);
  }
  
  public function getByIdList($playlistIdList)
  {
    if (\count($playlistIdList) > 0) {
      $result = $this->find($playlistIdList);
      if (\count($result) != \count($playlistIdList)) {
        throw new \Exception(sprintf("Number of playlists is %d but the expected number is %d", \count($result), \count($playlistIdList)));
      }
      return $result;
    } else {
      return array();
    }
  }
  
  // return: list of WpPlaylist
  public function find($playlistIdList = null)
  {
    $playlist_taxonomy = WP_PLAYLIST_TAXONOMY;
    $image_option_suffix = WP_PLAYLIST_OPTION_IMAGE_URL_SUFFIX;
    $youtube_id_suffix = WP_PLAYLIST_OPTION_YOUTUBE_ID_SUFFIX;
    $waive_match_suffix = WP_PLAYLIST_OPTION_WAIVE_MATCH_SUFFIX;
    $query = <<<SQL
      SELECT
        TAX.term_id AS id,
        `name` AS title,
        `description`,
        OPT_IMG.option_value AS image_url,
        OPT_YT.option_value AS youtube_id,
        OPT_WAIVE.option_value AS waive_match
      FROM wp_term_taxonomy TAX
      INNER JOIN wp_terms TER
        ON TAX.taxonomy = "$playlist_taxonomy" AND TAX.term_id = TER.term_id
      LEFT JOIN wp_options OPT_IMG
        ON OPT_IMG.option_name = CONCAT("$playlist_taxonomy", TER.term_id, "$image_option_suffix")
      LEFT JOIN wp_options OPT_YT
        ON OPT_YT.option_name = CONCAT("$playlist_taxonomy", TER.term_id, "$youtube_id_suffix")
      LEFT JOIN wp_options OPT_WAIVE
        ON OPT_WAIVE.option_name = CONCAT("$playlist_taxonomy", TER.term_id, "$waive_match_suffix")
SQL;

    if ($playlistIdList !== null) {
      $playlistIdList = \array_map(function($id){ return (int) $id; }, $playlistIdList);
      $playlistIdString = \implode(",", $playlistIdList);
      $query .= " WHERE TAX.term_id IN ($playlistIdString)";
    }

    $result = $this->mysqli->query($query);
    if (!$result) {
      throw new \Exception("WP playlists could not be queried, error: ".$this->mysqli->error);
    }
    
    $playlists = array();
    while ($row = $result->fetch_assoc()) {
        $p = new Entity\WpPlaylist();
        $p->id = $row["id"];
        $p->title = $row["title"];
        $p->description = $row["description"];
        $p->imageUrl = $row["image_url"];
        $p->youtubeId = $row["youtube_id"];
        $p->waiveMatch = $row["waive_match"] !== null ? (bool) $row["waive_match"] : null;
        $playlists[] = $p;
    }
    $result->close();
    
    return $playlists;
  }
}
