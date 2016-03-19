<?php

namespace YniDB\Core;

use \DateTime;

/**
 * タイマークラス
 *
 * @author yni3
 */
class YniTimer {

    const MILLI = 0.001;
    const MICRO = 0.000001;
    const KILO = 1000;
    const MEGA = 1000000;

    const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';
    //------------------時間取得関係-------------------------

    /**
     * 現在の時刻をunix秒のミリ秒で取得します。
     * ※DateTimeオブジェクトに渡すには、1000で割り「秒」で渡す必要があります。
     * @return double
     */
    public static function getTimeBigint() {
        return self::getTimeMillisec();
    }
    
    /**
     * @deprecated since version number
     * @param type $bigint_timestamp
     * @return type
     */
    public static function getUnixSecondFromTimeBigint($bigint_timestamp) {
        return ceil($bigint_timestamp * self::MILLI);
    }
    
    public static function toUnixSecondFromTimeBigint($bigint_timestamp) {
        return self::getUnixSecondFromTimeBigint($bigint_timestamp);
    }
    
    public static function toBigintTimestampFromUnixTimeStamp($bigint_timestamp){
        return ceil($bigint_timestamp * self::KILO);
    }
    
    public static function toDatetimeStringFromBigintTimestamp($bigint_timestamp){
        $dt = new DateTime();
        $dt->setTimestamp(self::toUnixSecondFromTimeBigint($bigint_timestamp));
        return $dt->format(self::MYSQL_DATETIME_FORMAT);
    }
    
    public static function toBigintTimeStampFromDateTimeString($date_time_string){
        $dt = new DateTime($date_time_string);
        return $dt->getTimestamp() * self::KILO;        
    }

    public static function getTimeSecFloat() {
        return microtime(true); //これで、秒単位の値が出る
    }

    public static function getTimeSec() {
        return ceil(microtime(true));
    }

    public static function getTimeMillisecFloat() {
        return microtime(true) * self::KILO;
    }

    public static function getTimeMillisec() {
        return ceil(microtime(true) * self::KILO);
    }

    public static function getTimeMicrosec() {
        return ceil(microtime(true) * self::MEGA);
    }

    public static function getTimeMicrosecFloat() {
        return (microtime(true) * self::MEGA);
    }

    //------------------スリープ関係-------------------------

    /**
     * 
     * @param type $micro_seconds
     */
    public static function usleep($micro_seconds) {
        usleep($micro_seconds);
    }

    /**
     * 指定したミリ秒スリープします
     * @param type $milli_seconds
     */
    public static function msleep($milli_seconds) {
        usleep($milli_seconds * self::KILO);
    }

    /**
     * 指定した秒スリープします
     * @param type $milli_seconds
     */
    public static function sleep($seconds) {
        usleep($seconds * self::MEGA);
    }

    //------------------タイマー関係-------------------------

    private static $timers = array();

    const NO_TIMER = -1;

    private static $LastTimer = self::NO_TIMER;

    final public static function timerStart() {
        self::$LastTimer++;
        self::$timers[self::$LastTimer] = microtime(true);
        return count(self::$timers);
    }

    final public static function getTimerSecFloat($timer_id = self::NO_TIMER) {
        if ($timer_id === self::NO_TIMER) {
            $timer_id = self::$LastTimer;
        }
        if (isset(self::$timers[$timer_id])) {
            return (microtime(true) - self::$timers[$timer_id]);
        }
        return -1;
    }

    final public static function timerRemove($timer_id = self::NO_TIMER) {
        if ($timer_id === self::NO_TIMER) {
            $timer_id = self::$LastTimer;
        }
        if (isset(self::$timers[$timer_id])) {
            unset(self::$timers[$timer_id]);
            return true;
        }
        return false;
    }

    final public static function timerUpdate($timer_id = self::NO_TIMER) {
        if ($timer_id === self::NO_TIMER) {
            $timer_id = self::$LastTimer;
        }
        if (isset(self::$timers[$timer_id])) {
            self::$timers[$timer_id] = microtime(true);
            return true;
        }
        return false;
    }

    /**
     * 指定されたタイマーから、指定した秒数が経過しているか調べます。<br>
     * $timer_updateを有効にすると、秒数が有効であった場合、インターバル分を引いた数を新しいタイマー値として設定します。
     * @param int $interval_second
     * @param int $timer_id
     * @param boolean $timer_update
     * @return boolean
     */
    final public static function isIntervalSec($interval_second, $timer_id = self::NO_TIMER, $timer_update = true) {
        if ($timer_id === self::NO_TIMER) {
            $timer_id = self::$LastTimer;
        }
        if (self::getTimerSecFloat($timer_id) > ($interval_second)) {
            if ($timer_update === true) {
                self::$timers[$timer_id] = self::$timers[$timer_id] + $interval_second;
            }
            return true;
        } else {
            return false;
        }
    }

    //------------------時間起動型タイマー関係-------------------------

    const TIMEZONE_Tokyo = 'Asia/Tokyo'; //東京
    const TIMEZONE_London = 'Europe/London'; //ロンドン
    const TIMEZONE_Sydney = 'Australia/Sydney'; //シドニー
    const TIMEZONE_HongKong = 'Asia/Hong_Kong'; //香港
    const TIMEZONE_Seoul = 'Asia/Seoul';  //ソウル
    const TIMEZONE_NewYork = 'America/New_York'; //ニューヨーク
    const TIMEZONE_NewZealand = 'Pacific/Auckland'; //ニュージーランド

}

