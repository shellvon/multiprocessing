# multiprocessing
The Lightweight MultiProcessing Library for PHP

# Requires

* Linux/MacOS
* PHP 5.3+ (Not PHP7) with `-enable-pcntl` and `--enable-sysvshm`

# How
From the demo folder, you can see the `demo.php` file:

```zsh
➜  demo git:(master) ✗  php demo.php
INFO:fork child pid:48291
Job #[48291] Running, params:idx=3
INFO:fork child pid:48292
....
Job failed after use strategy:<currentRetry=1,maxRetry=1,retryType=immediately>
Job failed caused by:Recv failure: Connection reset by peer
INFO:Child exit
run BaiduSpider,use time:0.28525900840759 sec
get sum of range(1..100):
....
run Sums, get result:5050, use time:0.064109086990356 sec
```

# Change Logs
* 2016-10-30 First repo i committed.