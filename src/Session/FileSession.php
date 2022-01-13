<?php


namespace Lostcontrols\PHPtools\Session;


class FileSession implements SessionInterface
{

    public function has($key): bool
    {
        return isset($_SESSION[$key]) && !empty($key);
    }

    public function get($key, $default = null)
    {
        if ($this->has($key)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $_SESSION[$k] = $v;
            }
            return;
        }

        $_SESSION[$key] = $value;
    }

    public function delete(...$key)
    {
        foreach ($key as $k) {
            unset($_SESSION[$k]);
        }
    }
}