<?php

/**
 * 命令行程序
 */
abstract class Command_Abstract {

    /**
     * 构造函数
     */
    public function  __construct() {

        if (!Command::getEnv('argv')) {
            throw new Command_Exception('This script must be run under console mode', Command_Exception::E_COMMAND_ENVIROMENT_ERROR);
        }
    }

    /**
     * 执行命令
     * @param <type> $params
     */
    public function execute($params) {

        echo 'Params: ', PHP_EOL;
        print_r($params);
        echo PHP_EOL;
    }
}
