<?php

namespace SimeonBorko\WpYoutubeAgent\Matching;

abstract class BaseMatching
{
  // params:
  // - wpObjects: list of WordPress objects
  // - ytObjects: list of YouTube objects
  // returns array with the following keys:
  // - matches: list of arrays with keys wp, yt
  // - wpOnly: list of WordPress objects having no match with any YouTube object
  // - ytOnly: list of YouTube objects having no match with any WordPress object 
  public function match($wpObjects, $ytObjects)
  {
    $matches = array();
    foreach ($wpObjects as $wpKey => $wpValue) {
      $ytKey = $this->choose($wpValue, $ytObjects);
      if ($ytKey !== null) {
        $matches[] = array('wp' => $wpValue, 'yt' => $ytObjects[$ytKey]);
        unset($wpObjects[$wpKey]);
        unset($ytObjects[$ytKey]);
      }
    }
    return array(
      'matches' => $matches,
      'wpOnly' => $wpObjects,
      'ytOnly' => $ytObjects
    );
  }
  
  abstract protected function choose($wpValue, $ytObjects);
}
