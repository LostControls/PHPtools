<?php

namespace Lostcontrols\PHPtools\FileHandle;


use Lostcontrols\PHPtools\Exceptions\Exception;

/**
 * 文件压缩
 */
class ZipFileHandler implements FileHandlerStrategy
{
    /**
     * zip 文件压缩
     * @param $filePath
     * @return string
     * @throws \Exception
     * @author Cyw
     * @dateTime 2024/7/22 18:20
     */
    public function handle($filePath)
    {
        // TODO: Implement handle() method.
        $zipClass = new \ZipArchive();
        $zipFileName = $filePath . 'zip';
        if ($zipClass->open($zipFileName, \ZipArchive::CREATE) === true) {
            $zipClass->addFile($filePath, basename($filePath));
            $zipClass->close();
            return $zipFileName;
        } else {
            throw new \Exception('创建 Zip 文件失败');
        }
    }
}