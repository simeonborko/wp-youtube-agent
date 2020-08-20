<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/StatusRepository.php";

use SimeonBorko\WpYoutubeAgent\Repository\StatusRepository;

$status = (new StatusRepository)->get();

print_r($status);
