<?php


namespace Lostcontrols\PHPtools\Redis;


class Redis implements RedisInterFace
{
    private $redis;

    protected $dbId = 0;

    protected $auth;

    static private $_instance = [];

    private $key;

    protected $attr = [
        'timeout' => 30,
        'db_id' => 0,
    ];

    protected $expireTime;

    protected $host;

    protected $port;

    /**
     * Redis constructor.
     * @param $config
     * @param array $attr
     */
    public function __construct($config, $attr = [])
    {
        if (!extension_loaded('redis')) throw new \BadFunctionCallException('not support: redis');

        $this->attr = array_merge($this->attr, $attr);
        $this->redis = new \Redis();
        $this->host = $config['host'];
        $this->port = $config['port'] ?? 6379;
        $this->redis->connect($this->host, $this->port, $this->attr['timeout']);
        if ($config['auth']) {
            $this->auth($config['auth']);
            $this->auth = $config['auth'];
        }
        $this->expireTime = time() + $this->attr['timeout'];
    }

    /**
     * 获取实例化对象，为每个数据库建立一个连接，连接超时，则重新建立
     * @param $config
     * @param array $attr
     * @return mixed
     * @author Cyw
     * @dateTime 2022/2/11 14:50
     */
    public static function getInstance($config, $attr = [])
    {
        if (!is_array($attr)) {
            $dbId = $attr;
            $attr = [];
            $attr['db_id'] = $dbId;
        }
        $attr['db_id'] = $attr['db_id'] ?? 0;
        $key = md5(implode('', $config) . $attr['db_id']);
        if (!(static::$_instance[$key] instanceof self)) {
            static::$_instance[$key] = new self($config, $attr);
            static::$_instance[$key]->key = $key;
            static::$_instance[$key]->dbId = $attr['db_id'];
            if ($attr['db_id'] != 0) static::$_instance[$key]->select($attr['db_id']);
        } elseif (time() > static::$_instance[$key]->expireTime) {
            static::$_instance[$key]->close();
            static::$_instance[$key] = new self($config,$attr);
            static::$_instance[$key]->key = $key;
            static::$_instance[$key]->dbId = $attr['db_id'];
            //如果不是0号库，选择一下数据库。
            if ($attr['db_id'] != 0) static::$_instance[$key]->select($attr['db_id']);
        }

        return static::$_instance[$key];
    }


    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    /**
     * 执行原生 redis 操作
     * @return \Redis
     */
    public function getRedis(): \Redis
    {
        return $this->redis;
    }

    /**
     * 获取哈希表中一个字段的值
     * @param $key
     * @param $field
     * @return string
     * @author Cyw
     * @dateTime 2022/2/11 14:59
     */
    public function hGet($key, $field): string
    {
        return $this->redis->hGet($key, $field);
    }

    /**
     * 将键值存入哈希表
     * @param $key
     * @param $field
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/11 15:48
     */
    public function hSet($key, $field, $value)
    {
        return $this->redis->hSet($key, $field, $value);
    }

    /**
     * 判断哈希表中，指定的字段是否存在
     * @param $key
     * @param $field
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/11 15:50
     */
    public function hExists($key, $field): bool
    {
        return $this->redis->hExists($key, $field);
    }

    /**
     * 删除哈希表中指定字段，支持批量删除
     * @param $key
     * @param $field
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/11 17:50
     */
    public function hDel($key, $field)
    {
        $fieldArr = explode(',', $field);
        $delNum = 0;
        foreach ($fieldArr as $row) {
            $row = trim($row);
            $delNum += $this->redis->hDel($key, $row);
        }

        return $delNum;
    }

    /**
     * 返回哈希表元素个数
     * @param $key
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/11 17:51
     */
    public function hLen($key)
    {
        return $this->redis->hLen($key);
    }

    /**
     * 为哈希表设定一个字段值，如果字段存在返回 false
     * @param $key
     * @param $field
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/11 17:52
     */
    public function hSetNx($key, $field, $value): bool
    {
        return $this->redis->hSetNx($key, $field, $value);
    }


    /**
     * 哈希表多个字段设定
     * @param string $key
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/11 17:57
     */
    public function hMSet(string $key, $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        return $this->redis->hMSet($key, $value);
    }

    /**
     * 哈希表多个字段设定值
     * @param $ket
     * @param $field
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 10:20
     */
    public function hMGet($ket, $field): array
    {
        if (!is_array($field)) $field = explode(',', $field);

        return $this->redis->hMGet($ket, $field);
    }

    /**
     * 哈希表累加，可以为负数
     * @param $key
     * @param $field
     * @param $value
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 10:27
     */
    public function hIncrBy($key, $field, $value): int
    {
        $value = intval($value);
        return $this->redis->hIncrBy($key, $field, $value);
    }

    /**
     * 返回哈希表所有字段
     * @param $key
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 10:36
     */
    public function hKeys($key): array
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 返回所有哈希表的字段值，为索引数组
     * @param $key
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 10:37
     * @noinspection SpellCheckingInspection
     */
    public function hVals($key): array
    {
        return $this->redis->hVals($key);
    }

    /**
     * @param $key
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 11:09
     */
    public function hGetAll($key): array
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * 集合添加元素，如果 value 已存在，更新 order 值
     * @param $key
     * @param $order
     * @param $value
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 11:16
     */
    public function zAdd($key, $order, $value): int
    {
        return $this->redis->zAdd($key, $order, $value);
    }

    /**
     * 给 value 成员的 order 值，增加 num，可以为负数
     * @param $key
     * @param $num
     * @param $value
     * @return float
     * @author Cyw
     * @dateTime 2022/2/12 11:25
     */
    public function zIncrBy($key, $num, $value): float
    {
        return $this->redis->zIncrBy($key, $num, $value);
    }


    /**
     * 删除值为 value 的元素
     * @param $key
     * @param $value
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 11:34
     */
    public function zRem($key, $value): int
    {
        return $this->redis->zRem($key, $value);
    }

    /**
     * 集合以order递增排列后，0表示第一个元素，-1表示最后一个元素
     * @param $key
     * @param $start
     * @param $end
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 11:35
     */
    public function zRange($key, $start, $end): array
    {
        return $this->redis->zRange($key, $start, $end);
    }

    /**
     * 集合以order递减排列后，0表示第一个元素，-1表示最后一个元素
     * @param $key
     * @param $start
     * @param $end
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 11:38
     */
    public function zRevRange($key, $start, $end): array
    {
        return $this->redis->zRevRange($key, $start, $end);
    }

    /**
     * 集合以order递增排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     * @param  $key
     * @param string $start
     * @param string $end
     * @param array $option
     * @return array
     * @package array $option 参数
     *   with scores=>true，表示数组下标为Order值，默认返回索引数组
     *   limit => array(0,1) 表示从0开始，取一条记录。
     */
    public function zRangeByScore($key, $start = '-inf', $end = "+inf", $option = []): array
    {
        return $this->redis->zRangeByScore($key, $start, $end, $option);
    }

    /**
     * 集合以order递减排列后，返回指定order之间的元素。
     * min和max可以是-inf和+inf　表示最大值，最小值
     * @param $key
     * @param string $start
     * @param string $end
     * @param array $option
     * @return array
     * @package array $option 参数
     *   with scores=>true，表示数组下标为Order值，默认返回索引数组
     *   limit => array(0,1) 表示从0开始，取一条记录。
     */
    public function zRevRangeByScore($key, $start = '-inf', $end = "+inf", $option = []): array
    {
        return $this->redis->zRevRangeByScore($key, $start, $end, $option);
    }

    /**
     * 返回值在 start 和 end 之间的数量
     * @param $key
     * @param $start
     * @param $end
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 11:46
     */
    public function zCount($key, $start, $end): int
    {
        return $this->redis->zCount($key, $start, $end);
    }

    /**
     * 返回值的 order 值
     * @param $key
     * @param $value
     * @return bool|float
     * @author Cyw
     * @dateTime 2022/2/12 11:47
     */
    public function zScore($key, $value)
    {
        return $this->redis->zScore($key, $value);
    }

    /**
     * 返回集合以 score 递增加排序后，指定成员的排序号，从0开始。
     * @param $key
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 11:50
     */
    public function zRank($key, $value)
    {
        return $this->redis->zRank($key, $value);
    }

    /**
     * 返回集合以score递增加排序后，指定成员的排序号，从0开始。
     * @param $key
     * @param $value
     * @return bool|int
     */
    public function zRevRank($key, $value)
    {
        return $this->redis->zRevRank($key,$value);
    }

    /**
     * 删除集合中，score值在start end之间的元素　包括start end
     * @param $key
     * @param $start
     * @param $end
     * @return int 删除成员的数量
     * @author Cyw
     * @dateTime 2022/2/12 11:53
     */
    public function zRemRangeByScore($key, $start, $end): int
    {
        return $this->redis->zRemRangeByScore($key, $start, $end);
    }

    /**
     * 返回集合元素个数
     * @param $key
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 11:54
     */
    public function zCard($key): int
    {
        return $this->redis->zCard($key);
    }

    /**
     * 在队列尾部插入一个元素
     * @param $key
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 11:55
     */
    public function rPush($key, $value)
    {
        return $this->redis->rPush($key, $value);
    }

    /**
     * 在队列尾部插入一个元素，如果 key 不存在，什么也不做
     * @param $key
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 11:56
     */
    public function rPushx($key, $value)
    {
        return $this->redis->rPushx($key, $value);
    }

    /**
     * 在队列头部插入一个元素
     * @param $key
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 11:58
     */
    public function lPush($key, $value)
    {
        return $this->redis->lPush($key, $value);
    }

    /**
     * 在队列头部插入一个元素，如果 key 不存在，什么也不做
     * @param $key
     * @param $value
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 14:24
     */
    public function lPushx($key, $value)
    {
        return $this->redis->lPushx($key, $value);
    }

    /**
     * 返回队列长度
     * @param $key
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 14:26
     */
    public function lLen($key)
    {
        return $this->redis->lLen($key);
    }

    /**
     * 返回队列区间内指定元素
     * @param $key
     * @param $start
     * @param $end
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 14:28
     */
    public function lRange($key, $start, $end): array
    {
        return $this->redis->lRange($key, $start, $end);
    }

    /**
     * 返回队列中指定索引的值
     * @param $key
     * @param $index
     * @return bool|mixed
     * @author Cyw
     * @dateTime 2022/2/12 14:29
     */
    public function lIndex($key, $index)
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 设定队列中指定index的值
     * @param $key
     * @param $index
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:31
     */
    public function lSet($key, $index, $value): bool
    {
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 删除值为 value 的 count 个元素  count>0 从尾部开始 | >0　从头部开始 | =0　删除全部
     * @param $key
     * @param $value
     * @param $count
     * @return bool|int
     * @author Cyw
     * @dateTime 2022/2/12 14:33
     */
    public function lRem($key, $value, $count)
    {
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 删除并返回队列中的头元素
     * @param $key
     * @return bool|mixed
     * @author Cyw
     * @dateTime 2022/2/12 14:35
     */
    public function lPop($key)
    {
        return $this->redis->lPop($key);
    }

    /**
     * 删除并返回队列中的尾元素
     * @param $key
     * @return bool|mixed
     * @author Cyw
     * @dateTime 2022/2/12 14:36
     */
    public function rPop($key)
    {
        return $this->redis->rPop($key);
    }

    /**
     * 设置一个key
     * @param $key
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:37
     */
    public function set($key,$value): bool
    {
        return $this->redis->set($key,$value);
    }

    /**
     * 得到一个key
     * @param $key
     * @return bool|mixed|string
     * @author Cyw
     * @dateTime 2022/2/12 14:38
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 设置一个有过期时间的 key
     * @param $key
     * @param $expire
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:40
     */
    public function setEx($key, $expire, $value): bool
    {
        return $this->redis->setex($key, $expire, $value);
    }

    /**
     * 设置一个 key ,如果 key 存在,不做任何操作
     * @param $key
     * @param $value
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:41
     */
    public function setNx($key,$value): bool
    {
        return $this->redis->setnx($key,$value);
    }

    /**
     * 批量设置 key
     * @param $array
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:42
     */
    public function mSet($array): bool
    {
        return $this->redis->mset($array);
    }

    /**
     * 返回集合中所有元素
     * @param $key
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 14:45
     */
    public function sMembers($key): array
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 求2个集合的差集
     * @param $key1
     * @param $key2
     * @return array
     * @author Cyw
     * @dateTime 2022/2/12 14:46
     */
    public function sDiff($key1, $key2): array
    {
        return $this->redis->sDiff($key1, $key2);
    }

    /**
     * 添加集合，可批量添加
     * @param $key
     * @param $value
     * @author Cyw
     * @dateTime 2022/2/12 14:49
     */
    public function sAdd($key, $value)
    {
        $array = !is_array($value) ? [$value] : $value;
        foreach ($array as $row) {
            $this->redis->sAdd($key, $row);
        }
    }

    /**
     * 返回无序集合元素个数
     * @param $key
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 14:50
     */
    public function sCard($key): int
    {
        return $this->redis->scard($key);
    }

    /**
     * 从集合中删除一个元素
     * @param $key
     * @param $value
     * @return int
     * @author Cyw
     * @dateTime 2022/2/12 14:52
     */
    public function sRem($key, $value): int
    {
        return $this->redis->sRem($key, $value);
    }


    /**
     * 选择数据库
     * @param $dbId
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:53
     */
    public function select($dbId): bool
    {
        $this->dbId = $dbId;

        return $this->redis->select($dbId);
    }

    /**
     * 清空当前数据库
     * @return bool
     * @author Cyw
     * @dateTime 2022/2/12 14:54
     */
    public function flushDB(): bool
    {
        return $this->redis->flushDB();
    }

    /**
     * 返回当前库状态
     * @return string
     */
    public function info(): string
    {
        return $this->redis->info();
    }

    /**
     * 同步保存数据到磁盘
     */
    public function save(): bool
    {
        return $this->redis->save();
    }

    /**
     * 异步保存数据到磁盘
     */
    public function bgSave(): bool
    {
        return $this->redis->bgSave();
    }

    /**
     * 返回最后保存到磁盘的时间
     */
    public function lastSave(): int
    {
        return $this->redis->lastSave();
    }

    /**
     * 返回key,支持*多个字符，?一个字符
     * 只有*　表示全部
     * @param string $key
     * @return array
     */
    public function keys(string $key): array
    {
        return $this->redis->keys($key);
    }

    /**
     * 删除指定key
     * @param $key
     * @return int
     */
    public function del($key): int
    {
        return $this->redis->del($key);
    }

    /**
     * 判断一个key值是不是存在
     * @param  $key
     * @return bool|int
     */
    public function exists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 为一个key设定过期时间 单位为秒
     * @param  $key
     * @param  $expire
     * @return bool
     */
    public function expire($key,$expire): bool
    {
        return $this->redis->expire($key,$expire);
    }

    /**
     * 返回一个key还有多久过期，单位秒
     * @param $key
     * @return bool|int
     */
    public function ttl($key)
    {
        return $this->redis->ttl($key);
    }

    /**
     * 设定一个key什么时候过期，time为一个时间戳
     * @param  $key
     * @param  $time
     * @return bool
     */
    public function expireAt($key,$time): bool
    {
        return $this->redis->expireAt($key,$time);
    }

    /**
     * 关闭服务器链接
     */
    public function close(): bool
    {
        return $this->redis->close();
    }

    /**
     * 关闭所有连接
     */
    public static function closeAll()
    {
        foreach(static::$_instance as $o) {
            if($o instanceof self) $o->close();
        }
    }

    /**
     * 返回当前数据库key数量
     */
    public function dbSize(): int
    {
        return $this->redis->dbSize();
    }

    /**
     * 返回一个随机key
     */
    public function randomKey(): string
    {
        return $this->redis->randomKey();
    }

    /**
     * 得到当前数据库ID
     * @return int
     */
    public function getDbId(): int
    {
        return $this->dbId;
    }

    /**
     * 返回当前密码
     */
    public function getAuth()
    {
        return $this->auth;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getConnInfo(): array
    {
        return [
            'host'=>$this->host,
            'port'=>$this->port,
            'auth'=>$this->auth
        ];
    }


    /**
     * 监控key,就是一个或多个key添加一个乐观锁
     * 在此期间如果key的值如果发生的改变，刚不能为key设定值
     * 可以重新取得Key的值。
     * @param $key
     */
    public function watch($key)
    {
        return $this->redis->watch($key);
    }

    /**
     * 取消当前链接对所有key的watch
     * EXEC 命令或 DISCARD 命令先被执行了的话，那么就不需要再执行 UNWATCH 了
     */
    public function unwatch()
    {
        return $this->redis->unwatch();
    }

    /**
     * 开启一个事务
     * 事务的调用有两种模式Redis::MULTI和Redis::PIPELINE，
     * 默认是Redis::MULTI模式，
     * Redis::PIPELINE管道模式速度更快，但没有任何保证原子性有可能造成数据的丢失
     */
    public function multi($type=\Redis::MULTI): \Redis
    {
        return $this->redis->multi($type);
    }

    /**
     * 执行一个事务
     * 收到 EXEC 命令后进入事务执行，事务中任意命令执行失败，其余的命令依然被执行
     */
    public function exec()
    {
        return $this->redis->exec();
    }

    /**
     * 回滚一个事务
     */
    public function discard()
    {
        return $this->redis->discard();
    }

    /**
     * 测试当前链接是不是已经失效
     * 没有失效返回+PONG
     * 失效返回false
     * @throws \RedisException
     */
    public function ping()
    {
        return $this->redis->ping('');
    }

    public function auth($auth): bool
    {
        return $this->redis->auth($auth);
    }


    /**
     * 得到一组的 ID 号
     * @param $prefix
     * @param $ids
     * @return array|false
     * @author Cyw
     * @dateTime 2022/2/12 15:29
     */
    public function hashAll($prefix, $ids)
    {
        if ($ids === false) return false;
        if (is_string($ids)) $ids = explode(',', $ids);
        $array = [];
        foreach ($ids as $id) {
            $key = $prefix . '.' . $id;
            $res = $this->hGetAll($key);
            if ($res != false) $array[] = $res;
        }

        return $array;
    }

    /**
     * 生成一条消息，放在redis数据库中。使用0号库。
     * @param $lKey
     * @param $msg
     * @return string
     * @author Cyw
     * @dateTime 2022/2/12 15:33
     */
    public function pushMessage($lKey, $msg): string
    {
        if (is_array($msg)) $msg = json_encode($msg);
        $key = md5($msg);
        //重新设置新消息
        $this->lPush($lKey, $key);
        $this->setEx($key, 3600, $msg);

        return $key;
    }

    /**
     * 得到条批量删除key的命令
     * @param $keys
     * @param $dbId
     * @return string
     * @author Cyw
     * @dateTime 2022/2/12 15:35
     */
    public function delKeys($keys, $dbId): string
    {
        $redisInfo = $this->getConnInfo();
        $cmdArr = [
            'redis-cli',
            '-a',
            $redisInfo['auth'],
            '-h',
            $redisInfo['host'],
            '-p',
            $redisInfo['port'],
            '-n',
            $dbId,
        ];
        $redisStr = implode(' ', $cmdArr);

        return "{$redisStr} KEYS \"{$keys}\" | xargs {$redisStr} del";
    }
}