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

> #### Bootstrap 【组件】
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

> #### Router 【组件】
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
public function doAction($action) // 执行流程：_beforeAction -> $actionName() -> _afterAction -> 输出 Web_Response 内容

```


> #### Web_Request
> 请求输入信息获取，包括 GET、POST、FILE、COOKIE、SERVER 等


**Web_Request： 接口**
```php
public static function getInstance() // 获取唯一实例；初始化时会取消魔术引号效果（即使 php.ini 配置了）；配置中可以开启注入检测
public function get($name, $default = '')  // 获取一个 GET 值，可以指定 !isset() 时的默认值
public function post($name, $default = '') // 获取一个 POST 值，可以指定 !isset() 时的默认值
public function server($name, $default = '') // 获取一个 $_SERVER 值，可以指定 !isset() 时的默认值
public function file($name, $key = '')  // 获取一个 $_FILE 值
public function cookie($name, $default = '') // 获取一个 COOKIE 值
public function referer()  // 获取链接来源
public function getClientIp()  // 获取客户端IP
public function getBrowser()   // 获取客户端浏览器
public function getOS()  // 获取客户端操作系统
public function isPost()  // 判断是否POST请求
public function isAjax($allowInajax = false)  // 判断是否 ajax 请求
public function isSpider()  // 判断是否蜘蛛
public function url()  // 获取当前访问链接
public function checkIfNotModified($eTag)  // 检测etag值是否有变化
public function checkReferer()  // 判断请求来源和当前链接是否同域

```

**Web_Response： 接口**
```php
public static function getInstance() // 获取唯一实例
public function setOutputObContent($isOutput)  // 设置是否输出缓冲区内容；如果否，则只会输出设置在 body 变量里的内容
public function setOuputGZip($isEnableGzip)  // 设置是否对输出内容做 gzip 压缩
public function setRawHeader($content, $replace = true) // 设置输出头信息（原始格式）
public function setHeader($type, $content, $replace = true) // 设置输出头信息
public function clearHeader($type = '') // 清除全部头信息设置
public function setResponseCode($code)  // 设置 http 头返回码
public function setBody($content)  // 设置 body 内容
public function appendBody($content)  // 追加内容
public function registerRewriteCallback($callback)  // 注册一个 rewrite 函数，在输出 ouput 时重写内容
public function removeRewriteCallback($callback = '')  // 移除一个注册的 rewrite 函数
public function cancelOutput() // 关闭 Response 对象默认输出
public function output($ob = true)  // 输出
public function setNoCache()  // 设置头信息为 不缓存
public function redirect($url, $isPermanent = false)  // 重定向到指定 url
public function returnNotModifed()  // 输出时仅返回 304 状态码
public function setEtag($eTag) // 设置etag，以便比较内容是否有变化
public function setCookie($key, $value, $expired = 0, $path = null, $domain = null)  // 设置 cookie
public function clearCookie($keys = '') // 清除指定或全部 cookie

```

> #### View 【组件】
> 模版引擎，根据变量赋值来解析模板
> 内置 View_PHP，以 php 语法作为模板语言进行解析

**View_Abstract： 接口**
```php
public function assign($key, $value = null) // 设置一个模板变量
public function render($tpl, $target = View::RENDER_WEB_RESPONSE) // 解析指定模板并输出内容到 target（直接返回内容/追加内容到 Response 实例的 body 里）

```


> #### Model
> 业务处理模块。根据 controller 的输入，调用 DAO/其它 Model 获取数据进行业务逻辑处理或执行操作。

**Model：组件**
```php
public static function getInstance($engine)  // 获取指定名称 Model 的一个唯一实例
public static function getDAO($engine)  // 获取指定名称 DAO 的一个唯一实例

```

**Model_Abstract： 接口**
```php
// 空

```

> #### DAO
> 各种数据源的访问控制，如 Mysql
> 内置 DAO_Mysql 组件；每个表对应一个实例的方式，简单配置即可方便的调用



**其它辅助类组件**

> #### Cache 【组件】
> 缓存操作
> 内置 Cache_File，Cache_APC，Cache_Memcache 三种缓存操作方式

**Cache_Abstrct： 接口**
```php
abstract public function save($id, $data, $expired = null);  // 设置一个 id 对应的数据
abstract public function load($id);  // 获取指定 id 对应的数据
abstract public function remove($id);  // 移除指定 id 
abstract public function clean(); // 移除所有缓存

```


> #### Cryptor 【组件】
> 文本加密解密
> 内置 Cryptor_Xor，以 异或 方式进行加密解密

**Cryptor_Abstract： 接口**
```php
abstract public function encrypt($string, $skey = ''); // 使用 skey 加密指定字符串
abstract public function decrypt($string, $skey = '');  // 使用 skey 解密指定字符串

```

> #### DB 【组件】
> 数据库操作
> 内置 DB_Pdo，以 PDO 方式操作数据库

**DB_Abstract： 接口**
```php
abstract function connect(array $option = array()); // 连接到数据库
abstract public function begin(); // 开启事物
abstract public function commit(); // 提交事务
abstract public function rollBack(); // 回滚事务
abstract public function fetchRow($query, $params = array()); // 取得查询记录的第一行
abstract public function fetchAll($query, $params = array()); // 取得所有查询的记录
abstract public function fetchOne($query, $params = array()); // 获取记录的第一行第一列
abstract public function execute($query, $params = array()); // 执行sql 语句，返回操作影响的行数
abstract public function lastInsertId(); // 获取最后插入记录的id
abstract public function close(); // 关闭数据库连接
public function escape($value) // 过滤变量中 sql 非法字符

```

> #### Image 【组件】
> 图像处理
> 内置 Image_GD，调用 GD 库进行处理

**Image_Abstract： 接口**
```php
abstract public function create($width, $height, $bgColor = '#FFFFFF', $type = Image::TYPE_JPG); // 创建一个空白图像
abstract public function createCaptcha($code, $width = 200, $height = 60); // 创建一个验证码图像
abstract public function loadFromFile($file); // 载入图片
public function getWidth()  // 获取当前图片宽度
public function getHeight() // 获取当前图片高度
public function getType()   // 获取当前图片类型
abstract public function resize($newWidth, $newHeight, $resizeMode = Image::RESIZE_STRETCH); // 调整大小（缩略图）
abstract public function addText($text, $px, $py, $color = '#FFFFFF', $fontSize = 5); // 图片上追加文本内容（文字水印）
abstract public function merge(Image_Abstract $im, $px, $py, $w, $h, $opacity = 100);  // 图片合并（图片水印）
abstract public function output($target = Image::OUTPUT_WEB_RESPONSE, $option = array()); // 输出编辑后的图片
abstract public function isSupport($type);  // 检查当前环境是否支持指定类型图像处理

```

> #### HTML
> HTML 处理相关，如 过滤，输出 widget 等

**HTML**
```php
public static function clean($html, $allowTags = '') // 移除文本中 html 标签
public static function escape($html, $convertAllEntities = false)  // 文本中的 html 便签转义
public static function widget($engine, $option = array())  // 输出一个 widget 生成的内容

```



> #### Storage 【组件】
> 文件存储操作，
> 内置 Storage_Local，进行本地文件操作
> 内置 Storage_Sae，可以操作 SAE 上的 storage 服务

**Storage_Abstract：接口**
```php
abstract public function write($path, $content); // 写入内容到指定路径（写文件）
abstract public function upload($path, $localPath);  // 上传本地文件到指定路径
abstract public function read($path);  // 读取指定路径的文件内容
abstract public function remove($path);  // 删除指定路径文件
abstract public function rmdir($path);  // 删除指定目录
abstract public function isExist($path);  // 判断文件是否存在
abstract public function getUrl($path);  // 获取文件对应的可访问 url

```

> #### Validator
> 变量验证，通常用于用户输入。
> 内置一些常见规则，如 url，email 等。可以通过配置自己添加其它规则




