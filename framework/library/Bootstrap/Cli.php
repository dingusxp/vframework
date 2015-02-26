<?php
/**
 * 命令行模式
 */

class Bootstrap_Cli extends Bootstrap_Abstract {

    /**
     * 运行
     */
    public function run() {

        // 可选，加载 Command 类运行
        if (defined('COMMAND_NAME')) {
            try {
                $command = Command::factory(COMMAND_NAME);
                $argv = Command::getEnv('argv');
                $args = Command::getArgs($argv);
                $params = Command::getParams($args);
                $command->execute($params);
            } catch (Exception $e) {
                echo PHP_EOL, 'Error: ', $e->getMessage(), PHP_EOL;
            }
        }
    }
}