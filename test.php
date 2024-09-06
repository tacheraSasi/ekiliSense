<?php
require "vendor/autoload.php";
use ekilie\EkiliRelay;

$ekilirelay = new EkiliRelay("");
$res = $ekilirelay->sendEmail("","","","");
print_r($res);