<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../task/StatusUpdater.php";

use SimeonBorko\WpYoutubeAgent\Task\StatusUpdater;

$updater = new StatusUpdater(getYoutubeService(), getMysqli());
$status = $updater->updateStatus();

print_r($status);
