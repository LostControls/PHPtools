<?php


namespace Lostcontrols\PHPtools\Filter;


use Lostcontrols\PHPtools\Exceptions\Exception;

class Filter implements FilterInterface
{
    /**
     * 敏感词字节
     * @var
     */
    private $dict;

    /**
     * 敏感词库文件类型
     * @var string
     */
    private $fileType = 'txt';

    /**
     * 敏感词库文件路径 （如果是 txt 文件 fileType 成员变量属性值必须跟 filePath 是同一类型）
     * @var string
     */
    private $filePath = './Filter/demo.txt';

    /**
     * txt 文件类型的分隔符
     * @var string
     */
    private $delimiter = '|';


    /**
     * 初始化
     * Filter constructor.
     */
    public function __construct()
    {
        $this->loadDictFile();
    }

    /**
     * 加载敏感词典文件
     * @author Cyw
     * @dateTime 2022/1/19 15:25
     */
    private function loadDictFile()
    {
        $array = $this->sensitiveWordConvertArray($this->fileType);

        foreach ($array as $value) $this->addNode(trim($value));
    }

    /**
     * 将敏感词添加到节点
     * @param $words
     * @author Cyw
     * @dateTime 2022/1/19 15:23
     */
    private function addNode($words)
    {
        $wordArr = $this->splitStr($words);
        $curNode = &$this->dict;
        foreach ($wordArr as $char) {
            if (!isset($curNode)) {
                $curNode[$char] = [];
            }
            $curNode = &$curNode[$char];
        }
        $curNode['end']++;
    }

    /**
     * 正则分隔内容
     * @param $str
     * @return array|false|string[]
     * @author Cyw
     * @dateTime 2022/1/19 15:27
     */
    private function splitStr($str)
    {
        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    /**
     * 敏感词校验
     * @param $str ;需要校验的字符串
     * @param int $level ;屏蔽词校验等级 1-只要顺序包含都屏蔽；2-中间间隔skipDistance个字符就屏蔽；3-全词匹配即屏蔽
     * @param int $skipDistance ;允许敏感词跳过的最大距离，如笨aa蛋a傻瓜等等
     * @param bool $isReplace ;是否需要替换，不需要的话，返回是否有敏感词，否则返回被替换的字符串
     * @param string $replace ;替换字符
     * @return bool|string
     */
    public function filter($str, $level = 1, $skipDistance = 2, $isReplace = true, $replace = '*')
    {
        //允许跳过的最大距离
        if ($level == 1) {
            $maxDistance = strlen($str) + 1;
        } elseif ($level == 2) {
            $maxDistance = max($skipDistance, 0) + 1;
        } else {
            $maxDistance = 2;
        }
        $strArr = $this->splitStr($str);
        $strLength = count($strArr);
        $isSensitive = false;
        for ($i = 0; $i < $strLength; $i++) {
            //判断当前敏感字是否有存在对应节点
            $curChar = $strArr[$i];
            if (!isset($this->dict[$curChar])) {
                continue;
            }
            $isSensitive = true; //引用匹配到的敏感词节点
            $curNode = &$this->dict[$curChar];
            $dist = 0;
            $matchIndex = [$i]; //匹配后续字符串是否match剩余敏感词
            for ($j = $i + 1; $j < $strLength && $dist < $maxDistance; $j++) {
                if (!isset($curNode[$strArr[$j]])) {
                    $dist++; continue;
                }
                //如果匹配到的话，则把对应的字符所在位置存储起来，便于后续敏感词替换
                $matchIndex[] = $j;
                //继续引用
                $curNode = &$curNode[$strArr[$j]];
            }
            //判断是否已经到敏感词字典结尾，是的话，进行敏感词替换
            if (isset($curNode['end']) && $isReplace) {
                foreach ($matchIndex as $index) {
                    $strArr[$index] = $replace;
                }
                $i = max($matchIndex);
            }
        }
        if ($isReplace) {
            return implode('', $strArr);
        } else {
            return $isSensitive;
        }
    }

    /**
     * 将各种敏感词文件类型转换为数组
     * @param string $initType
     * @return array|string[]
     * @throws Exception
     * @author Cyw
     * @dateTime 2022/1/19 15:20
     */
    private function sensitiveWordConvertArray($initType = 'array'): array
    {
        switch ($initType) {
            case 'array':
                // 请在 array 内添加你想需要过滤的词
                $array = ['傻逼','你妈','狗东西'];
                break;
            case 'str':
                // 请在 str 内添加你想需要过滤的词
                $str = '傻逼,你妈,狗东西';
                $array = explode(',', $str);
                break;
            case 'txt':
                // 加载 txt 文本中内容
                $array = $this->getTxtContent($this->filePath);
                break;
            case 'excel':
                // 加载 excel 文件内容
                $array = $this->getExcelContent($this->filePath);
                break;
            default:
                $array = ['暂无此类型'];
        }

        return $array;
    }

    /**
     * 获取 txt 文件内容
     * @param $file
     * @return array
     * @author Cyw
     * @dateTime 2022/1/19 15:07
     */
    private function getTxtContent($file): array
    {
        // realpath('./')
        if (file_exists($file)) {
            $fileContent = file_get_contents($file);
            // 将文本中换行(回车)的内容替换为 ,
            $result = str_replace("\r\n",",",$fileContent);

            return explode($this->delimiter, $result);
        }

        return [];
    }

    // 后续完成 PHPExcel 工具类处理
    private function getExcelContent($file): array
    {
        return [];
    }
}