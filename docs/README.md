# 介绍

`vframework` 是一个基础的`PHP`框架。他将框架的各个要素以“组件”的形式来提供，从而让你可以通过配置和添加自己的组件来定制出更适合自己项目的基础架构。

## 安装

安装`vframework` ,你可以在github上下载源码包或者直接fork项目然后clone到本地. [传送门](https://github.com/dingusxp/vframework)
 
当然为了切合`现代化`的php，请使用php7.+， 如果你不想使用php7，`vframework`也是向下兼容的。

建议的服务器环境要求

* PHP >= 7.2
* OpenSSL PHP 扩展
* JSON PHP 扩展
* PDO PHP 扩展 （如需要使用到 MySQL 客户端）
* Redis PHP 扩展 （如需要使用到 Redis 客户端）

## 目录结构

`vframework`的文件夹结构

* framework目录(框架目录)
    * config目录
    * language目录
    * library目录
    * shell目录
    * test目录
    * tpl目录
    * 框架引导文件V.php
* app目录(应用程序目录)
    * config目录
    * language目录
    * library目录
    * shell目录
    * test目录
    * tpl目录
    * 框架引导文件V.php

### framework config目录

    这个目录存放了框架启动的配置
    
### framework language目录

    框架语言包配置
