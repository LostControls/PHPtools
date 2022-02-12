<?php


namespace Lostcontrols\PHPtools;



require_once '../vendor/autoload.php';

use Lostcontrols\PHPtools\Redis\Redis;

///** @noinspection PhpUnhandledExceptionInspection */
//$b = callToolsMethod(BcryptHash::class, 'generate', '557184');
//$a = callToolsMethod(BcryptHash::class, 'check', '1447292956,$2y$10$gBqKJNngmmhixGNFIZltKejuXEQMXWL9l.ZcZ./gDcX0thvZXMmE6');
//$data = [
//    'name' => '张三',
//    'sex' => 1,
//    'age' => 30,
//    'address' => '长沙'
//];
//$param = ['你是不是傻X玩意'];
/** @noinspection PhpUnhandledExceptionInspection */
$res = callToolsMethod(new Redis([],[]),'test', []);
//$res = new Filter();
dd($res);