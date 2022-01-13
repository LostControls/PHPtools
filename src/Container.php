<?php


namespace Lostcontrols\PHPtools;

use Lostcontrols\PHPtools\Exceptions\Exception;

class Container
{
    /**
     * 存放实例
     * @var null
     */
    protected static $_instance = null;

    /**
     * 将实例添加到单例
     * @param $instance
     * @throws InvalidArgumentException
     * @author Cyw
     * @dateTime 2022/1/11 17:34
     */
    public static function addInstanceToSingleton($instance)
    {
        if (!is_object($instance)) {
            throw new \InvalidArgumentException('instance not is object');
        }
        $className = get_class($instance);
        if (!array_key_exists($className, self::$_instance)) {
            self::$_instance[$className] = $instance;
        }
    }

    /**
     * 获取一个单例
     * @param $className
     * @return mixed|null
     * @author Cyw
     * @dateTime 2022/1/11 17:36
     */
    public static function getSingleton($className)
    {
        return array_key_exists($className, self::$_instance) ? self::$_instance[$className] : null;
    }

    /**
     * 销毁一个单例
     * @param $className
     * @author Cyw
     * @dateTime 2022/1/11 17:39
     */
    public static function unsetSingleton($className)
    {
        self::$_instance[$className] = null;
    }

    /**
     * 获取实例
     * @param $className
     * @param array $params
     * @return object
     * @throws Exception
     * @author Cyw
     * @dateTime 2022/1/11 18:01
     */
    public static function getInstance($className, $params = []): object
    {
        try {
            // 获取反射实例
            $reflector = new \ReflectionClass($className);
            // 获取反射实例的构造方法
            $constructor = $reflector->getConstructor();

            $diParams = [];
            if ($constructor) {
                // 获取反射实例构造方法的形参
                foreach ($constructor->getParameters() as $param) {
                    $class = $param->getClass();
                    if ($class) {
                        // 如果依赖是单例，则直接获取，反之创建实例
                        $singleton = self::getSingleton($class->name);
                        $diParams[] = $singleton ?: self::getInstance($class->name);
                    }
                }
            }

            $diParams = array_merge($diParams, $params);
            // 创建实例
            return $reflector->newInstanceArgs($diParams);
        } catch (\ReflectionException $e) {
            throw new Exception('class not exists: ' . $className);
        }
    }

    /**
     * 运行方法
     * @param $class
     * @param $method
     * @param array $params
     * @param array $constructParams
     * @return mixed
     * @throws Exception
     * @throws \ReflectionException
     * @author Cyw
     * @dateTime 2022/1/12 10:32
     */
    public static function run($class, $method, $params = [], $constructParams = [])
    {
        if (!class_exists($class)) {
            throw new Exception("Class $class is not found!");
        }
        if (!method_exists($class, $method)) {
            throw new Exception("undefined method $method in $class !");
        }
        // 获取外层实例 new $class
        $instance = self::getInstance($class, $constructParams);

        //以下是为了获取 $method 方法的参数
        try {
            $diParams = [];
            // 通过反射实例，获取 $class 类的相关方法和属性等
            // $reflector = new \ReflectionClass($class);
            // 获取方法
            // $reflectorMethod = $reflector->getMethod($method)->getParameters();
            // 查找方法的形参
//            foreach ($reflectorMethod as $param) {
//                $_class = $param->getClass();
//                if ($_class) {
//                    $singleton = self::getSingleton($_class->name);
//                    $diParams[] = $singleton ?: self::getInstance($_class->name);
//                }
//            }
            $param = is_string($params)
                ? explode(',',$params)
                : array_merge($diParams, $params);

            return call_user_func_array([$instance, $method], $param);
        } catch (\ReflectionException $e) {
            throw new \ReflectionException($e->getMessage());
        }
    }
}