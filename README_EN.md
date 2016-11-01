# multiprocessing

### [中文版文档](./README.md)

A very **very** light weight PHP framework for multi processing.Currently implemented IPC by
[Shared memory](https://en.wikipedia.org/wiki/Shared_memory)/[Semaphore](https://en.wikipedia.org/wiki/Semaphore_(programming)).

## Features
* Easy to create processes without worrying about IPC
* **Retry support** (retry immediately or later is both available!)
* Observer Pattern: start/retry/success/failure **event callback for worker support**.
* Simple API
* Easy to extend: lots of interfaces in `MultiProcessing\Contracts`

## Quick start

#### Required Environment/Compatibility
* Linux/Mac OS X
* PHP 5.3+ with `-enable-pcntl` and `--enable-sysvshm`
* [Composer](https://getcomposer.org/)

> This project is developed on PHP 5.6, Mac OS X, but haven't use `[]` to present array,
neither use keyword like `yield`, and other features of higher version than PHP5.3, so PHP 5.3
is supported in theory.

#### Usage
Only a few steps to create a multi process task:
1. Extend `MultiProcessing\Worker` and implement `process` to define your own worker class.
2. Add listeners for events you care about, e.g. `JobEvent::JOB_SUCCESS`.
3. Call `\MultiProcessing\Process\Process` and set max count for processes, then call `map`.
 > NOTE: If you do not return a type that already implements `MultiProcessing\Contracts\Response`
  on `process` method, worker would automatically cast it to `JobResponse`, which
  implemented`Response` interface properly.

That's all!

## Source Structure
```
multiprocessing-+
                |--demo-+
                |       |--BaiduCrawler.php // Curl爬百度首页的演示代码
                |       |--Sums.php         // 求和的演示代码
        		|       |--demo.php         // 演示代码,执行php demo.php 看效果
                |       |--clearIPC.py      // MacOS下清理共享数据的python脚本
                |
                |
                |--src--+                    //核心文件目录
                |       |--BootStrap-+
                |       |            |--Autoloader.php  // 自动加载
                |       |--Contract-+
                |       |           |--//各种接口，高级用户需要自己实现某些接口以完成任务
                |       |--Events-+
                |       |		  |--JobEvents.php    // 任务事件.用于事件监听
                |       |--Process-+
                |       |          |--Process.php     // 多进程管理相关文件
                |       |--Queue-+
                | 		|		 |--SharedMemQueue.php // 利用共享内存实现的队列
                |       |     	 |--NullQueue.php      // 空队列，啥也不做
                |		|--Response-+
                |       |           |--JobResponse.php
                |       |--Strategy-+
                |       |           |--RetryStrategy.php // 重试策略.
                |       |--Worker.php  // Worker类，用户需要继承然后实现它的process方法
                |--vendor              //Composer管理的第三方库
```

## TODO
###### No clear plan now; but any idea is welcome. :smile:

## Thanks
[Muyangmin](https://github.com/Muyangmin)