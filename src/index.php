<?php


namespace Lostcontrols\PHPtools;


use Lostcontrols\PHPtools\Encrypt\Xdecode;


require_once '../vendor/autoload.php';
$a = callToolsMethod(Xdecode::class, 'getRandNum', [2000, 9999]);

dd($a);