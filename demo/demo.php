<?php
/**
 * Created by shellvon.
 *
 * @author: shellvon<iamshellvon@gmail.com>
 * @date: 2016/10/27
 * @time: 下午4:03
 *
 * @version 1.0
 */
define('APP_ROOT', __DIR__.DIRECTORY_SEPARATOR);
date_default_timezone_set('PRC');
require_once APP_ROOT.'/../src/Bootstrap/Autoloader.php';

\MultiProcessing\Bootstrap\Autoloader::instance()->addRoot(APP_ROOT)->init();

use MultiProcessing\Contracts\Event\JobEvent;
use MultiProcessing\Strategy\RetryStrategy;
use MultiProcessing\Queue\SharedMemQueue;

// Demo 1. BaiduSpider
$worker = new BaiduCrawler('Baidu Spider');
$processes = new \MultiProcessing\Process\Process(4);

$worker->addListener(JobEvent::JOB_SUCCESS, function () use ($worker) {
    $id = $worker->getJobId();
    $time = date(DATE_ISO8601);
    echo "Job #[$id] Run at:[$time] Successful\n";
})->addListener(JobEvent::JOB_RUNNING, function () use ($worker) {
    $id = $worker->getJobId();
    $params = $worker->getParams();
    echo "Job #[$id] Running, params:".http_build_query($params['params'])."\n";
})->addListener(JobEvent::JOB_FAILED, function () use ($worker) {
    $strategy = $worker->getRetryStrategy();
    if ($strategy != null) {
        echo "Job failed after use strategy:{$strategy}\n";
    }
    echo 'Job failed caused by:'.$worker->getResult()->getExceptions()->getMessage()."\n";
})->addListener(JobEvent::JOB_RETRY, function () {
    echo 'Use Retry....';
})->setRetryStrategy(new RetryStrategy(1, RetryStrategy::TYPE_IMMEDIATELY));

$params = array();
foreach (range(1, 3) as $idx) {
    $params[] = array('params' => array('idx' => $idx), 'endPoint' => '');
}
$start = microtime(true);
$responses = $processes->map($worker, $params);
$elapsed = microtime(true) - $start;
echo "run BaiduSpider,use time:{$elapsed} sec\n";

// Demo 2. get sums.
$worker = new Sums();
// 使用共享内存保存单个worker的结果。
$processes = new \MultiProcessing\Process\Process(4, new SharedMemQueue());

echo "get sum of range(1..100):\n";
$start = microtime(true);
$sum = 0;
foreach ($processes->map($worker, array_chunk(range(1, 100), 20)) as $resp) {
    if ($resp->isOk()) {
        $sum += $resp->getBody();
    } else {
        echo "Error occurred.\n";
    }
}
$elapsed = microtime(true) - $start;
echo "run Sums, get result:{$sum}, use time:{$elapsed} sec\n";
