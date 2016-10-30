<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午3:56
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Observer;

interface Observable
{
    public function fireEvent($event);

    public function addListener($event, \Closure $callback, $opt = array());
}
