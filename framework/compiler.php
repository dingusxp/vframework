<?php

/*****************************************
 *         试验性质！！！ 请慎用！！！        *
 *****************************************/

/**
 * 编译 bootstrap 并运行 
 * @param type $compileFile
 * @param type $boostrap
 * @param type $config
 * @return type
 */
function compile_and_run($compileFile, $loader, $config = null, $extraLib = array()) {

    if (is_file($compileFile)) {
        require $compileFile;
        return;
    }

    include dirname(__FILE__).'/_compile.php';
}
