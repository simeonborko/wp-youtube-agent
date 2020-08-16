<?php

namespace SimeonBorko\WpYoutubeAgent\Repository;

require_once __DIR__."/constants.php";
require_once __DIR__."/../entity/Status.php";

use SimeonBorko\WpYoutubeAgent\Entity\Status;

class StatusRepository
{
  public function get() {
    return \get_option(WP_STATUS_OPTION, new Status);
  }
  
  public function save($status) {
    \update_option(WP_STATUS_OPTION, $status);
  }
}
