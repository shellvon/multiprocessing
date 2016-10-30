<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/28
 * @time: 上午10:40
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Response;

interface Response
{
    public function isOk();

    public function getExceptions();

    public function getBody();

    public function getExitCode();
}
