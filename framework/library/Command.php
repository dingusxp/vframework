<?php

/**
 * 命令行程序
 */
class Command extends Component {

    /**
     * 环境变量
     * @var <type>
     */
    private static $_env;
    
    /**
     * 工厂
     * @param <type> $engine
     * @param <type> $option
     * @return <type>
     */
    public static function factory($engine, $option = array()) {

        return parent::_factory(__CLASS__, $engine, $option);
    }

    /**
     * 初始化环境变量
     */
    protected static function _initEnv() {

        self::$_env = $_SERVER;
    }

    /**
     * 获取环境变量
     * @param <type> $name
     * @return <type>
     */
    public static function getEnv($name) {

        if (!self::$_env) {
            self::_initEnv();
        }
        return isset(self::$_env[$name]) ? self::$_env[$name] : null;
    }

    /**
     * 读取用户输入
     * @param <type> $prompt
     * @return <type>
     */
    public static function readline($prompt = "\n>>") {

        if (extension_loaded('readline')) {
            $input = readline($prompt);
            readline_add_history($input);
            return $input;
        } else {
            echo $prompt;
            return fgets(STDIN);
        }
    }

    /**
     * 解析参数
     * @param <type> $args
     * @param <type> $stripCommand
     * @return <type>
     */
    public static function getParams($args, $stripCommand = '') {

        $params = array();
        if ($stripCommand && 0 === strpos($args, $stripCommand)) {
            $args = substr($args, strlen($stripCommand));
        }

        $args = trim($args);
        // 将 引号内的字符串中的空格换成 [BLANK]
        $args = preg_replace_callback('/([\'\"])(.*?)\\1/', 'command_strip_blank_and_dash', $args);

        // 匹配 --key=value 和 -name value 模式
        $matches = array();
        if (preg_match_all('/\-\-(\w+)\=(.+?)( |$)/', $args, $matches)) {
            foreach ($matches[1] as $k => $key) {
                $params[$key] = $matches[2][$k];
            }
        }
        if (preg_match_all('/\-(\w+)[ ]+(.+?)( |$)/', $args, $matches)) {
            foreach ($matches[1] as $k => $key) {
                $params[$key] = $matches[2][$k];
            }
        }

        // 回原 空格和短横线
        foreach ($params as $key => $value) {
            $value = str_replace('[BLANK]', ' ', $value);
            $value = str_replace('[DASH]', '-', $value);
            $params[$key] = $value;
        }

        return $params;
    }

    /**
     * 获取输入命令的参数部分
     * @param <type> $argv
     * @param <type> $argc
     * @return <type>
     */
    public static function getArgs($argv, $argc = null) {

        $params = array();
        $paramStart = false;
        if (null == $argc) {
            $argc = count($argv);
        }
        $scriptName = self::getEnv('SCRIPT_NAME');
        for($i = 0; $i < $argc; $i++) {
            if ($paramStart) {
                $params[] = $argv[$i];
            }
            if ($argv[$i] == $scriptName) {
                $paramStart = true;
            }
        }

        return implode(' ', $params);
    }
}


/**
 * 移除空格和短横线
 * @param <type> $match
 * @return <type>
 */
function command_strip_blank_and_dash($match) {

    $str = $match[2];
    $str = str_replace(' ', '[BLANK]', $str);
    $str = str_replace('-', '[DASH]', $str);
    return $str;
}