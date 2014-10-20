vframework - app-grabber
==========

### 介绍

app-grabber，一个网页抓取服务。


**提供接口：**

> #### /url/fetch
> 抓取指定url的网页源代码；
> 注意：此接口仅返回一个任务 ID，内容将异步抓取。

**主要方法：**
```php
@param string @url  要抓取的链接。必填
@param string @strategy 应用的策略号，默认： default（可通过 /strategy/* 接口配置）
@param string @callback 抓取完成后回调的链接，默认：对应策略的配置项。

@return mixed 返回一个任务ID，随时可以用此ID来查询抓取状态

```

> #### /url/syncFetch
> 抓取指定url的网页源代码（同步返回内容）

**主要方法：**
```php
@param string @url  要抓取的链接。必填
@param string @strategy 应用的策略号，默认： default（可通过 /strategy/* 接口配置）

@return mixed 如果 async = true，返回

```