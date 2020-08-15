<?php

ob_start();
include_once __DIR__.'/../../wp-load.php';
ob_end_clean();
require_once __DIR__."/../vendor/autoload.php";
require_once __DIR__."/configuration.php";

function getMysqli() {
  return new mysqli(MY_DB_HOST, MY_DB_USER, MY_DB_PASSWD, MY_DB_DBNAME, MY_DB_PORT);
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
