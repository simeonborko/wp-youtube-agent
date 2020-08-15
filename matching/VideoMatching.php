<?php

namespace SimeonBorko\WpYoutubeAgent\Matching;

require_once __DIR__."/BaseMatching.php";

class VideoMatching extends BaseMatching
{
  // choose YouTube video from array for the given WordPress sermon
  // params:
  // - wp: WpSermon object
  // - ytVideos: list of available YtVideo objects
  // returns null if cannot choose any YouTube video or key from ytVideos
  protected function choose($wp, $ytVideos)
  {
    $predicate = function($yt) use ($wp) { return $yt->id == $wp->videoId; };
    $youtubeIdMatches = \array_filter($ytVideos, $predicate);
    if (\count($youtubeIdMatches) > 1) {
      throw new \Exception("Multiple YouTube videos have the same ID as Wordpress video with ID ".$wp->id);
    }
    return (\count($youtubeIdMatches) > 0) ? \key($youtubeIdMatches) : null;
  }
}
