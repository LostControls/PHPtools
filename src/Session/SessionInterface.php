<?php


namespace Lostcontrols\PHPtools\Session;


interface SessionInterface
{
    public function has($key);

    public function get($key);

    public function set($key, $value);

    public function delete(...$key);
}