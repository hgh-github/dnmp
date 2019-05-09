<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/9
 * Time: 9:33
 */

class redis_lock{

    protected $redisString;
    protected $lockedNames = [];
    private $keyPrefix = 'Lock';

    public function __construct()
    {
        $this->redisString = new Redis();
    }

    /**
     * 加锁
     * @param [type] $name      锁的标识名
     * @param integer $timeout    循环获取锁的等待超时时间，在此时间内会一直尝试获取锁直到超时，为0表示失败后直接返回不等待
     * @param integer $expire     当前锁的最大生存时间(秒)，必须大于0，如果超过生存时间锁仍未被释放，则系统会自动强制释放
     * @param integer $waitIntervalUs 获取锁失败后挂起再试的时间间隔(微秒)
     * @return [type]         [description]
     */
    public function lock($name, $timeout = 0, $expire = 15, $waitIntervalUs = 100000) {
        if ($name == null) return false;

        //取得当前时间
        $now = time();
        //获取锁失败时的等待超时时刻
        $timeoutAt = $now + $timeout;
        //锁的最大生存时刻
        $expireAt = $now + $expire;

        $redisKey = "Lock:{$name}";
        while (true) {
            //将rediskey的最大生存时刻存到redis里，过了这个时刻该锁会被自动释放
            $result = $this->redisString->setnx($redisKey, $expireAt);

            if ($result != false) {
                //设置key的失效时间
                $this->redisString->expire($redisKey, $expireAt);
                //将锁标志放到lockedNames数组里
                $this->lockedNames[$name] = $expireAt;
                return true;
            }

            //以秒为单位，返回给定key的剩余生存时间
            $ttl = $this->redisString->ttl($redisKey);

            //ttl小于0 表示key上没有设置生存时间（key是不会不存在的，因为前面setnx会自动创建）
            //如果出现这种状况，那就是进程的某个实例setnx成功后 crash 导致紧跟着的expire没有被调用
            //这时可以直接设置expire并把锁纳为己用
            if ($ttl < 0) {
                $this->redisString->set($redisKey, $expireAt);
                $this->lockedNames[$name] = $expireAt;
                return true;
            }

            /*****循环请求锁部分*****/
            //如果没设置锁失败的等待时间 或者 已超过最大等待时间了，那就退出
            if ($timeout <= 0 || $timeoutAt < microtime(true)) break;

            //隔 $waitIntervalUs 后继续 请求
            usleep($waitIntervalUs);

        }

        return false;
    }


    /**
     * 解锁
     * @param [type] $name [description]
     * @return [type]    [description]
     */
    public function unlock($name) {
        //先判断是否存在此锁
        if ($this->isLocking($name)) {
            //删除锁
            if ($this->redisString->deleteKey("Lock:$name")) {
                //清掉lockedNames里的锁标志
                unset($this->lockedNames[$name]);
                return true;
            }
        }
        return false;
    }

    /**
     * 释放当前所有获得的锁
     * @return [type] [description]
     */
    public function unlockAll() {
        //此标志是用来标志是否释放所有锁成功
        $allSuccess = true;
        foreach ($this->lockedNames as $name => $expireAt) {
            if (false === $this->unlock($name)) {
                $allSuccess = false;
            }
        }
        return $allSuccess;
    }
}

//$time = microtime(true);
//print_r($time);