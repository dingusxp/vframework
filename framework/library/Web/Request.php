<?php

/**
 * web 请求信息
 */
class Web_Request {

    /**
     * get 数据
     * @var array
     */
    private $_get;

    /**
     * post 数据
     * @var array
     */
    private $_post;

    /**
     * file 数据
     * @var array
     */
    private $_file;

    /**
     * 服务端信息
     * @var array
     */
    private $_server;

    /**
     * cookie 实例
     * @var array
     */
    private $_cookie;

    /**
     * 实例
     * @var Web_Request
     */
    private static $_instance;

    /**
     * 是否已完成初始化
     * @var type
     */
    private $_inited = false;

    /**
     * 构造函数： 解析传入参数
     */
    private function  __construct() {

        // 取消 gpc 变量自动 addslashes
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            $_GET = Str::stripSlashes($_GET);
            $_POST = Str::stripSlashes($_POST);
            $_COOKIE = Str::stripSlashes($_COOKIE);
        }

        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_file = $_FILES;
        $this->_server = $_SERVER;
        $this->_cookie = Web_Cookie::getInstance();
    }

    /**
     * 初始化
     */
    public function init() {

        if ($this->_inited) {
            return;
        }
        $this->_inited = true;

        // 注入检测
        $config = V::config('web.request');
        if (!empty($config['check_injection'])) {
            $checks = array(
                'get' => $_GET,
                'post' => $_POST,
                'cookie' => $_COOKIE
                );
            $filter = array();
            $filter['get'] = "'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)";
            $filter['post'] = '\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)';
            $filter['cookie'] = '\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?Select|Update.+?SET|Insert\\s+INTO.+?VALUES|(Select|Delete).+?FROM|(Create|Alter|Drop|TRUNCATE)\\s+(TABLE|DATABASE)' ;
            foreach ($checks as $k => $value) {
                foreach ($value as $field => $item) {
                    if ($this->_checkInjection($item, $filter[$k]) && !empty($config['on_injection_detect'])) {
                        $param = array(
                            'type' => $k,
                            'field' => $field,
                            'value' => $item,
                        );
                        call_user_func($config['on_injection_detect'], $param);
                    }
                }
            }
        }
    }

    /**
     * 检查是否包含恶意注入代码
     * @param type $value
     * @param type $filter
     * @return boolean
     */
    private function _checkInjection($value, $filter) {

        if (is_array($value)) {
            $value = implode(';', $value);
        }
        if (preg_match('/'.$filter.'/is', $value)) {
            return true;
        }
        return false;
    }

    /**
     * 获取实例
     * @return <type>
     */
    public static function getInstance() {

        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
            self::$_instance->init();
        }
        return self::$_instance;
    }

    /**
     * 获取数组中变量的值
     * @param <type> $array
     * @param <type> $keys
     * @param <type> $default
     * @return <type>
     */
    private function _fetch($array, $keys, $default = '') {

        if ($keys) {
            return Arr::fetch($array, $keys, $default);
        }
        return $array;
    }

    /**
     * get
     * 获取指定 get 数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get($name, $default = '') {

        return isset($this->_get[$name]) ? $this->_get[$name] : $default;
    }

    /**
     * getx
     * 获取指定 get 数据
     *
     * @return array
     */
    public function getx() {

		$keys = func_get_args();
        return $this->_fetch($this->_get, $keys);
    }

    /**
     * post
     * 获取指定 post 数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function post($name, $default = '') {

        return isset($this->_post[$name]) ? $this->_post[$name] : $default;
    }

    /**
     * postx
     * 获取指定 post 数据
     *
     * @return array
     */
    public function postx() {

		$keys = func_get_args();
        return $this->_fetch($this->_post, $keys);
    }

    /**
     * server
     * 获取指定 server 数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function server($name, $default = '') {

        return isset($this->_server[$name]) ? $this->_server[$name] : $default;
    }

    /**
     * serverx
     * 获取指定 server 数据
     *
     * @return array
     */
    public function serverx() {

		$keys = func_get_args();
        return $this->_fetch($this->_server, $keys);
    }

    /**
     * 获取提交的文件信息
     * @param <type> $name
     * @param <type> $key
     * @return <type>
     */
    public function file($name, $key = '') {

        $file = array();
        if (isset($this->_file[$name])) {
            $file = $this->_file[$name];
        }
        if ($key) {
            return isset($file[$key]) ? $file[$key] : '';
        }
        return $file;
    }

    /**
     * cookie
     * 获取指定 cookie 数据
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function cookie($name, $default = '') {

        $cookies = $this->_cookie->getx();
        return isset($cookies[$name]) ? $cookies[$name] : $default;
    }

    /**
     * cookie
     * 获取指定 cookie 数据
     *
     * @return array
     */
    public function cookiex() {

		$keys = func_get_args();
        return $this->_fetch($this->_cookie->getx(), $keys);
    }

    /**
     * 链接来源
     * @return <type>
     */
    public function referer() {

        return $this->server('HTTP_REFERER');
    }

    /**
     * 获取客户端 IP
     */
    public function getClientIp() {

        if (isset($this->_server['client_ip'])) {
            return $this->_server['client_ip'];
        }

		$ip = $_SERVER['REMOTE_ADDR'];
        $matches = array();
		if (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
			foreach ($matches[0] AS $xip) {
				if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
					$ip = $xip;
					break;
				}
			}
		}

        $this->_server['client_ip'] = $ip;
        return $ip;
    }

	/**
	 * 获取客户端浏览器
	 *
	 */
	public function getBrowser(){

		if (isset($this->_server['client_browser'])) {
			return $this->_server['client_browser'];
		}

        $agent = $_SERVER['HTTP_USER_AGENT'];
        $regs = array();
        if (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs)) {
            $browser  = 'OmniWeb';
            $browser_ver   = $regs[2];
        }

        if (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Netscape';
            $browser_ver   = $regs[2];
        }

        if (preg_match('/safari\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Safari';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs)) {
            $browser  = 'Internet Explorer';
            $browser_ver   = $regs[1];
        }

        if(preg_match('/Chrome\/([^\s]+)/i',$agent,$regs)){
            $browser  = 'Chrome';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Opera';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs)) {
            $browser  = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Maxthon/i', $agent, $regs)) {
            $browser  = '(Internet Explorer ' .$browser_ver. ') Maxthon';
            $browser_ver   = '';
        }

        if (preg_match('/360SE/i', $agent, $regs)) {
                $browser       = '(Internet Explorer ' .$browser_ver. ') 360SE';
                $browser_ver   = '';
        }

        if (preg_match('/SE 2.x/i', $agent, $regs)) {
            $browser       = '(Internet Explorer ' .$browser_ver. ') 搜狗';
            $browser_ver   = '';
        }

        if (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'FireFox';
            $browser_ver   = $regs[1];
        }

        if (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs)) {
            $browser  = 'Lynx';
            $browser_ver   = $regs[1];
        }

        if ($browser != '') {
             $this->_server['client_browser'] = $browser.' '.$browser_ver;
        } else {
            $this->_server['client_browser'] = 'Unknow browser';
        }
		return $this->_server['client_browser'];
    }

	/**
	 * 获取客户端操作系统
	 *
	 */
    public function getOS(){

		if (isset($this->_server['client_os'])) {
			return $this->_server['client_os'];
		}

        $sys = $_SERVER['HTTP_USER_AGENT'];
        if(stripos($sys, 'NT 6.1')){
           $os = 'Windows 7';
        }
        elseif(stripos($sys, 'NT 6.0')){
           $os = 'Windows Vista';
        }
        elseif(stripos($sys, 'NT 5.1')){
           $os = 'Windows XP';
        }
        elseif(stripos($sys, 'NT 5.2')){
           $os = 'Windows Server 2003';
        }
        elseif(stripos($sys, 'NT 5')){
           $os = 'Windows 2000';
        }
        elseif(stripos($sys, 'NT 4.9')){
           $os = 'Windows ME';
        }
        elseif(stripos($sys, 'NT 4')){
           $os = 'Windows NT 4.0';
        }
        elseif(stripos($sys, '98')){
           $os = 'Windows 98';
        }
        elseif(stripos($sys, '95')){
           $os = 'Windows 95';
        }
        elseif(stripos($sys, 'Mac')){
           $os = 'Mac';
        }
        elseif(stripos($sys, 'Linux')){
           $os = 'Linux';
        }
        elseif(stripos($sys, 'Unix')){
           $os = 'Unix';
        }
        elseif(stripos($sys, 'FreeBSD')){
           $os = 'FreeBSD';
        }
        elseif(stripos($sys, 'SunOS')){
           $os = 'SunOS';
        }
        elseif(stripos($sys, 'BeOS')){
           $os = 'BeOS';
        }
        elseif(stripos($sys, 'OS/2')){
           $os = 'OS/2';
        }
        elseif(stripos($sys, 'PC')){
           $os = 'Macintosh';
        }
		elseif(stripos($sys, 'AIX')){
           $os = 'AIX';
        }
		else{
           $os = '未知操作系统';
        }

		$this->_server['client_os'] = $os;
        return $os;
    }

    /**
     * 判断是否 POST 请求
     * @return boolean
     */
    public function isPost() {

        return ('POST' == strtoupper($_SERVER['REQUEST_METHOD'])) ? true : false;
    }

    /**
     * 判断是否 Ajax 请求
     * @return boolean
     */
    public function isAjax($allowInajax = false) {

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return true;
        } elseif ($allowInajax && $this->get('inajax')) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否搜索引擎蜘蛛访问
     */
    public function isSpider() {

        if (isset($this->_server['is_spider'])) {
            return $this->_server['is_spider'];
        }

        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (empty($agent)) {
            $this->_server['is_spider'] = false;
            return false;
        }

        $spiderSites = array(
                'TencentTraveler',
                'Baiduspider+',
                'BaiduGame',
                'Googlebot',
                'msnbot',
                'Sosospider+',
                'Sogou web spider',
                'ia_archiver',
                'Yahoo! Slurp',
                'YoudaoBot',
                'Yahoo Slurp',
                'MSNBot',
                'Java (Often spam bot)',
                'BaiDuSpider',
                'Voila',
                'Yandex bot',
                'BSpider',
                'twiceler',
                'Sogou Spider',
                'Speedy Spider',
                'Google AdSense',
                'Heritrix',
                'Python-urllib',
                'Alexa (IA Archiver)',
                'Ask',
                'Exabot',
                'Custo',
                'OutfoxBot/YodaoBot',
                'yacy',
                'SurveyBot',
                'legs',
                'lwp-trivial',
                'Nutch',
                'StackRambler',
                'The web archive (IA Archiver)',
                'Perl tool',
                'MJ12bot',
                'Netcraft',
                'MSIECrawler',
                'WGet tools',
                'larbin',
                'Fish search',
        );
        foreach($spiderSites as $val) {
            if (stripos($agent, $val) !== false) {
                $this->_server['is_spider'] = true;
                $this->_server['spider_site'] = $val;
                return true;
            }
        }

        $this->_server['is_spider'] = false;
        return false;
    }

    /**
     * 当前访问的链接
     */
    public function url() {

        return (isset($_SERVER['port']) && $_SERVER['port'] == 443 ? 'https' : 'http').'://'.$_SERVER ['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    }

    /**
     * 检查 etag， 是否未修改
     * @param <type> $eTag
     */
    public function checkIfNotModified($eTag) {

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $eTag) {
            return true;
        }
        return false;
    }

    /**
     * 判断是否来自本站的请求：
     * 即当前url和referer的url是否同域
     */
    public function checkReferer() {

        $referer = $this->referer();
        if (!$referer) {
            return false;
        }
        $url = $this->url();
        $refererInfo = parse_url($referer);
        $urlInfo = parse_url($url);
        if ($refererInfo['host'] == $urlInfo['host']) {
            return true;
        }
        return false;
    }
}
