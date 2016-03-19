<?php

namespace YniDB\Core;

use \stdClass;
use YniDB\Exception\NotFoundException AS NotFoundException;

/**
 * 配列のヘルパー<br>
 * 
 * @version 1.0
 * @license http://www.yni3.com/license/BSD.txt BSD
 * @author yni3
 */
class YniArrayUtil {

    const VERSION = '1.0';
    const DATE_APPROVED = '2014-06-22';
    
    const DEFAULT_RETURN = NULL;

    /**
     * 配列への安全なアクセスを行います。
     * @param array $array
     * @param int|string $keys... OR array(keys...)
     * @return mixed|false
     */
    public static function getParam(array &$array, $keys) {
        if(!is_array($keys)){
            $keys = func_get_args();
            array_shift($keys);
        }
        return self::dig($array, $keys);
    }

    /**
     * 配列への安全なアクセスを行います。
     * 要素が見つからなかった場合、例外を投げます。
     * @param array $array
     * @param int|string $keys... OR array(keys...)
     * @return mixed
     * @throws NotFoundException
     */
    public static function getParamThrow(array &$array, $keys) {
        if(!is_array($keys)){
            $keys = func_get_args();
            array_shift($keys);
        }
        $return = NULL;
        if (($return = self::dig($array, $keys)) === false) {
            throw new NotFoundException($keys . "is not found");
        } else {
            return $return;
        }
    }

    /**
     * 配列への再帰的なアクセスを提供
     */
    protected static function dig(&$arr, $keys, $default = self::DEFAULT_RETURN) {
        $key = array_shift($keys);
        if (isset($arr[$key])) {
            $val = $arr[$key];
            if (is_array($val) && $keys) {
                return self::dig($val, $keys, $default); // 再帰
            } elseif (is_scalar($val) && $keys) {
                return $default;
            } else {
                return $val;
            }
        } else {
            return $default;
        }
    }

    /**
     * 配列をsedClass形式に変換します。
     * @param array $array
     * @return stdClass|array
     */
    public static function arrayToObject($array) {
        if (is_array($array)) {
            $object = new stdClass();
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $object->{$key} = self::arrayToObject($value);
                } else {
                    $object->{$key} = $value;
                }
            }
            return $object;
        } else {
            return $array;
        }
    }

}

