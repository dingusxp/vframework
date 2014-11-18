<?php

/**
 * 控制器抽象类
 */
abstract class Controller_Abstract {

    /**
     * 模板引擎
     * @var <type>
     */
    protected $_view;

    /**
     * request 实例
     * @var <type>
     */
    protected $_request;

    /**
     * response 实例
     * @var <type>
     */
    protected $_response;

    /**
     * session 实例
     * @var <type>
     */
    protected $_session;

    /**
     * 实例类的名称
     * @var <type>
     */
    protected $_controllerName;

    /**
     * 执行的 action
     * @var <type>
     */
    public $_actionName;
    
    /**
     * csrf salt cookie name 
     * @var type 
     */
    protected static $_csrfSaltName = 'v_salt';
    
    /**
     *
     * @var type csrf 加密密钥
     */
    protected static $_csrfToken = 'v_token';

    /**
     * 验证码 默认长度
     */
    const CAPTCHA_DEFAULT_LENGTH = 4;

    /**
     * 验证码默认图片宽度
     */
    const CAPTCHA_DEFAULT_WIDTH = 160;

    /**
     * 验证码默认图片高度
     */
    const CAPTCHA_DEFAULT_HEIGHT = 64;

    /**
     * 验证码 cookie 名字
     * @var type
     */
    protected $_captchaName = 'captcha';

    /**
     * 构造函数
     */
    public function  __construct() {

        // 判断是否 Module
        $class = get_class($this);
        $this->_controllerName = substr($class, 11);// 11 is the length of "Controller[_\]"

        $this->_request = Web_Request::getInstance();
        $this->_response = Web_Response::getInstance();
        $this->_session = Web_Session::getInstance();

        // view
        $config = V::config('web.view');
        if (!$config) {
            $config = V::config('view');
        }
        $this->_view = View::factory($config);
    }

    /**
     * 执行动作前调用，若返回 false，则终止动作
     * @return <type>
     */
    protected function _beforeAction() {

        return true;
    }

    /**
     * 执行指定动作
     * @param <type> $action
     */
    public function doAction($action) {

        $this->_actionName = $action;
        if ($this->_beforeAction()) {
            $method = 'action' . $action;
            $this->$method();
            $this->_afterAction();
        }

        $this->_response->output();
    }

    /**
     * 执行动作后的调用
     */
    protected function _afterAction() {

    }

    /**
     * 检查跳转链接是否是同域名的
     */
    protected function _checkJumpUrl($jumpUrl) {

        $jumpUrl = trim($jumpUrl);
        if ($jumpUrl[0] == '/') {
            return true;
        }

        $jumpUrlInfo = parse_url($jumpUrl);
        $loginUrlInfo = parse_url($this->_request->url());
        return $jumpUrlInfo['host'] === $loginUrlInfo['host'] ? true : false;
    }

    /**
     * 获取一个 token
     * 通常在需要操作的表单/链接里传给客户端
     */
    protected function _getCsrfToken($id = null) {

        $saltName = self::$_csrfSaltName;
        $token = self::$_csrfToken;
		$hash = $this->_request->cookie($saltName);
		if (!$hash) {
			$hash = mt_rand(10000000, 99999999);
			$this->_response->setCookie($saltName, $hash, 86400 * 14);
		}
		return substr(md5($id.$hash.$token), 8, 8);
    }

    /**
     * 验证客户端提交的 token 是否合法
     */
    protected function _checkCsrfToken($token, $id = null) {

		return $token == $this->_getCsrfToken($id);
    }

    /**
     * 输出验证码
     */
    protected function _renderCaptcha($length = self::CAPTCHA_DEFAULT_LENGTH, $width = self::CAPTCHA_DEFAULT_WIDTH, $height = self::CAPTCHA_DEFAULT_HEIGHT) {

        try{
            $code = Image::generateCaptchaCode($length);
			$img = Image::factory(V::config('image'));
            $img->createCaptcha($code, $width, $height);
            $captchaCode = $this->_getAuthCryptor()->encrypt($code);
            $this->_response->setCookie($this->_captchaName, $captchaCode);
            return $img->output();
		} catch (Exception $e) {
            V::log($e, Logger::LEVEL_ERROR);
			return false;
		}
    }

    /**
     * 校验验证码
     * @param type $captcha
     * @return type
     */
    protected function _checkCaptcha($captcha) {

        $captchaCode = $this->_request->cookie($this->_captchaName);
        $code = $this->_getAuthCryptor()->decrypt($captchaCode);
        $res = (strtolower($captcha) == strtolower($code)) ? true : false;
        $this->_response->clearCookie($this->_captchaName);
        return $res;
    }

    /**
     * 获取 auth 信息加密解密对象
     */
    protected function _getAuthCryptor() {
        static $_cryptor = null;

        // 初始化加密器
        if (null === $_cryptor) {
            $config = V::config('cryptor');
            $_cryptor = Cryptor::factory($config);
        }

        return $_cryptor;
    }
}