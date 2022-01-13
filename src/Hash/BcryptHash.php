<?php


namespace Lostcontrols\PHPtools\Hash;


class BcryptHash implements HashInterface
{
    /**
     * CRYPT_BLOWFISH 算法
     * @var string
     */
    private static $algo = PASSWORD_BCRYPT;

    /**
     * 加密次数（8-10 是个不错的底线，在服务器够快的情况下，越高越好。）
     * @var int[]
     */
    private static $options = ['cost' => 10];

    /**
     * 生成密文
     * @param $plain
     * @return false|string|null
     * @author Cyw
     * @dateTime 2022/1/13 15:12
     */
    public function generate($plain)
    {
        return password_hash($plain, self::$algo, self::$options);
    }

    /**
     * 验证密文
     * @param $plain * 未加密字符串
     * @param $password * 加密后的密文
     * @return bool
     * @author Cyw
     * @dateTime 2022/1/13 15:29
     */
    public function check($plain, $password): bool
    {
        return password_verify($plain, $password);
    }

    /**
     * 刷新密文
     * @param $password
     * @return mixed
     * @author Cyw
     * @dateTime 2022/1/13 15:01
     */
    public function needRehash($password)
    {
        return password_needs_rehash($password, self::$algo, self::$options);
    }
}