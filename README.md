# multiprocessing

###[English Doc](./README_EN.md) [Change Log](./CHANGELOG.md)
一个非常非常轻量级的多进程处理的PHP框架。目前使用共享内存/信号量进行进程间通信(IPC)

# 特性

- 轻松创建多进程,不用担心IPC问题
- worker使用观察者模式,支持注册worker的开始/重试/成功/失败等事件。比如在重试的时候修改worker属性
- worker支持重试机制.(立即重试/放入队列稍后重试)
- API简单,用户实现自己在process中的业务逻辑
- 方便扩展,用户可以自己实现自己比如DB相关的数据队列(实现`MultiProcessing\Contracts\Queue`)
- 我想不到了

# 快速开始

## 环境要求

该项目在PHP5.6,Mac OS下开发,但是没有使用[]表示数组/yield关键字等PHP高版本的语法特性，理论上PHP5.3+都支持。

- Linux/MacOS
- PHP 5.3+ (Not PHP7) with `-enable-pcntl` and `--enable-sysvshm`
- [Composer](https://getcomposer.org/)

## 使用步骤

如果需要自己创建一个多进程任务，你需要几分钟完成以下工作(比起自己去创建/管理多进程来说这个时间很短啦)

1. 参见`demo/BaiduCrawler.php`, 将你自己的worker类继承`\MultiProcessing\Worker`即可,并且同时实现`process`方法。需要注意的是实现process方法时候，请返回实现了`MultiProcessing\Contracts\Response`的类型，否则worker将把自动转为类型为`JobResponse`(该类实现了`MultiProcessing\Contracts\Response`接口)
2. 添加自己喜欢的Listener回调,比如`JobEvent::JOB_SUCCESS`之类的。
3. 调用`\MultiProcessing\Process\Process`并且设置好进程数量;然后调用`map`这个API即可。

当然，你可以自己查看demo目录下的代码



# 代码结构

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



# TODO

:smile: 等我想想 …..
