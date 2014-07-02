<?php
/**
 * 优化器：
 * 把 相关初始化 类库都打包到一个文件中，减少文件读取
 * 
 */

include dirname(__FILE__).'/V.php';
V::loadBootstrap($loader, $config)->run();

$bootstrapLibs = array(
    'Runtime' => array(
    ),
    'Web' => array(
        'Router.php',
        'Router/Abstract.php',
        'Router/Exception.php',
        'Router/Simple.php',
        'Router/Regexp.php',
        'Controller.php',
        'Controller/Abstract.php',
        'Controller/Exception.php',
        'Web/Exception.php',
        'Web/Request.php',
        'Web/Response.php',
        'Web/Cookie.php',
        'Web/Session.php',
        'View.php',
        'View/Abstract.php',
        'View/Exception.php',
        'View/PHP.php',
    ),
);

$commonLibs = array(
    'Component.php',
    'Component/Abstract.php',
    'Component/Exception.php',
    'Str.php',
    'Arr.php',
    'FS.php',
    'Debugger.php',
    'Logger.php',
);

$bootstrap = ucfirst($loader);
define('LIB_PATH', V_PATH.'/library');
$files = array();
$files[] = V_PATH.'/V.php';
$files[] = LIB_PATH.'/Bootstrap.php';
$files[] = LIB_PATH.'/Bootstrap/Abstract.php';
$files[] = LIB_PATH.'/Bootstrap/Exception.php';
$files[] = LIB_PATH.'/Bootstrap/'.$bootstrap.'.php';
foreach ($commonLibs as $lib) {
    $files[] = LIB_PATH.'/'.$lib;
}
if (!empty($bootstrapLibs[$bootstrap])) {
    foreach ($bootstrapLibs[$bootstrap] as $lib) {
        $files[] = LIB_PATH.'/'.$lib;
    }
}
if ($extraLib) {
    foreach ($extraLib as $file) {
        $files[] = $file;
    }
}
$files = array_unique($files);

$content = '<?php'.PHP_EOL;
$content .= "define('V_PATH', '".V_PATH."');\n";
foreach ($files as $file) {
    if (!is_file($file)) {
        echo 'Warning: bad lib file: ', $file, PHP_EOL;
        continue;
    }
    $content .= phpcompress($file);
    $content .= PHP_EOL;
}

$content = str_replace('V::init();', '', $content);
$config = V::config('*');
$config = var_export($config, true);
$config = preg_replace('/[\s]+/', ' ', $config);
$content .= '$config = '.$config.';'.PHP_EOL;
$content .= 'V::compileInit($config);';
$content .= 'unset($config);';
$content .= "V::loadBootstrap('{$loader}')->run();";

file_put_contents($compileFile, $content);

function phpcompress($file) {
	$content = file_get_contents($file);
	$content = preg_replace('/(^|\s+)<\?php(\s+|$)/i', "\n", $content);
	$content = preg_replace('/(^|\s+)\?\>(\s+|$)/', "\n", $content);
	$content = preg_replace("!//(\\*| )(.*?)(\n|$)!", "\n", $content);
	$content = preg_replace("!(^|[\;\}])\s*//(.*?)(\n|$)!", "\n", $content);
	$content = preg_replace('/[\s]+/', ' ', $content);
	$content = preg_replace('!/\*\*(.*?)(\s+|\*)\*/!', '', $content);
	$content = preg_replace('/[ ][ ]+/', ' ', $content);
	return trim($content);
}