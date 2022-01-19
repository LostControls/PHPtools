<?php


namespace Lostcontrols\PHPtools\Filter;


interface FilterInterface
{
    public function __construct();

    public function filter($str, $level, $skipDistance, $isReplace, $replace);
}