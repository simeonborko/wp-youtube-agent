<?php

require_once __DIR__.'/../../wp-load.php';
require_once __DIR__."/../entity/WpSermon.php";
require_once __DIR__."/../repository/WpSermonRepository.php";

use SimeonBorko\WpYoutubeAgent\Entity\WpSermon;
use SimeonBorko\WpYoutubeAgent\Repository\WpSermonRepository;

$sermon = new WpSermon();
$sermon->title = "DuckDuckGo";
$sermon->description = "Dear brothers, DuckDuckGo is the best search engine ever.";
// $sermon->ytImageUrl = 'https://images.techhive.com/images/article/2014/05/duckduckgo-logo-100266737-large.jpg';
$sermon->ytImageUrl = 'https://d.ibtimes.co.uk/en/full/1444517/duckduckgo.jpg';
$sermon->speaker = 'Mr Duck';
$sermon->tags = array('duckduckgo', 'search engine', 'google', 'google alternative', 'seznam.cz');
$sermon->videoId = 'a8Uk6fI4oS4';
$sermon->audioUrl = 'http://www.example.com/';

$repo = new WpSermonRepository();
$repo->save($sermon);

echo "New sermon ID is ".$sermon->id;
