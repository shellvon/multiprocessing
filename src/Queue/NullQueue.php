<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/30
 * @time: 上午10:55
 *
 * @version 1.0
 */

namespace MultiProcessing\Queue;

use MultiProcessing\Contracts\Queue\Queue;

class NullQueue implements Queue
{
    public function offsetExists($offset)
    {
        return false;
    }

    public function offsetGet($offset)
    {
        return;
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

    public function put($value)
    {
        // TODO: Implement put() method.
    }

    public function pop()
    {
        // TODO: Implement pop() method.
    }

    public function clear()
    {
        // TODO: Implement clear() method.
    }

    public function size()
    {
        return 0;
    }

    public function getAll($bEmpty = true)
    {
        return array();
    }

    public function destroy()
    {
        //
    }

    public function __toString()
    {
        return 'MultiProcessing <NullQueue>';
    }
}
