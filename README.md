# multiprocessing
The Lightweight MultiProcessing Library for PHP

# Requires

* Linux/MacOS
* PHP 5.3+ (Not PHP7) with `-enable-pcntl` and `--enable-sysvshm`
* [Composer](https://getcomposer.org/)

# How
From the demo folder, you can see the `demo.php` file:

```zsh
➜  multiprocessing git:(master) ✗  php demo/demo.php
Demo 1. BaiduSpider(Run BaiduSpider With EventListener)
Job need retry, Change BaseURL:http://www.123.com/
Job need retry, Change BaseURL:http://www.123.com/
Job need retry, Change BaseURL:http://www.123.com/
Job need retry, Change BaseURL:http://www.123.com/
Job need retry, Change BaseURL:http://www.123.com/
Job #[MultiProcessing_4] Run at:[2016-10-31T16:27:17+0800],url:[http://www.baidu.com] Successful
Job #[MultiProcessing_2] Run at:[2016-10-31T16:27:17+0800],url:[http://www.baidu.com] Successful
Job #[MultiProcessing_0] Run at:[2016-10-31T16:27:18+0800],url:[http://www.baidu.com] Successful
Job #[MultiProcessing_3] Run at:[2016-10-31T16:27:18+0800],url:[http://www.baidu.com] Successful
Job #[MultiProcessing_1] Run at:[2016-10-31T16:27:19+0800],url:[http://www.baidu.com] Successful
BaiduSpider finished. Time elapsed:3.2079880237579 sec
Demo 2.Get sum of range(1..100) (Use SharedMemQueue to save each process result)
Sums finished,get result:5050, use time:0.13310813903809 sec

```

# Change Logs
* 2016-10-31 Add composer support && Add Worker(with [superClosure](https://github.com/jeremeamia/super_closure) support) to jobQueue to make `RetryStrategy::LATER` can work.
* 2016-10-30 First repo i committed.