<?php


namespace Lostcontrols\PHPtools;



require_once '../vendor/autoload.php';

//use Lostcontrols\PHPtools\Hash\BcryptHash;
use Lostcontrols\PHPtools\Log\Log;

///** @noinspection PhpUnhandledExceptionInspection */
//$b = callToolsMethod(BcryptHash::class, 'generate', '557184');
//$a = callToolsMethod(BcryptHash::class, 'check', '1447292956,$2y$10$gBqKJNngmmhixGNFIZltKejuXEQMXWL9l.ZcZ./gDcX0thvZXMmE6');
$data = [
    'name' => '张三',
    'sex' => 1,
    'age' => 30,
    'address' => '长沙'
];
/** @noinspection PhpUnhandledExceptionInspection */
callToolsMethod(Log::class,'debug',['debug',$data]);