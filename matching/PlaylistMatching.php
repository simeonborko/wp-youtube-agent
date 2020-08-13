<?php

namespace SimeonBorko\WpYoutubeAgent\Matching;

class PlaylistMatching
{
  
  const MIN_TITLE_SIMILARITY = 85;
  const YEAR_REGEX_PATTERN = '/[0-9]{4}/';
  
  // params:
  // - wpPlaylists: list of WpPlaylist objects
  // - ytPlaylists: list of YtPlaylist objects
  // returns array with the following keys:
  // - matches: list of arrays with keys wp, yt
  // - wpOnly: list of WpPlaylist objects having no match with any YouTube playlist
  // - ytOnly: list of YtPlaylist objects having no match with any WordPress playlist 
  public function match($wpPlaylists, $ytPlaylists)
  {
    $matches = array();
    foreach ($wpPlaylists as $wpKey => $wpValue) {
      $ytKey = $this->chooseYtPlaylist($wpValue, $ytPlaylists);
      if ($ytKey != null) {
        $matches[] = array('wp' => $wpValue, 'yt' => $ytPlaylists[$ytKey]);
        unset($wpPlaylists[$wpKey]);
        unset($ytPlaylists[$ytKey]);
      }
    }
    return array(
      'matches' => $matches,
      'wpOnly' => $wpPlaylists,
      'ytOnly' => $ytPlaylists
    );
  }
  
  // choose YouTube playlist from array for the given WordPress playlist
  // params:
  // - wp: WpPlaylist object
  // - ytPlaylists: list of available YtPlaylist objects
  // returns null if cannot choose any YouTube playlist or key from ytPlaylists
  private function chooseYtPlaylist($wp, $ytPlaylists)
  {
    $youtubeIdMatches = array();
    $similarTitles = array();  // ytKey => similarity
    $similarTitlesModified = array();  // ytKey => similarity
    foreach ($ytPlaylists as $ytKey => $ytValue) {
      $yearDiffers = false;
      if ($this->youtubeIdMatches($wp, $ytValue)) {
        $youtubeIdMatches[] = $ytKey;
      }
      $similarity = $this->titleSimilarity($wp->title, $ytValue->title, $yearDiffers);
      if ($similarity >= self::MIN_TITLE_SIMILARITY && !$yearDiffers) {
        $similarTitles[$ytKey] = $similarity;
      }
      if ($this->canModifyTitle($wp->title) || $this->canModifyTitle($ytValue->title)) {
        $first = $this->modifyTitle($wp->title);
        $second = $this->modifyTitle($ytValue->title);
        $similarity = $this->titleSimilarity($first, $second, $yearDiffers);
        if ($similarity >= self::MIN_TITLE_SIMILARITY && !$yearDiffers) {
          $similarTitlesModified[$ytKey] = $similarity;
        }
      }
    }
    // sort by title similarity in descending order
    \arsort($similarTitles);
    \arsort($similarTitlesModified);
    // options: possible YouTube playlist keys, the first is the best
    $options = \array_merge(
      $youtubeIdMatches,
      \array_keys($similarTitles),
      \array_keys($similarTitlesModified)
    );
    return (\count($options) > 0) ? $options[0] : null;
  }
  
  private function canModifyTitle($title)
  {
    return \strpos($title, '|') !== false;
  }
  
  private function modifyTitle($title)
  {
    $pos = \strpos($title, '|');
    if ($pos !== false) {
      $title = \rtrim(\substr($title, 0, $pos));
    }
    return $title;
  }
  
  private function youtubeIdMatches($wp, $yt)
  {
    return ($wp->youtubeId && $wp->youtubeId == $yt->id);
  }
  
  private function titleSimilarity($first, $second, &$yearDiffers)
  {
    // check whether titles contain year and it differs
    $firstHasYear = preg_match(self::YEAR_REGEX_PATTERN, $first, $firstMatches);
    $secondHasYear = preg_match(self::YEAR_REGEX_PATTERN, $second, $secondMatches);
    if ($firstHasYear && $secondHasYear && $firstMatches[0] != $secondMatches[0]) {
      $yearDiffers = true;
    }
    \similar_text($first, $second, $percent);
    return $percent;
  }
}
