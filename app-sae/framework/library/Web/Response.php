<?php

/**
 * web 输出
 */
class Web_Response {

    /**
     * 头信息
     * @var <type>
     */
    private $_header = array();

    /**
     * 内容
     * @var <type>
     */
    private $_body = '';

    /**
     * http code
     * @var <type>
     */
    private $_responseCode = 200;

    /**
     * rewrite 回调函数
     * @var <type>
     */
    private $_rewriteCallback = array();

    /**
     * 是否输出 ob 里缓存的内容
     * @var <type>
     */
    public $_outputObContent = false;

    /**
     * 是否已输出过
     * @var <type>
     */
    public $_outputted = false;

    /**
     * 是否开启 gzip 压缩
     * @var <type>
     */
    public $_outputGzip = true;

    /**
     * 实例
     * @var Web_Request
     */
    private static $_instance = null;

    /**
     * 获取实例
     * @return <type>
     */
    public static function getInstance() {

        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 构造函数
     */
    private function  __construct() {

        // ob_start
        ob_start();

        // 注册回调函数
        $config = V::config('web.response');
        if (!empty($config['rewrite_callbacks'])) {
            $callbacks = (array)$config['rewrite_callbacks'];
            foreach ($callbacks as $callback) {
                $this->registerRewriteCallback($callback);
            }
        }

        // 是否开启 gzip
        $this->setOuputGZip(!empty($config['enable_gzip']) ? true : false);

        // 是否显示程序中直接输出的内容
        $this->setOutputObContent(!empty($config['output_ob_content']) ? true : false);
    }

    /**
     * 设置是否要输出 ob 缓冲区中的内容
     * 是的话会追加缓存区中内容；否则只输出 setBody 里设置的内容
     * @param <type> $isOutput
     */
    public function setOutputObContent($isOutput) {

        $this->_outputObContent = $isOutput ? true : false;
    }

    /**
     * 是否开启 gzip 输出
     * @param <type> $isEnableGzip
     */
    public function setOuputGZip($isEnableGzip) {

        $this->_outputGzip = $isEnableGzip ? true : false;
    }

    /**
     * 设置 header
     * @param <type> $content
     * @param <type> $replace
     */
    public function setRawHeader($content, $replace = true) {

        $this->_header[] = array($content, $replace);
    }

    /**
     * 设置 header
     * @param <type> $type
     * @param <type> $content
     * @param <type> $replace
     */
    public function setHeader($type, $content, $replace = true) {

        $type = strtolower($type);
        if ($replace) {
            $this->_header[$type] = array();
        }

        $this->_header[$type][] = $content;
    }

    /**
     * 清除指定 header 或全部 header
     * @param <type> $type
     */
    public function clearHeader($type = '') {

        if ($type) {
            $this->_header[$type] = array();
        } else {
            $this->_header = array();
        }
    }

    /**
     * 设置 http 头
     * @param <type> $code
     */
    public function setResponseCode($code) {

        if (!is_int($code) || (100 > $code) || (599 < $code)) {
            throw new Web_Exception('Bad http response code ' . $code);
        }

        $this->_responseCode = $code;
    }

    /**
     * 设置内容
     * @param <type> $content
     */
    public function setBody($content) {

        $this->_body = $content;
    }

    /**
     * 添加内容到 body
     * @param <type> $content
     */
    public function appendBody($content) {

        $this->_body .= $content;
    }

    /**
     * 注册一个 rewrite 函数，在输出 ouput 时重写内容
     * @param <type> $callback
     */
    public function registerRewriteCallback($callback) {

        if (is_string($callback) || (is_array($callback) && count($callback) == 2)) {
            $this->_rewriteCallback[] = $callback;
        }
    }

    /**
     * 取消一个或全部注册的 rewrite 函数
     * @param <type> $callback
     */
    public function removeRewriteCallback($callback = '') {

        // 清除全部
        if (!$callback) {
            $this->_rewriteCallback = array();
            return;
        }

        // 清除指定
        $callbacks = $this->_rewriteCallback;
        $this->_rewriteCallback = array();
        foreach ($callbacks as $registeredCallback) {
            if ($callback != $registeredCallback) {
                $this->_rewriteCallback[] = $registeredCallback;
            }
        }
    }

    /**
     * 取消输出
     * 通常是需要输出 文件，图像等内容
     */
    public function cancelOutput() {

        // 清除内容
        ob_end_clean();
        $this->_outputted = true;
    }

    /**
     * 输出内容
     * @param <type> $ob
     * @return <type>
     */
    public function output($ob = true) {

        if ($this->_outputted) {
            return;
        }
        $this->_outputted = true;

        // 追加 ob 中捕获的内容
        if ($this->_outputObContent) {
            $content = ob_get_contents();
            $this->setBody($content . $this->_body);
        }

        // rewrite
        if ($this->_rewriteCallback) {
            foreach ($this->_rewriteCallback as $callback) {
                $this->_body = call_user_func($callback, $this->_body);
            }
        }

        // 输出 header 和 内容
        ob_end_clean();
        if ($ob) {
            $this->_outputOb();
        }
        foreach ($this->_header as $type => $values) {
            if (is_int($type)) {
                header($values[0], $values[1]);
            } elseif($values) {
                for ($i = 0, $L = count($values); $i < $L; $i++) {
                    header("$type: $values[$i]", $i == 0);
                }
            }
        }

        // HTTP Response Code
        $responseCodeDescription = array(
            '301' => 'Moved Permanently',
            '302' => 'Moved Temporarily',
            '304' => 'Not Modified',
            '400' => 'Bad Request',
            '403' => 'Forbidden',
            '404' => 'Not Found',
            '500' => 'Internal Server Error',
        );
        $protocol = Web_Request::getInstance()->server('SERVER_PROTOCOL');
        if (!$protocol) {
            $protocol = 'HTTP/1.1';
        }
        $responseString = $protocol . ' ' . $this->_responseCode;
        if (isset($responseCodeDescription[$this->_responseCode])) {
            $responseString .= ' ' . $responseCodeDescription[$this->_responseCode];
        }
        header($responseString);

        // 重定向， 清空 body
        if ($this->_responseCode >= 300 && $this->_responseCode <= 307) {
            $this->_body = '';
        }

        echo $this->_body;
    }

    /**
     * 输出 ob
     */
    private function _outputOb() {

        if (!$this->_outputGzip) {
            ob_start();
            return;
        }

        // 检查浏览器是否支持
        $encoding = Web_Request::getInstance()->server('HTTP_ACCEPT_ENCODING');
        if ($encoding && Str::has($encoding, 'gzip', true)) {
            ob_start('ob_gzhandler');
        } else {
            ob_start();
        }
    }

    /**
     * 禁止浏览器缓存
     */
    public function setNoCache() {

        $this->setHeader('Expires', gmdate("D, d M Y H:i:s", V::timestamp()) . ' GMT');
        $this->setHeader('Last-Modified', gmdate("D, d M Y H:i:s", V::timestamp()) . ' GMT');
        $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->setHeader('Cache-Control', 'post-check=0, pre-check=0', false);
        $this->setHeader('Pragma', 'no-cache');
    }

    /**
     * 重定向
     * @param <type> $url
     * @param <type> $isPermanent
     */
    public function redirect($url, $isPermanent = false) {

        $callback = V::config('web.response.redirect_rewrite');
        if ($callback) {
            $url = call_user_func($callback, $url);
        }

        if ($isPermanent) {
            $this->setResponseCode(301);
        } else {
            $this->setResponseCode(302);
        }
        $this->setHeader('Location', $url);
        $this->setHeader('Connection', 'close');
    }

    /**
     * 返回 304， 未修改；
     * 通常和 setEtag， Request 的 checkIfNotModifed 联合使用
     */
    public function returnNotModifed() {

        $this->setResponseCode(304);
        return $this->setBody('');
    }

    /**
     * 设置 etag， 以便比较内容是否修改了
     */
    public function setEtag($eTag) {

        $this->setHeader('Etag', $eTag);
    }

    /**
     * 设置 cookie
     * @param <type> $key
     * @param <type> $value
     * @param <type> $expired
     * @param <type> $path
     * @param <type> $domain
     */
    public function setCookie($key, $value, $expired = 0, $path = null, $domain = null) {

        Web_Cookie::getInstance()->set($key, $value, $expired, $path, $domain);
    }

    /**
     * 清除指定或全部 cookie
     * @param <type> $keys
     */
    public function clearCookie($keys = '') {

        if (!$keys) {
            $keys = array_keys(Web_Cookie::getInstance()->getx());
        }

        $cookie = Web_Cookie::getInstance();
        foreach ((array)$keys as $key) {
            $cookie->set($key, '', -864000);
        }
    }
}