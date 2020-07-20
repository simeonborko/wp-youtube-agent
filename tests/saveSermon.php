<?php

require_once '../../wp-load.php';
require_once "../entity/WpSermon.php";
require_once "../repository/WpSermonNativeRepository.php";

use SimeonBorko\WpYoutubeAgent\Entity\WpSermon;
use SimeonBorko\WpYoutubeAgent\Repository\WpSermonNativeRepository;

$sermon = new WpSermon();
$sermon->title = "DuckDuckGo";
$sermon->description = "Dear brothers, DuckDuckGo is the best search engine ever.";
$sermon->imageUrl = 'https://images.techhive.com/images/article/2014/05/duckduckgo-logo-100266737-large.jpg';
$sermon->speaker = 'Mr Duck';
$sermon->tags = array('duckduckgo', 'search engine', 'google', 'google alternative', 'seznam.cz');
$sermon->videoId = 'a8Uk6fI4oS4';
$sermon->audioUrl = 'http://www.example.com/';

$repo = new WpSermonNativeRepository();
$repo->save($sermon);

echo "New sermon ID is ".$sermon->id;
