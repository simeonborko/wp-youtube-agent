<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../task/Transfer.php";

use SimeonBorko\WpYoutubeAgent\Task\Transfer;

$transfer = new Transfer(getYoutubeService(), getMysqli());
$result = $transfer->doTransfer();

print_r($result);
