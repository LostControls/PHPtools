<?php


namespace Lostcontrols\PHPtools\Log;

/**
 * Class Log
 * Author Cyw
 * DateTime 2022/1/17 14:48
 * @package Lostcontrols\PHPtools\Log
 */
class Log implements LogInterface
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    private static $logPath = '../log';

    private static $logFile = 'log.log';

    private static $format = 'Y/m/d';

    private static $tag = 'log';

    private static $config = [];

    /**
     * Log constructor.
     * @param array $config 配置信息
     */
    public function __construct(array $config = [])
    {
        if (isset($config['path'])) self::$logPath = $config['path'];

        if (isset($config['file'])) self::$logFile = $config['file'];

        if (isset($config['format'])) self::$format = $config['format'];

        if (isset($config['tag'])) self::$tag = $config['tag'];
    }

    /**
     * 写入日志
     * @param $msg
     * @param $type
     * @param $data
     * @return false|int
     * @author Cyw
     * @dateTime 2022/1/17 14:49
     */
    public function recordLogInfo($msg, $type, $data)
    {
        $logDir = $this->getLogFile();

        $dateTime = date('Y-m-d H:i:s',time());
        $logContent = sprintf('[%s] %-5s %s %s'.PHP_EOL, $dateTime, $type, self::$tag, var_export($data,true));

        if ($this->generateLogDir(dirname($logDir))) {
            return file_put_contents($logDir, $logContent, FILE_APPEND);
        }

        return false;
    }

    /**
     * 获取日志文件名
     * @return string
     * @author Cyw
     * @dateTime 2022/1/17 11:48
     */
    private function getLogFile(): string
    {
        $dateTime = new \DateTime();

        return sprintf("%s/%s/%s", self::$logPath, $dateTime->format(self::$format), self::$logFile);
    }

    /**
     * 生成日志目录
     * @param $logDir
     * @return bool
     * @author Cyw
     * @dateTime 2022/1/17 14:46
     */
    private function generateLogDir($logDir): bool
    {
        if (!is_dir($logDir)) {
            $auth = 0777;
            return @mkdir($logDir, $auth, true) && @chmod($logDir, $auth);
        }

        return true;
    }

    /**
     * 生成日志
     * @param mixed $level
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function log($level, string $message, array $context = array())
    {
        $this->recordLogInfo($message, $level, $context);
    }

    /**
     * 记录emergency信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function emergency(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录警报信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function alert(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录紧急情况
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function critical(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录错误信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function error(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录warning信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function warning(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录notice信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function notice(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录一般信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function info(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    /**
     * 记录调试信息
     * @param string $message 日志信息
     * @param array $context 替换内容
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/14 15:42
     */
    public function debug(string $message, array $context = array())
    {
        $this->log(__FUNCTION__, $message, $context);
    }
}