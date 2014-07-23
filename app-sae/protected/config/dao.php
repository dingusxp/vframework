<?php
/**
 * DAO 相关配置，
 * 每个单独的 dao 配置，可以直接添加 key 配置，也可在 dao 文件夹下以 key 为文件名配置
 * 如： 为 test 增加配置
 * 可以直接增加
 * test => array('table' => 'test', 'pk' => 'tid')
 * 也可建立 dao/test.php ，内容为
 * return array('table' => 'test', 'pk' => 'tid')
 */

return array(

    /**
     * 通用 db 配置，可以在每个 dao 中单独制定
     
    'db' => array(
        'engine' => 'pdo',
        'option' => array(
            'dsn' => 'mysql:host=localhost;dbname=vframework',
            'user' => 'root',
            'password' => '1',
        ),
    ),*/
    
    /**
     * sae
     */
    'db' => V::config('db'),
);