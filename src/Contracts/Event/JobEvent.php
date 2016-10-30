<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午3:59
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Event;

interface JobEvent
{
    const JOB_RETRY = 'job.event.retry';
    const JOB_START = 'job.event.start';
    const JOB_RUNNING = 'job.event.running';
    const JOB_SUCCESS = 'job.event.success';
    const JOB_FAILED = 'job.event.failed';
    const JOB_CANCELED = 'job.event.canceled';
}
