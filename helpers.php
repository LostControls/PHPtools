<?php

use Lostcontrols\PHPtools\Container;



if (!function_exists('callToolsMethod')) {
    /**
     * @throws \Lostcontrols\PHPtools\Exceptions\Exception
     * @throws ReflectionException
     */
    function callToolsMethod($class, $method, $param = []) {
        try {
            return Container::run($class, $method, $param);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage());
        }
    }
}

if (!function_exists('dd')) {
    function dd()
    {
        $args = func_get_args();
        foreach ($args as $val) {
            echo '<pre style="color: red">';
            var_dump($val);
            echo '</pre>';
        }
        exit;
    }
}