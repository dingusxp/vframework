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
        return $this->_view->renderLayout($this->_msgTplMessage, $this->_msgTplLayout, array(), View::LAYOUT_REBUILD_RESOURCE);
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
}