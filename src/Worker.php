<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午10:32
 *
 * @version 1.0
 */

namespace MultiProcessing;

use Jeremeamia\SuperClosure\SerializableClosure;
use MultiProcessing\Contracts\Event\JobEvent;
use MultiProcessing\Contracts\Job\Job;
use MultiProcessing\Contracts\Observer\Observable;
use MultiProcessing\Contracts\Response\Response;
use MultiProcessing\Response\JobResponse;
use MultiProcessing\Strategy\RetryStrategy;

class Worker implements Observable, Job
{
    protected $id = null;

    protected $events = array();

    /**
     * @var RetryStrategy
     */
    protected $retryStrategy = null;

    protected $status = self::STATUS_READY;

    protected $response = null;

    const STATUS_CANCELED = -1;
    const STATUS_READY = 0;
    const STATUS_RUNNING = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_FAILED = 3;
    const STATUS_RETRY = 4;

    public function __construct($workerName = '')
    {
        $workerName = empty($workerName) ? 'UNKNOWN' : $workerName;
        $this->setProcessTitle("PHP-MultiProcessing-Worker:#[{$workerName}({$this->id})]");
    }

    /**
     * @return RetryStrategy
     */
    public function getRetryStrategy()
    {
        return $this->retryStrategy;
    }

    /**
     * @param RetryStrategy $retryStrategy
     *
     * @return self
     */
    public function setRetryStrategy($retryStrategy)
    {
        $this->retryStrategy = $retryStrategy;

        return $this;
    }

    /**
     * 设置进程名称，需要proctitle支持 或者php>=5.5.
     *
     * @param string $title 标题.
     */
    protected function setProcessTitle($title)
    {
        // 更改进程名
        if (extension_loaded('proctitle') && function_exists('setproctitle')) {
            @setproctitle($title);
        }
        // >=php 5.5
        elseif (version_compare(phpversion(), '5.5', 'ge') && function_exists('cli_set_process_title')) {
            @cli_set_process_title($title);
        }
    }

    public function fireEvent($event)
    {
        if (isset($this->events[$event])) {
            list($callback, $opt) = $this->events[$event];
            # unset($this->events[$event]);
            return call_user_func_array($callback, $opt);
        }
    }

    public function addListener($event, \Closure $callback, $opt = array(), $override = false)
    {
        if (!is_array($opt)) {
            $opt = array($opt);
        }
        if (isset($this->events[$event]) && !$override) {
            throw new \LogicException("The event[$event] is already exists");
        }
        $this->events[$event] = array(new SerializableClosure($callback), $opt);

        return $this;
    }

    public function start()
    {
        $arguments = func_get_args();

        if ($this->status !== self::STATUS_READY && $this->status !== self::STATUS_RETRY) {
            return;
        }
        if ($this->status == self::STATUS_RETRY) {
            $this->fireEvent(JobEvent::JOB_RETRY);
        }
        $this->status = self::STATUS_RUNNING;
        $this->fireEvent(JobEvent::JOB_START);

        try {
            $this->response = call_user_func_array(array($this, 'process'), $arguments);
            if (!$this->response instanceof Response) {
                $this->response = new JobResponse($this->response);
            }
            $this->fireEvent(JobEvent::JOB_SUCCESS);
            $this->status = self::STATUS_SUCCESS;
        } catch (\Exception $e) {
            if ($this->retryStrategy == null || ($this->retryStrategy->getCurrentRetryCnt() >= $this->retryStrategy->getMaxRetryCnt())) {
                $this->response = new JobResponse(null, $e->getCode(), $e);
                $this->status = self::STATUS_FAILED;
                $this->fireEvent(JobEvent::JOB_FAILED);
            }
            throw $e;
        }
    }

    /**
     * 子类来实现他吧.
     *
     * @param null $arguments
     *
     * @return JobResponse
     */
    protected function process($arguments = null)
    {
        $this->fireEvent(JobEvent::JOB_RUNNING);

        return new JobResponse();
    }

    public function getJobId()
    {
        return $this->id;
    }

    public function cancel()
    {
        if ($this->status === self::STATUS_READY || $this->status === self::STATUS_RETRY) {
            $this->status = self::STATUS_CANCELED;
            $this->fireEvent(JobEvent::JOB_CANCELED);
        }
    }

    public function getJobStatus()
    {
        return $this->status;
    }

    /**
     * @return JobResponse
     */
    public function getResult()
    {
        return $this->response;
    }

    /**
     * @param int $status
     */
    public function setJobStatus($status)
    {
        $this->status = $status;
    }

    public function setJobId($id)
    {
        $this->id = $id;
    }
}
