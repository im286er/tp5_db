<?php
/**
 * Created by PhpStorm.
 * CacheRedis.php
 * Author: gl
 * Date: 2018/1/22
 * Description:定义redis缓存拓展类，主要是弥补redis缓存基类的缓存方式太少的不足(注：原类中只有set get)
 */

namespace app\api\controller;

use think\cache\driver\Redis;

class CacheRedis extends Redis
{
    //初始化options配置参数
    public function __construct($options)
    {
        parent::__construct($options);
    }


    /**
     *填充hash表的值
     * @param string $name hash表的名字
     * @param array $arr hash表名对应的键值对 如 array('key1'=>'value1','key2'=>'value2') 相当于 hset($name,'key1','value1')和hset($name,'key2','value2')
     */
    public function hMset($name, $arr)
    {
        return $this->handler->hmset($name, $arr);
    }

    /**
     *    delete hash opeation
     */

    public function del($name)
    {
        return $this->handler->del($name);
    }

    public function delete($key) {
        return $this->handler->delete($this->formatKey($key));
    }

    protected function unformatValue($value) {
        return @unserialize($value);
    }
    protected function formatValue($value) {
        return @serialize($value);
    }
    //这里可以定义前缀
    protected function formatKey($key) {
        return $key;
    }

    /**
     * 直接返回redis实例
     * @return Redis
     */
    public function getRedis(){
        return $this->handler;
    }

    public function hIncrBy($name, $key, $num = 1){
        return $this->handler->hIncrBy($this->formatKey($name), $key, $num);
    }
    /**
     *    set hash opeation
     */
    public function hSet($name,$key,$value) {
        return $this->handler->hset($this->formatKey($name),$key,$this->formatValue($value));
    }

    /**
     *    get hash opeation
     */
    public function hGet($name,$key = null, $unserializeable = true) {
        if($key){
            $value = $this->handler->hget($this->formatKey($name),$key);
        } else {
            $value = $this->handler->hgetAll($this->formatKey($name));
        }
        if (empty($value)) {
            return '';
        }
        if (empty($unserializeable)) {
            return $value;
        }
        return $this->unformatValue($value);
    }

    /**
     *    delete hash opeation
     */
    public function hDel($name,$key = null){
        if($key){
            return $this->handler->hdel($this->formatKey($name),$key);
        }
        return $this->handler->delete($this->formatKey($name));
    }

    /*******************************************************
    队列操作开始 start 通过list模拟队列queue操作
     ********************************************************/
    /**
     * 入列
     * @param $queueName string 队列名称
     * @param $value object 入列元素的值
     */
    public function qPush($queueName,$value) {
        return $this->handler->rpush($this->formatKey($queueName),$this->formatValue($value));
    }
    /**
     * 出列
     * @param $queueNam
     */
    public function qPop($queueName) {
        $value = $this->handler->lpop($this->formatKey($queueName));
        if (!empty($value))
            $value = $this->unformatValue($value);
        return $value;
    }
    /**
     * 获取队列长度
     */
    public function qLen($queueName) {
        return $this->handler->llen($this->formatKey($queueName));
    }
    /*******************************************************
    队列操作结束 end
     ********************************************************/
}