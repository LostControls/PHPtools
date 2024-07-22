<?php

namespace Lostcontrols\PHPtools\FileHandle;

interface FileHandlerStrategy
{
    public function handle($filePath);
}