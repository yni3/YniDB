<?php

namespace YniDB\Util;

/**
 * ディレクトリ用クラス
 *
 * @author yni3
 */
class DirectoryUtil {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-06-06';

    /**
     * 書き込み可能か検査
     */
    public static function checkIsWritableDirectory($path) {
        if (file_exists($path)) { //ディレクトリが存在するか？
            if (is_dir($path)) { //ディレクトリか？
                if (is_writable($path)) { //書き込み可能か
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            if(empty($path) === true){
                return self::checkIsWritableDirectory(".");
            }else if($path === "."){
                return false;
            }
            return self::checkIsWritableDirectory(mb_substr(($path),0, mb_strrpos($path, DIRECTORY_SEPARATOR)));
        }
    }

    public static function mkdir($path) {
        if (file_exists($path)){
            return true;
        }
        if (strpos($path, DIRECTORY_SEPARATOR) && !file_exists(dirname($path))) {
            if (self::mkdir(dirname($path)) === false)
                return false;
        }
        return mkdir($path);
    }

}

