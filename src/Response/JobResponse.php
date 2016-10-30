<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午11:09
 *
 * @version 1.0
 */

namespace MultiProcessing\Response;

use MultiProcessing\Contracts\Response\Response;

class JobResponse implements Response
{
    const STATUS_OK = 0;

    /**
     * @var \Exception|null
     */
    protected $exceptions = null;

    protected $body = null;

    protected $exitCode = 0;

    public function __construct($body = null, $exitCode = 0, $exceptions = null)
    {
        $this->body = $body;
        $this->exitCode = $exitCode;
        $this->exceptions = $exceptions;
    }

    public function isOk()
    {
        return $this->exitCode === self::STATUS_OK;
    }

    /**
     * @return \Exception|null
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * @return mixed 消息体.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return int 错误代码.
     */
    public function getExitCode()
    {
        return $this->exitCode;
    }
}
