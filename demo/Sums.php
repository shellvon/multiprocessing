<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/30
 * @time: 上午11:30
 *
 * @version 1.0
 */
class Sums extends MultiProcessing\Worker
{
    public function process($arguments = null)
    {
        parent::process($arguments);

        return array_sum($arguments);
    }
}
