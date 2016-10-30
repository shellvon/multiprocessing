<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午9:38
 *
 * @version 1.0
 */

namespace MultiProcessing\Queue;

use MultiProcessing\Contracts\Queue\Queue;

class SharedMemQueue implements Queue
{
    const SHARD_MEM_MAX_SIZE = 262144; # 256Kb

    protected $sharedMem;

    protected $semaphore;

    public function __construct()
    {
        $this->sharedMem = shm_attach(mt_rand(), self::SHARD_MEM_MAX_SIZE);
        if ($this->sharedMem === false) {
            throw new \Exception('Unable to get shared memory segment');
        }
        $this->semaphore = sem_get(mt_rand());
        if ($this->semaphore === false) {
            throw new \Exception('Unable to get semaphore');
        }
    }

    /**
     * 从共享内存中拿数据.
     *
     * @return array|mixed
     */
    protected function getQueue()
    {
        if (shm_has_var($this->sharedMem, 0)) {
            return shm_get_var($this->sharedMem, 0);
        } else {
            return array();
        }
    }

    /**
     * 讲数据放在共享内存中.
     *
     * @param array $queue
     */
    protected function setQueue(array $queue)
    {
        shm_put_var($this->sharedMem, 0, $queue);
    }

    public function __toString()
    {
        return 'MultiProcessing <SharedMemQueue>';
    }

    /* ---------------------------
     *
     *    Queue API.
     *
     * ---------------------------
     */

    /**
     * 放置一个数据到队列之中.
     *
     * @param $value
     */
    public function put($value)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        $queue[] = $value;
        $this->setQueue($queue);
        sem_release($this->semaphore);
    }

    /**
     * 返回消息队列中的最后一个值(Job).如果没有就返回null.
     *
     * @return mixed
     */
    public function pop()
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        $result = array_pop($queue);
        $this->setQueue($queue);
        sem_release($this->semaphore);

        return $result;
    }

    public function clear()
    {
        $this->setQueue(array());
    }

    public function size()
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        $size = count($queue);
        sem_release($this->semaphore);

        return $size;
    }

    public function getAll($bEmpty = false)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        if ($bEmpty) {
            $this->setQueue(array());
        }
        sem_release($this->semaphore);

        return $queue;
    }

    public function destroy()
    {
        @shm_remove($this->sharedMem);
        @shm_detach($this->sharedMem);
        @sem_remove($this->semaphore);
    }

    /* ---------------------------
     *
     *    ArrayAccess API.
     *
     * ---------------------------
     */

    public function offsetExists($offset)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        sem_release($this->semaphore);

        return array_key_exists($offset, $queue);
    }

    public function offsetGet($offset)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        sem_release($this->semaphore);

        return isset($queue[$offset]) ? $queue[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        $queue[$offset] = $value;
        $this->setQueue($queue);
        sem_release($this->semaphore);
    }

    public function offsetUnset($offset)
    {
        sem_acquire($this->semaphore);
        $queue = $this->getQueue();
        unset($queue[$offset]);
        $this->setQueue($queue);
        sem_release($this->semaphore);
    }
}
