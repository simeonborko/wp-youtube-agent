<?php

include_once __DIR__.'/../../wp-load.php';
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/configuration.php";

function getMysqli() {
  return new mysqli(DB_HOST, DB_USER, DB_PASSWD, DB_DBNAME, DB_PORT);
}

function getYoutubeService() {
  $client = new \Google_Client();
  $client->setApplicationName("WP Youtube Agent");
  $client->setDeveloperKey(DEVELOPER_KEY);
  return new \Google_Service_YouTube($client);
}

function getChannelId() {
  return CHANNEL_ID;
}
