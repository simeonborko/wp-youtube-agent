<?php

require_once __DIR__."/../vendor/autoload.php";

function getMysqli() {
  return new mysqli("127.0.0.1", "wpuser", "wppassword", "wpdb", 3307);
}

function getYoutubeService() {
  $developerKey = \trim(\file_get_contents('./developerKey.txt'));

  $client = new \Google_Client();
  $client->setApplicationName("WP Youtube Agent");
  $client->setDeveloperKey($developerKey);

  return new \Google_Service_YouTube($client);
}

function getChannelId() {
  return \trim(\file_get_contents('./channelId.txt'));
}
