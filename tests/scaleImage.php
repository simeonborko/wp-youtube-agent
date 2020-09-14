<?php

require_once __DIR__."/common.php";
require_once __DIR__."/../repository/ImageTool.php";

use SimeonBorko\WpYoutubeAgent\Repository\ImageTool;

$url = "https://upload.wikimedia.org/wikipedia/commons/thumb/d/de/Mustanggelding.jpg/1200px-Mustanggelding.jpg";

$tool = new ImageTool($url);
$tool->scale(500, 300);
$tool->display();
