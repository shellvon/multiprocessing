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
require_once APP_ROOT.'/../vendor/autoload.php';

use MultiProcessing\Bootstrap\Autoloader;
use MultiProcessing\Contracts\Event\JobEvent;
use MultiProcessing\Strategy\RetryStrategy;
use MultiProcessing\Queue\SharedMemQueue;


Autoloader::instance()->addRoot(APP_ROOT)->init();

// Demo 1. BaiduSpider
echo "Demo 1. BaiduSpider(Run BaiduSpider With EventListener)\n";
$worker = new BaiduCrawler('Baidu Spider');
$processes = new \MultiProcessing\Process\Process(4);
$worker->addListener(JobEvent::JOB_SUCCESS, function () use ($worker) {
    $id = $worker->getJobId();
    $time = date(DATE_ISO8601);
    $url = $worker->getBaseUrl();
    echo "Job #[$id] Run at:[$time],url:[$url] Successful\n";
})->addListener(JobEvent::JOB_RUNNING, function () use ($worker) {
    // ....
})->addListener(JobEvent::JOB_FAILED, function () use ($worker) {
    $strategy = $worker->getRetryStrategy();
    if ($strategy != null) {
        echo "Job failed after use strategy:{$strategy}\n";
    }
    echo 'Job failed caused by:'.$worker->getResult()->getExceptions()->getMessage()."\n";
})->addListener(JobEvent::JOB_RETRY, function ($worker) { // pass $worker to callback with second optional arguments.
    echo "Job need retry, Change BaseURL:".$worker->getBaseUrl().PHP_EOL;
    $worker->setBaseUrl("http://www.baidu.com");
}, $worker)->setRetryStrategy(new RetryStrategy(3, RetryStrategy::TYPE_LATER));

$params = array();
foreach (range(1, 5) as $idx) {
    $params[] = array('params' => array('idx' => $idx), 'endPoint' => '');
}
$start = microtime(true);
$responses = $processes->map($worker, $params);
$elapsed = microtime(true) - $start;
echo "BaiduSpider finished. Time elapsed:{$elapsed} sec\n";

// Demo 2. get sums.
$worker = new Sums();
// 使用共享内存保存单个worker的结果。
$processes = new \MultiProcessing\Process\Process(4, new SharedMemQueue());
echo "Demo 2.Get sum of range(1..100) (Use SharedMemQueue to save each process result)\n";
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
echo "Sums finished,get result:{$sum}, use time:{$elapsed} sec\n";
