<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午4:02
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Job;

interface Job
{
    public function start();

    public function getJobId();

    public function cancel();

    public function getJobStatus();

    public function getResult();
}
