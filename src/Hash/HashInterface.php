<?php

namespace Lostcontrols\PHPtools\Hash;

interface HashInterface
{
    public function generate($plain);

    public function check($plain, $password);

    public function needRehash($password);
}