<?php

/**
 * File System 操作类
 */
class FS {

    /**
     * lsdir 仅列出文件
     */
    const LIST_FILE = 1;

    /**
     * lsdir 仅列出目录
     */
    const LIST_DIRECTORY = 2;

    /**
     * lsdir 列出文件和目录
     */
    const LIST_ALL = 3;

    /**
     * 创建目录（递归）
     */
    public static function mkdir($dir, $mode = 0777) {

        if (is_dir($dir)) {
            return true;
        }
        return mkdir($dir, $mode, true);
    }

    /**
     * 拷贝目录
     * @param <type> $source
     * @param <type> $dest
     */
    public static function cpdir($source, $dest, $filterCallback = null) {

        $list = array();
        $files = self::lsdir($source, FS::LIST_ALL, true, $filterCallback);
        sort($files);
        foreach ($files as $file) {
            $sourcePath = FS::joinPath($source, $file);
            $destPath = FS::joinPath($dest, $file);
            if (is_dir($sourcePath)) {
                FS::mkdir($destPath);
            } else {
                FS::copy($sourcePath, $destPath);
            }
            $list[] = $destPath;
        }

        return $list;
    }

    /**
     * 删除目录（及里面所有文件！）
     * @param string $dir 要删除的目录
     */
    public static function rmdir($dir) {

        self::cleandir($dir);
        return rmdir($dir);
    }

    /**
     * 清空目录下所有文件和文件夹（保留目录本身）
     * @param <type> $dir
     */
    public static function cleandir($dir) {

        $files = self::lsdir($dir);
        foreach ($files as $file) {
            $path = FS::joinPath($dir, $file);
            if (is_dir($path)) {
                $res = self::rmdir($path);
            } else {
                $res = self::remove($path);
            }
            if (!$res) {
                return false;
            }
        }
        return true;
    }

    /**
     * 列出指定目录下所有文件和文件夹
     * @param <type> $path
     */
    public static function lsdir($dir, $type = self::LIST_ALL, $recusive = false, $filterCallback = null, $relativeDir = '') {

        // 参数处理
        if ($filterCallback && !function_exists($filterCallback)) {
            $filterCallback = null;
        }

        // 获取当前文件夹下文件列表
        $list = array();
        $files =  scandir($dir);
        $files = false === $files ? array() : array_diff($files, array('.', '..'));
        foreach ($files as $file) {
            $path = self::joinPath($dir, $file);
            $relativePath = $relativeDir . $file;
            $addToList = false;
            if (is_dir($path)) {
                if ($type & self::LIST_DIRECTORY) {
                    $addToList = true;
                }
                if ($recusive) {
                    $list = array_merge($list, self::lsdir($path, $type, $recusive, $filterCallback, $relativePath . DIRECTORY_SEPARATOR));
                }
            } elseif ($type & self::LIST_FILE) {
                $addToList = true;
            }
            if ($addToList && (!$filterCallback || call_user_func($filterCallback, $path, $relativePath, $file))) {
                $list[] = $relativePath;
            }
        }
        return $list;
    }

    /**
     * 写文件
     * @param <type> $file
     * @param <type> $content
     * @param <type> $append
     */
    public static function write($file, $content, $append = false) {

        self::mkdir(dirname($file));
        if ($append) {
            return file_put_contents($file, $content, FILE_APPEND);
        } else {
            return file_put_contents($file, $content);
        }
    }

    /**
     * 读取文件全部内容
     * @param <type> $file
     */
    public static function read($file) {

        return file_get_contents($file);
    }

    /**
     * 删除文件
     * @param <type> $file
     */
    public static function remove($file) {

        if (file_exists($file)) {
            return unlink($file);
        }
        return true;
    }

    /**
     * 移动文件（或文件夹）
     * @param <type> $source
     * @param <type> $dest
     */
    public static function move($source, $dest) {

        if (file_exists($source)) {
            self::mkdir(dirname($dest));
            return rename($source, $dest);
        }
        return false;
    }

    /**
     * 拷贝文件
     * @param <type> $source
     * @param <type> $dest
     */
    public static function copy($source, $dest) {

        if (file_exists($source)) {
            self::mkdir(dirname($dest));
            return copy($source, $dest);
        }
        return false;
    }

    /**
     * 获取文件扩展名
     * @param <type> $file
     * @return <type>
     */
    public static function getExt($file) {

        return strtolower(trim(substr(strrchr($file, '.'), 1)));
    }

    /**
     * 检查指定文件/目录是否存在
     * @param <type> $file
     * @return <type>
     */
    public static function exist($file) {

        return file_exists($file);
    }

    /**
     * 检查是否文件
     * @param <type> $file
     * @return <type>
     */
    public static function isFile($file) {

        return is_file($file);
    }

    /**
     * 检查是否目录
     * @param <type> $dir
     * @return <type>
     */
    public static function isDir($dir) {

        return is_dir($dir);
    }

    /**
     * 获取到一个独占的空临时目录
     * 请在使用后自行删除，以免产生太多垃圾目录
     * @param <type> $salt
     */
    public static function tmpDir($salt = '') {
        
        $maxTry = 5;
        $tmpPath = V::config('tmp_path');
        $key = $salt . ';' . V_START . ';';
        $prefix = 'd' . date('Ymd') . '_';
        for ($i = 0; $i < $maxTry; $i++) {
            $dirname = substr(md5($key . mt_rand(1, 1000000000)), 8, 16);
            $tmpDir = self::joinPath($tmpPath, $prefix . $dirname);
            if (!self::isDir($tmpDir) && self::mkdir($tmpDir)) {
                return $tmpDir;
            }
        }
        
        throw new FS_Exception('未获取到可用的临时目录', FS_Exception::E_LOGIC_ERROR);
    }

    /**
     * 拼接路径
     */
    public static function joinPath() {

        $pathes = func_get_args();
        $pathes = array_map('fs_remove_rslash', $pathes);
        $pathes = array_filter($pathes);
        return implode(DIRECTORY_SEPARATOR, $pathes);
    }
}

/**
 * 移除路径最右的目录分隔符
 * @param <type> $path
 * @return <type>
 */
function fs_remove_rslash($path) {
    return strlen($path) > 1 ? rtrim($path, '\\/') : $path;
}