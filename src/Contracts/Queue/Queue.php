<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午5:19
 *
 * @version 1.0
 */

namespace MultiProcessing\Contracts\Queue;

interface Queue extends \ArrayAccess
{
    public function put($value);

    public function pop();

    public function clear();

    public function size();

    public function getAll($bEmpty = true);

    public function destroy();
}
