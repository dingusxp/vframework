<?php
/**
 * 通用控制器基类
 */

class Controller_Base extends Controller_Abstract {

    /**
     * 提示信息类型：信息
     */
    const MESSAGE_INFO = 1;

    /**
     * 提示信息类型：警告
     */
    const MESSAGE_WARNING = 2;

    /**
     * 提示信息类型：错误
     */
    const MESSAGE_ERROR = 3;

    /**
     * 提示信息类型：成功
     */
    const MESSAGE_SUCCESS = 4;

    /**
     * ajax 状态码：成功
     */
    const CODE_SUCCESS = 0;

    /**
     * ajax 状态码：参数错误
     */
    const CODE_PARAM_ERROR = 1;

    /**
     * ajax 状态码：系统错误
     */
    const CODE_SYSTEM_ERROR = 2;

    /**
     * ajax 状态码：需要登录而未登录的状态
     */
    const CODE_NOT_LOGIN = 4;

    /**
     * 提示信息页主模板
     * @var type
     */
    protected $_msgTplMessage = 'message';

    /**
     * 提示信息页布局模板
     * @var type
     */
    protected $_msgTplLayout = 'layout';

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
     * 当前用户
     * @var type 
     */
    protected $_user = array();
    
    /**
     * before action
     */
    protected function _beforeAction() {
        
        if (false == parent::_beforeAction()) {
            return false;
        }

        // 默认模板变量
        $this->_view->assign('staticUrl', V::config('static_url'));
        $this->_view->assign('controller', strtolower($this->_controllerName));
        $this->_view->assign('action', strtolower($this->_actionName));
        return true;
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
     * 信息提示页
     * @param <type> $message
     * @param <type> $msgType
     * @param <type> $urlForwards
     * @param <type> $redirectTime
     * @return <type>
     */
    protected function _showMessage($message = '', $msgType = self::MESSAGE_INFO, $urlForwards = array(), $redirectTime = -1) {

        // 清空内容先
        $this->_response->setBody('');

        // 前往链接
        if (!is_array($urlForwards) && $urlForwards) {
            $urlForwards = array(
                array('url' => $urlForwards, 'text' => '前进')
            );
        }
        if (!$urlForwards) {
            $urlForwards = array(
                array('url' => urldecode($this->_request->referer()), 'text' => '返回')
            );
        }
        $redirectUrl = $urlForwards[0]['url'];
        if (0 == $redirectTime && !empty($redirectUrl)) {
            return $this->_response->redirect($urlForwards[0]['url']);
        }

        // 提示框样式
        $msgTypes = array(
            self::MESSAGE_INFO => 'info',
            self::MESSAGE_WARNING => 'warning',
            self::MESSAGE_ERROR => 'error',
            self::MESSAGE_SUCCESS => 'success',
        );
        if (!array_key_exists($msgType, $msgTypes)) {
            $msgType = self::MESSAGE_INFO;
        }

        $this->_view->assign('title', '提示信息');
        $this->_view->assign('message', $message);
        $this->_view->assign('messageType', $msgTypes[$msgType]);
        $this->_view->assign('urlForwards', $urlForwards);
        $this->_view->assign('redirectUrl', $redirectUrl);
        $this->_view->assign('redirectTime', $redirectTime > 0 ? intval($redirectTime) : 0);
        return $this->_renderLayout($this->_msgTplMessage, array(), $this->_msgTplLayout);
    }
    
    /**
     * 渲染到模板框架
     * @param type $tpl
     * @param type $tplVars
     */
    protected function _renderLayout($tpl, $data = array(), $layoutTpl = 'layout') {

        // 参数指定变量
        if ($data) {
            foreach ($data as $key => $value) {
                $this->_view->assign($key, $value);
            }
        }

        $content = $this->_view->render($tpl, View::RENDER_NONE);

        // 抽取代码中的样式和js内容
        $extraScript = $extraStyle = '';
        $matches = array();
        if (preg_match_all('/\<link rel\=\"stylesheet\".*?\/\>\s*/i', $content, $matches)) {
            foreach ($matches[0] as $value) {
                $extraStyle .= $value;
                $content = str_replace($value, '', $content);
            }
        }
        if (preg_match_all('/\<style type\=\"text\/css\".*?\<\/style\>\s*/ims', $content, $matches)) {
            foreach ($matches[0] as $value) {
                $extraStyle .= $value;
                $content = str_replace($value, '', $content);
            }
        }
        if (preg_match_all('/\<script type\=\"text\/javascript\" src\=\".*?\<\/script\>\s*/i', $content, $matches)) {
            foreach ($matches[0] as $value) {
                $extraScript .= $value;
                $content = str_replace($value, '', $content);
            }
        }
        if (preg_match_all('/\<script type\=\"text\/javascript"\>.*?\<\/script>\s*/ims', $content, $matches)) {
            foreach ($matches[0] as $value) {
                $extraScript .= $value;
                $content = str_replace($value, '', $content);
            }
        }
        
        // 去除多余的 script 和 style 标签
        $content = preg_replace('/\<\/script>\s*\<script/', '', $content);
        $content = preg_replace('/\<\/style\>\s*\<style/', '', $content);

        $this->_view->assign('mainContent', $content);
        $this->_view->assign('extraStyle', $extraStyle);
        $this->_view->assign('extraScript', $extraScript);
        return $this->_view->render($layoutTpl);
    }

    /**
     * ajax 返回
     * @param <type> $code
     * @param <type> $message
     * @param <type> $result
     * @param <type> $jsonp
     * @return <type>
     */
    protected function _ajaxMessage($code, $message, $result = '', $jsonp = false) {

        $return = Str::jsonEncode(array(
            'code' => intval($code),
            'message' => $message,
            'result' => $result,
        ));

        if ($jsonp) {
            // [TODO] check referer ?

            $callback = $this->_request->get(is_string($jsonp) ? $jsonp : 'callback', '');
            if (!preg_match('/^jsonp\d{10,15}$/', $callback)) {
                $callback = '';
            }
            $return = $callback . '(' . $return . ')';
        }

        // 不缓存
        $this->_response->setNoCache();
        return $this->_response->setBody($return);
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

        $saltName = V::config('security.csrf_salt', 'v_salt');
        $token = V::config('security.csrf_token', 'v_token');
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
}