<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午11:30
 *
 * @version 1.0
 */

namespace MultiProcessing\Strategy;

use MultiProcessing\Contracts\Strategy\Strategy;

class RetryStrategy implements Strategy
{
    // 重试策略类型

    /**
     * @var int 重试策略类型:立刻重试
     */
    const TYPE_IMMEDIATELY = 0;

    /**
     * @var int 重试策略类型:稍后重试
     */
    const TYPE_LATER = 1;

    /**
     * @var int 最大重试次数，默认是3.
     */
    protected $maxRetryCnt = 3;

    protected $retryType = self::TYPE_IMMEDIATELY;

    /**
     * @var int 当前重试次数.
     */
    protected $currentRetryCnt = 0;

    /**
     * RetryStrategy constructor.
     *
     * @param int $retryCnt  最大重试次数.
     * @param int $retryType 重试类型.
     */
    public function __construct($retryCnt = -1, $retryType = self::TYPE_IMMEDIATELY)
    {
        if ($retryCnt >= 0) {
            $this->maxRetryCnt = $retryCnt;
        }
        if ($retryType == self::TYPE_LATER) {
            throw new \LogicException('RetryType<later> not implements yet.');
        }
        $this->retryType = $retryType;
        $this->setCurrentRetryCnt(0);
    }

    /**
     * @return mixed
     */
    public function getMaxRetryCnt()
    {
        return $this->maxRetryCnt;
    }

    /**
     * @param mixed $maxRetryCnt
     *
     * @return self
     */
    public function setMaxRetryCnt($maxRetryCnt)
    {
        $this->maxRetryCnt = $maxRetryCnt;

        return $this;
    }

    /**
     * @return int
     */
    public function getRetryType()
    {
        return $this->retryType;
    }

    /**
     * @param int $retryType
     *
     * @return self
     */
    public function setRetryType($retryType)
    {
        $this->retryType = $retryType;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurrentRetryCnt()
    {
        return $this->currentRetryCnt;
    }

    /**
     * @param int $currentRetryCnt
     *
     * @return self
     */
    public function setCurrentRetryCnt($currentRetryCnt)
    {
        $this->currentRetryCnt = $currentRetryCnt;

        return $this;
    }

    /**
     * @param int $num
     *
     * @return self
     */
    public function increaseRetryCnt($num = 1)
    {
        $this->currentRetryCnt += $num;

        return $this;
    }

    public function __toString()
    {
        $type = $this->retryType == self::TYPE_IMMEDIATELY ? 'immediately' : 'later';

        return "<currentRetry={$this->currentRetryCnt},maxRetry={$this->maxRetryCnt},retryType={$type}>";
    }
}
