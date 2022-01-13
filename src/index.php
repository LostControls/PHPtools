<?php


namespace Lostcontrols\PHPtools;



require_once '../vendor/autoload.php';

use Lostcontrols\PHPtools\Hash\BcryptHash;

/** @noinspection PhpUnhandledExceptionInspection */
$b = callToolsMethod(BcryptHash::class, 'generate', '557184');
/** @noinspection PhpUnhandledExceptionInspection */
$a = callToolsMethod(BcryptHash::class, 'check', '1447292956,$2y$10$gBqKJNngmmhixGNFIZltKejuXEQMXWL9l.ZcZ./gDcX0thvZXMmE6');
var_dump($b);
dd($a);