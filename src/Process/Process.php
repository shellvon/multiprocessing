<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午5:33
 *
 * @version 1.0
 */

namespace MultiProcessing\Process;

use MultiProcessing\Contracts\Queue\Queue;
use MultiProcessing\Contracts\Runnable\Runnable;
use MultiProcessing\Queue\NullQueue;
use MultiProcessing\Queue\SharedMemQueue;
use MultiProcessing\Strategy\RetryStrategy;
use MultiProcessing\Worker;

class Process implements Runnable
{
    /**
     * @var int 进程数量(最大).
     */
    protected $nProcess;

    /**
     * @var Queue 存放结果的消息队列.
     */
    protected $msgQueue;

    /**
     * @var SharedMemQueue 存放任务的任务队列
     */
    protected $jobQueue;

    /**
     * @var int Master 进程.
     */
    protected $parentPid;

    /**
     * @var null 日志.
     */
    protected $logger = null;

    /**
     * @var int 正在跑的进程数量.
     */
    protected $runningProcessNumber = 0;

    /**
     * @var array 开启的进程id。
     */
    protected static $workPids = array();

    /**
     * @var array 退出的进程id.
     */
    protected static $exitedPids = array();

    /**
     * @var Worker 进程实例.
     */
    protected $worker;

    /**
     * @var int cpu间隔时间(毫秒)
     */
    protected $cpuInternalTime = 10;

    public function __construct($nProcess = 4, Queue $msgQueue = null)
    {
        $this->nProcess = $nProcess;
        $this->parentPid = posix_getpid();

        $this->msgQueue = is_null($msgQueue) ? new NullQueue() : $msgQueue;
        $this->jobQueue = new SharedMemQueue();

        $this->registerSignal();
        register_shutdown_function(array($this, 'shutdown'));
    }

    public function run()
    {
        while (true) {
            pcntl_signal_dispatch();
            if (posix_getpid() != $this->parentPid) {
                $this->log('parent exited..', 'ERROR');
                break;
            }
            $size = $this->jobQueue->size();
            if ($size <= 0 && $this->runningProcessNumber <= 0) {
                break;
            }
            $processNumber = min($this->nProcess, $size);
            if ($processNumber > 0) {
                foreach (range(1, $processNumber) as $idx) {
                    $this->forkOneProcess();
                }
            }
            usleep($this->cpuInternalTime);
        }
    }

    /**
     * 记录日志.
     *
     * @param string $msg 消息.
     * @param null $level 消息级别.
     */
    protected function log($msg, $level = null)
    {
        if ($this->logger  === null) {
            return;
        }
        // color   30:黑,31:红,32:绿,33:黄,34:蓝色,35:紫色,36:深绿,37:白色
        // gcolor 40:黑, 41:深红,42:绿,43:黄色,44:蓝色,45:紫色,46:深绿,47:白色
        // style   0:无样式,1:高亮/加粗,4:下换线,7:反显
        $level = $level === null ? 'INFO' : strtoupper($level);
        if ($this->logger === null) {
            $style = 1;
            $color = $level == 'INFO' ? 32 : 31 ;
            $bgColor = 49;
            echo "\033[{$style};{$color};{$bgColor}m{$level}:\033[0m";
            echo $msg.PHP_EOL;
        } else {
            $this->logger->log($msg, $level);
        }
    }

    /**
     * 注册信号.
     */
    protected function registerSignal()
    {
        //设置终止信号(SIGINT)和子进程退出信号(SIGCHLD)处理函数
        foreach (array(SIGINT, SIGCHLD) as $signal) {
            pcntl_signal($signal, array($this, 'onSignal'));
        }
    }

    /**
     * 信号处理器.
     *
     * @param int $signal 信号.
     */
    public function onSignal($signal)
    {
        switch ($signal) {
            case SIGINT:
                $this->shutdown();
                break;
            case SIGCHLD:
                $this->log('Child exit');
                while (($pid = pcntl_wait($status, WNOHANG)) > 0) {
                    --$this->runningProcessNumber;
                    if (isset(self::$workPids[$pid])) {
                        self::$exitedPids[] = self::$workPids[$pid];
                        unset(self::$workPids[$pid]);
                    }
                }
                break;
            default:
                $this->log("Ignored signal : #{$signal}.");
                break;
        }
    }

    /**
     * 创建一个子进程.
     */
    protected function forkOneProcess()
    {
        $currentPid = @pcntl_fork();
        if ($currentPid == -1) {
            $this->log('fork error', 'ERROR');
        } elseif ($currentPid > 0) {
            self::$workPids[$currentPid] = array();
            ++$this->runningProcessNumber;
        } else {
            $this->log('fork child pid:'.posix_getpid());
            $job = $this->jobQueue->pop();
            if ($job != null) {
                $this->invokeWorker($job);
            }
            exit(0);
        }
    }

    /**
     * 唤起一个worker任务.
     *
     * @param array $job 消息对列中的任务(worker && arguments).
     */
    protected function invokeWorker($job)
    {
        if ($job == null) {
            return;
        }
        list($worker, $arguments) = $job;

        $retryStrategy = $worker->getRetryStrategy();
        $retryCnt = 0; # 默认不重试.
        $type = null;
        if ($retryStrategy != null) {
            $type = $retryStrategy->getRetryType();
            $retryCnt = $retryStrategy->getMaxRetryCnt() - $retryStrategy->getCurrentRetryCnt();
        }
        while ($retryCnt >= 0) {
            try {
                $worker->start($arguments);
                $retryCnt = -1; # 清空，不用再重试.
                $this->msgQueue->put($worker->getResult());
            } catch (\Exception $e) {
                if ($retryStrategy != null && $retryCnt > 0) { # 如果不为空且还有多余的重试机会,需要重试.
                    $worker->setJobStatus(Worker::STATUS_RETRY);
                    $worker->getRetryStrategy()->increaseRetryCnt(); # 增加一次重试.
                }
                if ($type === RetryStrategy::TYPE_LATER) {
                    $this->jobQueue->put(array($worker, $arguments));
                    break;
                }
                --$retryCnt;
                if ($retryCnt <= 0) {
                    $this->msgQueue->put($worker->getResult());
                }
            }
        }
    }

    /**
     * 进程退出.
     */
    public function shutdown()
    {
        if (posix_getpid() === $this->parentPid) {
            $this->jobQueue->destroy();
            $this->msgQueue->destroy();
        }
    }

    /**
     * @param Worker $worker
     * @param array  $opt
     *
     * @return array Response array.
     */
    public function map(Worker $worker, $opt = array())
    {
        foreach ((array) $opt as $idx => $arguments) {
            $worker->setJobId("MultiProcessing_{$idx}");
            $this->jobQueue->put(array($worker, $arguments));
        }
        $this->run();

        return $this->msgQueue->getAll();
    }

}
