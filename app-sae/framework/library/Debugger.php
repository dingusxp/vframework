<?php

/**
 * 调试器
 */
class Debugger {
    
    /**
     * 格式化输出的字符串最大长度
     */
    const FORMAT_STRING_MAX_LENGTH = 255;

    /**
     * 输出变量模式：var_export 函数打印
     */
    const OUTPUT_VAR_EXPORT = 1;

    /**
     * 输出变量模式：print_r 函数打印
     */
    const OUTPUT_PRINT_R = 2;

    /**
     * 输出变量模式：var_dump 函数打印
     */
    const OUTPUT_VAR_DUMP = 3;

    /**
     * 输出变量
     * @param <type> $var
     */
    public static function output($var, $terminate = false, $mode = self::OUTPUT_PRINT_R) {

        // 输出
        switch ($mode) {
            case self::OUTPUT_PRINT_R:
                print_r($var);
                break;
            case self::OUTPUT_VAR_DUMP:
                var_dump($var);
                break;
            default:
                var_export($var);
                break;
        }

        // 终止
        if ($terminate) {
            exit;
        }
    }

    /**
     * 获取当前位置的调用堆栈信息
     */
    public static function debugTrace() {

        $output = '';
        $backtrace = debug_backtrace();
        foreach ($backtrace as $bt) {
            $args = '';
            foreach ($bt['args'] as $a) {
                if (!empty($args)) {
                    $args .= ', ';
                }
                $args .= self::_formatVar($a);
            }
            $output .= "{$bt['file']} #{$bt['line']}: {$bt['class']}{$bt['type']}{$bt['function']}($args)<br />\n";
        }
        return $output;
    }
    
    /**
     * 格式化一个变量
     * @param type $var
     */
    private static function _formatVar($var) {
        
        $var2string = '';
        switch (gettype($var)) {
            case 'integer':
            case 'double':
                $var2string = $var;
                break;
            case 'string':
                $var = substr($var, 0, self::FORMAT_STRING_MAX_LENGTH) . ((strlen($var) > self::FORMAT_STRING_MAX_LENGTH) ? '...' : '');
                $var2string = "\"$var\"";
                break;
            case 'array':
                $var2string = 'Array(' . count($var) . ')';
                break;
            case 'object':
                $var2string = 'Object(' . get_class($var) . ')';
                break;
            case 'resource':
                $var2string = 'Resource(' . strstr($var, '#') . ')';
                break;
            case 'boolean':
                $var2string = $var ? 'True' : 'False';
                break;
            case 'NULL':
                $var2string = 'Null';
                break;
            default:
                $var2string = 'Unknown';
        }
        return $var2string;
    }
}