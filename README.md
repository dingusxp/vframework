vframework
==========

### 介绍

vframework 是一个基础的 PHP 框架。他将框架的各个要素以“组件”的形式来提供，从而让你可以通过配置和添加自己的组件来定制出更适合自己项目的基础架构。


### 框架目录：

```php
config    // 框架默认配置，所有配置都可以在自己应用中覆盖之
language  // 语言包
library   // 框架类库
shell     // 辅助脚本
test      // 测试脚本
tpl       // 模板
V.php     // 框架主文件；使用框架时，只需要包含这个文件即可
compiler.php  // 编译加速；实验用，请忽略
_compile.php  // 编译加速；实验用，请忽略
```

### 框架主要文件和组件介绍：

> #### V.php
> **主文件，包含了环境初始化（类库自动加载规则、timezone 设置等）**

**方法列表：**

```php
V::runtime() // 获取脚本运行时间（从框架加载开始）
V::timestamp() // 获取脚本加载时的时间戳
V::init() // 初始化；包含 V.php 时已自动调用
V::autoload($class) // 注册到 spl_autoload_register 的类自动加载方法；规则：类名 = 目录[_子目录]_文件名，区分大小写，支持命名空间
V::loadBootstrap($loader, $config = '') // 加载 Bootstrap 组件，将执行流程交由其控制
V::addLibrary($sourceName, $path) // 添加类库包路径（用于自动加载时查找）
V::removeLibrary($sourceName) // 移除类库包路径
V::import($resource, $source = 'library') // 加载一个指定的资源文件
V::t($lang, $params = array(), $group = 'global') // 输出语言：自动加载语言包做映射和解析变量
V::setLocale($locale) // 设置时区
V::config($key, $default = null) // 获取指定 key 对应的配置；对 CONFIG_PATH 下的配置文件，以 文件名.key.subkey 方式访问
V::log($message, $type = Logger::LEVEL_INFO) // 记录一条日志
V::debug() // 打印调试信息
```

#### 快速开始1：

**现在我们已经可以开始使用框架：**

```php
define('APP_PATH', dirname(__FILE__) . '/protected');
define('V_DEBUG', true);

try {
    require_once dirname(__FILE__) . '/framework/V.php';
    V::loadBootstrap('runtime')->run(); // 可选，Bootstrap_Runtime 啥事也不干
} catch (Exception $e) {
    echo defined('V_DEBUG') && V_DEBUG ? $e->getMessage() : 'Error';
}

// mycode here
// 可以使用 V 的各种方法，也可以直接使用 类库包 里的各种类。

```

**这明显不够用是吧~~~ 至少实现一个标准的 MVC 模式吧：（嗯，继续）**

> #### Component
> 定义了组件的组成：
> 一个组件需要继承 Component；同时实现一个 [ComponentName]_Abstract 名的基类（用来定义该组件的公用方法），以及一个 [ComponentName]_Exception 的异常类。

**主要方法：**
```php
protected static function _factory($componentName, $engine, $option = array())  // 工厂，实例化一个组件对象

```

> #### Bootstrap
> 引导器；规定后续代码的执行流程
> 内置实现：Bootstrap_Web： 走标准web流程： 路由分发 -> 执行对应action

**Bootstrap_Abstract： 接口**
```php
abstract public function run(); // 执行引导器规范的流程

```

**默认MVC模式**
```bash
Router -> Controller::actionActionName <-> Model <-> DAO
                                       <-> View
       -> render view
       -> url redirect
       
```

> #### Router
> 路由；配置规则，将请求（通常根据 url）对应到需要执行的操作
> 内置实现：Router_Simple： 根据 r=controller/action 规则映射到对应 Controller 的方法；
> 内置实现：Router_Regexp： 用正则表达式规则做映射，action 可以是 Router 支持的几种：action、view、redirect；

**Router_Abstract： 接口**
```php
abstract public function parse(); // 解析出当前请求需要执行的操作

```


> #### Controller
> 控制器：处理请求输入（Web_Request），调用对应方法进行逻辑处理，并输出反馈（Web_Response）

**Controller_Abstract： 接口**
```php
public function  __construct() // 实例化组件作为属性： Web_Request， Web_Session， Web_Response， View
public function doAction($action) // 调用本实例的 action[$action] 方法。并在执行前后分别触发： _beforeAction 和 _afterAction 方法

```



> #### Web_Request
> 请求输入信息获取，包括 GET、POST、FILE、COOKIE、SERVER 等

**Web_Request： 接口**
```php


```






