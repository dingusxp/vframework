<?php

// define APP_PATH, APP_NAME, ROOT_PATH
if (!defined('APP_PATH')) {

    // 自动检测，取 apps/[APPNAME] 作为目录
    $trace = debug_backtrace();
    $info = array_pop($trace);
    $matches = array();
    if ($trace && preg_match('/^(.*?)([\\\\\/])apps\\2(\w+)\\2/', $info['file'], $matches)) {
        define('APP_PATH', $matches[1] . $matches[2] . $matches[3]);
    } else {
        exit('Please define APP_PATH or include framework under directory "apps"');
    }
}
defined('CONFIG_PATH') or define('CONFIG_PATH', APP_PATH . '/config');

// V_PATH 框架目录
defined('V_PATH') or define('V_PATH', dirname(__FILE__));

// V_DEBUG 是否开启调试模式
defined('V_DEBUG') or define('V_DEBUG', false);

// V_START 开始时间
define('V_START', microtime(true));

/**
 * V_Exception
 */
class V_Exception extends Exception {

    /**
     * 异常类型
     */
    protected $_type = null;

    /**
     * 继承层级
     * @var type
     */
    protected $_level = 0;

    /**
     * 由下级未做处理直接传递过来的异常
     */
    const E_PASSON_EXCEPTION = 987654321;

    /**
     * 通用异常码： 参数错误
     */
    const E_PARAM_ERROR = 2;

    /**
     * 通用异常码： 逻辑错误
     */
    const E_LOGIC_ERROR = 1;

    /**
     * 异常： 引导器不能运行；
     */
    const E_BOOTSTRAP_CANNOT_RUN = 11;

    /**
     * 异常： 引导器已经在运行；
     */
    const E_BOOTSTRAP_INIT_ERROR = 12;

    /**
     * 构造函数
     * @param <type> $message
     * @param <type> $code
     */
    public function __construct($message, $code = 0) {

        if ($message instanceof Exception) {
            parent::__construct($message->getMessage(), self::E_PASSON_EXCEPTION);
        } else {
            parent::__construct($message, intval($code));
        }
        $this->_level++;
    }

    /**
     * 获取异常类型：即 类名前缀小写
     * 如 DAO_Exception ，返回 dao
     *
     * @return string
     */
    public function getType() {

        if (null === $this->_type) {
            $this->_type = str_replace('_exception', '', strtolower(get_class($this)));
        }

        return $this->_type;
    }

    /**
     * 获取异常继承的层级
     *
     * @return int
     */
    public function getLevel() {

        return $this->_level;
    }
}

/**
 * V
 * 初始化运行环境；
 * 自动加载类；
 * 装载 bootstrap
 */
class V {

    /**
     * $_bootstrap
     * @var Bootstrap 引导类
     */
    private static $_bootstrap = null;

    /**
     * $_logger
     * @var Logger LOG记录（运行时的错误，异常等）
     */
    private static $_logger = null;

    /**
     * $_inited
     * @var boolean 是否已经初始化过了
     */
    private static $_inited = false;
    private static $_compileMode = false;

    /**
     * $_timestamp
     * @var integer 时间戳
     */
    private static $_timestamp = 0;

    /**
     * 已加载的资源
     * @var array
     */
    private static $_imports = array();

    /**
     * 配置项
     * @var array
     */
    private static $_config = array();

    /**
     * 语言包
     * @var array
     */
    private static $_lang = array();

    /**
     * 地区
     * 加载不同语言包
     * @var string
     */
    private static $_locale = 'en_US';

    /**
     * 自定义的资源库
     */
    private static $_libraries = array();

    /**
     * 返回运行到当前的毫秒数
     */
    public static function runtime() {

        return intval(1000 * (microtime(true) - V_START));
    }

    /**
     * 返回一个时间戳，在本次脚本执行内不变
     */
    public static function timestamp() {

        return self::$_timestamp;
    }

    /**
     * init
     * 初始化： 类自动加载； 调试模式；
     * @return void
     */
    public static function init() {

        if (self::$_inited) {
            return ;
        }
        self::$_inited = true;

        // 加载框架默认配置
        if (!self::$_compileMode) {
            $data = include(V_PATH . '/config/global.php');
            self::_loadConfig($data, 'v.');
        }

        // 加载应用全局配置
        if (!self::$_compileMode && is_file(CONFIG_PATH . '/global.php')) {
            $data = include(CONFIG_PATH . '/global.php');
            self::_loadConfig($data);
        }

        // timezone
        $timezone = self::config('timezone');
        date_default_timezone_set($timezone);

        // 时间戳
        self::$_timestamp = time();

        // Debug mode
        if (V_DEBUG) {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        } else {
            error_reporting(E_ERROR);
            ini_set('display_errors', 0);
        }

        // 加载语言包
        $locale = self::config('language.locale');
        self::setLocale($locale);
        if (!self::$_compileMode && is_file(V_PATH . '/language/' . $locale . '/framework.php')) {
            self::$_lang['framework'] = include(V_PATH . '/language/' . $locale . '/framework.php');
        }

        // 加载类库
        $libraries = (array)self::config('libraries')
			+ array(
                'app_src' => APP_PATH . '/src',
                'app_lib' => APP_PATH . '/lib',
                'library' => V_PATH . '/library'
			);
		foreach ($libraries as $sourceName => $path) {
			self::addLibrary($sourceName, $path);
		}

        // 预加载类库
        $imports = self::config('imports');
        if ($imports && is_array($imports)) {
            foreach ($imports as $import) {
                if (!is_array($import) || count($import) != 2) {
                    continue;
                }
                list($resource, $source) = $import;
                self::import($resource, $source);
            }
        }

        // 自动加载注册
        spl_autoload_register(array('V', 'autoload'));
        $autoloads = self::config('autoloads');
        if ($autoloads && is_array($autoloads)) {
            foreach ($autoloads as $autoload) {
                if (function_exists($autoload)) {
                    spl_autoload_register($autoload);
                }
            }
        }

        Timer::getInstance()->log('framework inited ...');
    }

    /**
     * 使用编译好的配置初始化
     * @param type $config
     */
    public static function compileInit($config) {

        self::$_config = $config;
        self::$_compileMode = true;
        self::init();
    }

    /**
     * autoload
     *
     * @staticvar array $libraries 框架资源类
     * @param string $class 类名
     * @return boolean
     */
    public static function autoload($class) {

        // Library
        static $libraries = array (
		  'Arr' => 'Arr',
		  'Bootstrap' => 'Bootstrap',
		  'Bootstrap_Abstract' => 'Bootstrap/Abstract',
		  'Bootstrap_Exception' => 'Bootstrap/Exception',
		  'Bootstrap_Runtime' => 'Bootstrap/Runtime',
		  'Bootstrap_Web' => 'Bootstrap/Web',
		  'Cache' => 'Cache',
		  'Cache_Abstract' => 'Cache/Abstract',
		  'Cache_Apc' => 'Cache/Apc',
		  'Cache_Exception' => 'Cache/Exception',
		  'Cache_File' => 'Cache/File',
		  'Cache_Memcache' => 'Cache/Memcache',
		  'Component' => 'Component',
		  'Component_Abstract' => 'Component/Abstract',
		  'Component_Exception' => 'Component/Exception',
		  'Controller' => 'Controller',
		  'Controller_Abstract' => 'Controller/Abstract',
		  'Controller_Exception' => 'Controller/Exception',
		  'Cryptor' => 'Cryptor',
		  'Cryptor_Abstract' => 'Cryptor/Abstract',
		  'Cryptor_Exception' => 'Cryptor/Exception',
		  'Cryptor_Xor' => 'Cryptor/Xor',
		  'DAO' => 'DAO',
		  'DAO_Abstract' => 'DAO/Abstract',
		  'DAO_Exception' => 'DAO/Exception',
		  'DAO_Mysql' => 'DAO/Mysql',
		  'DB' => 'DB',
		  'DB_Abstract' => 'DB/Abstract',
		  'DB_Exception' => 'DB/Exception',
		  'DB_Mysql' => 'DB/Mysql',
		  'DB_Pdo' => 'DB/Pdo',
		  'Debugger' => 'Debugger',
		  'FS' => 'FS',
		  'FileStore' => 'FileStore',
		  'HTML' => 'HTML',
		  'HTML_Exception' => 'HTML/Exception',
		  'HTML_Widget' => 'HTML/Widget',
		  'HTML_Widget_Abstract' => 'HTML/Widget/Abstract',
		  'HTML_Widget_Exception' => 'HTML/Widget/Exception',
		  'HTML_Widget_Pager' => 'HTML/Widget/Pager',
		  'Image' => 'Image',
		  'Image_Abstract' => 'Image/Abstract',
		  'Image_Exception' => 'Image/Exception',
		  'Image_GD' => 'Image/GD',
		  'Logger' => 'Logger',
		  'Logger_Abstract' => 'Logger/Abstract',
		  'Logger_Exception' => 'Logger/Exception',
		  'Logger_File' => 'Logger/File',
		  'Model' => 'Model',
		  'Model_Abstract' => 'Model/Abstract',
		  'Model_Exception' => 'Model/Exception',
		  'PicStore' => 'PicStore',
		  'Router' => 'Router',
		  'Router_Abstract' => 'Router/Abstract',
		  'Router_Exception' => 'Router/Exception',
		  'Router_Regexp' => 'Router/Regexp',
		  'Router_Simple' => 'Router/Simple',
		  'Service' => 'Service',
		  'Storage' => 'Storage',
		  'Storage_Abstract' => 'Storage/Abstract',
		  'Storage_Exception' => 'Storage/Exception',
		  'Storage_Local' => 'Storage/Local',
		  'Storage_Sae' => 'Storage/Sae',
		  'Str' => 'Str',
		  'Timer' => 'Timer',
		  'Validator' => 'Validator',
		  'Validator_Exception' => 'Validator/Exception',
		  'Validator_Rule' => 'Validator/Rule',
		  'Validator_Rule_Abstract' => 'Validator/Rule/Abstract',
		  'Validator_Rule_Callback' => 'Validator/Rule/Callback',
		  'Validator_Rule_Regexp' => 'Validator/Rule/Regexp',
		  'View' => 'View',
		  'View_Abstract' => 'View/Abstract',
		  'View_Exception' => 'View/Exception',
		  'View_PHP' => 'View/PHP',
		  'Web_Cookie' => 'Web/Cookie',
		  'Web_Exception' => 'Web/Exception',
		  'Web_Request' => 'Web/Request',
		  'Web_Response' => 'Web/Response',
		  'Web_Session' => 'Web/Session',
		);
        if (isset($libraries[$class])) {
            return self::import($libraries[$class], 'library');
        }

        // 尝试从各资源库加载
        $resource = str_replace('_', '/', $class);

        // 兼容命名空间
        if (strpos($resource, '\\') !== false) {
            $resource = str_replace('\\', '/', $resource);
        }

        // 依次从资源库查找
        $sources = array_keys(self::$_libraries);
        foreach ($sources as $source) {
            if (self::import($resource, $source)) {
                return true;
            }
        }
        return false;
    }

    /**
     * loadBootStrap
     * 加载 bootstrap
     *
     * @param string $loader
     * @param string/array $config
     * @return Bootstrap
     */
    public static function loadBootstrap($loader, $config = '') {

        if (null !== self::$_bootstrap) {
            throw new V_Exception(self::t('Bootstrap can be load only once', array(), 'framework'), V_Exception::E_BOOTSTRAP_CANNOT_RUN);
        }

        // 全局配置
        if ($config) {
            if (!is_array($config) && is_file(CONFIG_PATH . '/' . $config . '.php')) {
                $config = include(CONFIG_PATH . '/' . $config . '.php');
            }
            self::_loadConfig($config);
        }

        // 加载配置
        $file = V_PATH . '/config/' . strtolower($loader) . '.php';
        if (!self::$_compileMode && is_file($file)) {
            $config = include($file);
            self::_loadConfig($config, 'v.' . strtolower($loader) . '.');
        }

        // 获取 bootstrap
        try {
            self::$_bootstrap = Bootstrap::factory($loader);
        } catch (Exception $e) {
            throw new V_Exception(self::t('Bootstrap init error: {message}', array('message' => $e->getMessage()), 'framework'), V_Exception::E_BOOTSTRAP_INIT_ERROR);
        }
        return self::$_bootstrap;
    }

    /**
     * 添加资源库
     * 以便自动或指定 import 文件
     * @param <type> $sourceName
     * @param <type> $path
     */
    public static function addLibrary($sourceName, $path) {

        if ($path && is_dir($path)) {
            self::$_libraries[$sourceName] = $path;
        }
    }

    /**
     * 移除资源库
     * @param <type> $sourceName
     */
    public static function removeLibrary($sourceName) {

        unset(self::$_libraries[$sourceName]);
    }

    /**
     * import
     * 加载资源
     *
     * @param string $resource 格式 /dir/dir/filename
     * @param string $source 来源： library, app, third
     * @return boolean
     */
    public static function import($resource, $source = 'library') {

        $resource = ltrim($resource, '\/');
        $key = $source . ':' . $resource;
        if (isset(self::$_imports[$key])) {
            return self::$_imports[$key];
        }

        // 可以从 框架， 应用， 第三方扩展下获取资源
        if (!isset(self::$_libraries[$source])) {
            return false;
        }

        $filename = self::$_libraries[$source] . DIRECTORY_SEPARATOR . $resource . '.php';
        if (is_file($filename)) {
            self::$_imports[$key] = include($filename);
        } else {
            self::$_imports[$key] = false;
        }
        return self::$_imports[$key];
    }

    /**
     * t
     * 返回处理后的语言
     *
     * @param string $lang
     * @param array $params
     * @param string $group
     * @return string
     */
    public static function t($lang, $params = array(), $group = 'global') {

        // 载入语言包
        if (!isset(self::$_lang[$group])) {
            $languages = array();
            $file = self::config('language.path') . '/' . self::$_locale . '/' . $group . '.php';
            if (is_file($file)) {
                $languages = include($file);
            }
            self::$_lang[$group] = (array)$languages;
        }

        // 替换变量
        $lang = isset(self::$_lang[$group][$lang]) ? self::$_lang[$group][$lang] : $lang;
        if ($params && is_array($params)) {
            $keys = array();
            foreach (array_keys($params) as $key) {
                $keys[] = '{' . $key . '}';
            }
            $lang = str_replace($keys, $params, $lang);
        }

        return $lang;
    }

    /**
     * setLocale
     * 设置地区，方便加载不同语言包
     * @param string $local
     */
    public static function setLocale($locale) {

        if ($locale && preg_match('/^\w{2,20}$/', $locale)) {
            self::$_locale = $locale;
        }
    }

    /**
     * config
     * 获取配置项
     *
     * @param string $key [dir.]file.array_key1[.array_key2]
     * @param mixed $default 若不存在时返回的默认值
     * @return mixed
     */
    public static function config($key, $default = null) {
        static $checked = array();

        if (array_key_exists($key, self::$_config)) {
            return self::$_config[$key];
        }

        // 自动加载配置文件
        $parts = explode('.', $key);
        $path = CONFIG_PATH;
        $prefix = '';
        for($i = 0, $len = count($parts); $i < $len; $i++) {
            $prefix .= ($i == 0 ? $parts[$i] : '.' . $parts[$i]);
            $path .= '/' . $parts[$i];

            // 检查满足条件的文件并加载
            if (!isset($checked['file'][$path])) {
                $checked['file'][$path] = is_file($path . '.php') ? true : false;

                // 加载
                if ($checked['file'][$path]) {
                    $data = include($path . '.php');
                    if (is_array($data)) {
                        self::$_config[$prefix] = $data;
                        self::_loadConfig($data, $prefix . '.');
                    }
                }
            }

            // 检查目录
            // 如果已经到 parts 最后一层，不用再检查
            if (!isset($checked['dir'][$path]) && $i != $len - 1) {
                $checked['dir'][$path] = is_dir($path) ? true : false;

                // 如果目录不存在，终止
                if (!$checked['dir'][$path]) {
                    break ;
                }
            }
        }

        // 若没有取到，取框架默认配置，再没有，返回默认值
        if (array_key_exists($key, self::$_config)) {
            return self::$_config[$key];
        } elseif (array_key_exists('v.' . $key, self::$_config)) {
            return self::$_config['v.' . $key];
        }

        // special
        if ($key == '*') return self::$_config;

        return $default;
    }

    /**
     * _loadConfig
     * 加载 配置项
     *
     * @param array $data
     * @param string $prefix
     */
    private static function _loadConfig($data, $prefix = '') {

        if (is_array($data)) {
            foreach($data as $key => $value) {
                $key = $prefix . $key;
                if (!array_key_exists($key, self::$_config)) {
                    self::$_config[$key] = $value;
                }
                if (is_array($value)) {
                    self::_loadConfig($value, $key . '.');
                }
            }
        }
    }

    /**
     * Log 记日志
     * @param <type> $type
     * @param <type> $message
     * @return <type>
     */
    public static function log($message, $type = Logger::LEVEL_INFO) {

        // 判断是否需要记录
        $level = self::config('logger.log_level');
        $type = intval($type);
        if (!$type || !($type & $level)) {
            return;
        }

        // 初始化 Logger
        if (!self::$_logger) {
            $param = self::config('logger');
            self::$_logger = Logger::factory($param['engine'], $param['option']);
        }

        self::$_logger->log($type, $message);
    }

    /**
     * 调试信息
     * @param <type> $action
     * @param <type> $param1
     * @param <type> $param2
     * ...
     */
    public static function debug() {

        if (! V_DEBUG) {
            return;
        }

        $params = func_get_args();
        call_user_func_array(array('Debugger', 'output'), $params);
    }
}

// 初始化
V::init();
