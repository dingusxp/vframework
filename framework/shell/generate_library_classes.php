<?php
/**
 * 生成 library 自动加载项
 */

$libDir = dirname(dirname(__FILE__)) . '/library';

$list = dirList($libDir, array('type'=>'file', 'exclude'=>'/.*/', 'include'=>'/\.php$/'), 10);
sort($list);
$libraries = array();
foreach($list as $item) {
    $v = str_replace('.php', '', $item);
    $k = str_replace('/', '_', $v);
    $contents = file_get_contents($libDir.'/'.$item);
    if (strpos($contents, 'class ' . $k) !== false) {
        $libraries[$k] = $v;
    }
}
var_export($libraries);

function dirList($dir, $filter = array(), $maxDepth = 0, $prefix = '', $list = array()) {

    $filter['type'] = !empty($filter['type']) && in_array($filter['type'], array('dir', 'file')) ? $filter['type'] : 'both';
    $dir = rtrim($dir, '\\/');
    if(!is_dir($dir)) {
        return $list;
    }
    $dh = opendir($dir);
    $dirs = array();
    while (($file = readdir($dh))) {
        if ($file == '.' || $file == '..') {
            continue ;
        }

        $isDir = is_dir($dir . '/' . $file) ? true : false;
        if ($isDir && $maxDepth > 0) {
            $dirs[] = $file;
        }
        
        // 若黑名单满足，而白名单不满足，跳过
        if (!empty($filter['exclude']) && preg_match($filter['exclude'], $file)
                && (empty($filter['include']) || !preg_match($filter['include'], $file))) {
            continue ;
        }

        if ($filter['type'] == 'both'
                || ($isDir && $filter['type'] == 'dir')
                || (!$isDir && $filter['type'] == 'file')) {
            $list[] = $prefix . $file;
        }
    }
    closedir($dh);
    if ($dirs) {
        foreach ($dirs as $file) {
            $list = dirList($dir . '/' . $file, $filter, $maxDepth - 1, $prefix . $file . '/', $list);
        }
    }
    return $list;
}
