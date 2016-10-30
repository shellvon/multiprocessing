<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午9:19
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Observer;

use MultiProcessing\Contracts\Job\Job;

interface Observer
{
    public function failed(Job $job);

    public function success(Job $job);
}
