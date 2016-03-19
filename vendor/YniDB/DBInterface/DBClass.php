<?php

namespace YniDB\DBInterface;

use \DateTime AS DateTime;
use \DateTimeZone AS DateTimeZone;
use \YniDB\Core\YniBase AS YniCore;
use \YniDB\Core\YniTimer AS YniTimer;

/**
 * YniDB基底クラス
 *
 * @author yni3
 */
abstract class DBClass extends YniCore {

    const AsiaTokyo = 'Asia/Tokyo'; //東京
    const EuropeLondon = 'Europe/London'; //ロンドン
    const AustraliaSydney = 'Australia/Sydney'; //シドニー
    const AsiaHongKong = 'Asia/Hong_Kong'; //香港
    const AsiaSeoul = 'Asia/Seoul';  //ソウル
    const AmericaNewYork = 'America/New_York'; //ニューヨーク
    const NewZealand = 'Pacific/Auckland'; //ニュージーランド

    protected $sql_connection_object;
    protected $dt = NULL;
    protected $millisec;

    public function __construct() {
        $this->setDtZone();
    }

    /**
     * タイムゾーンを設定します。
     * @param type $stZone
     */
    public function setDtZone($stZone = self::AsiaTokyo) {
        if (is_null($this->dt) === true) {
            $this->millisec = YniTimer::getTimeSecFloat();
            $this->dt = new DateTime();
            $this->dt->setTimestamp(ceil($this->millisec));
            $this->dt->setTimeZone(new DateTimeZone($stZone));
        } else {
            $this->dt->setTimeZone(new DateTimeZone($stZone));
        }
    }

    /**
     * 時刻をアップデートします。
     */
    public function updateTime() {
        $this->millisec = YniTimer::getTimeSecFloat();
        $this->dt->setTimestamp(ceil($this->millisec));
    }

    /**
     * Y-m-d H:i:sの形式で現在の年月日時分秒を返します。
     * @param bool $update 時刻を更新するか
     * @return string
     */
    public function getDtString($update = false) {
        if ($update === true) {
            $this->updateTime();
        }
        return $this->dt->format('Y-m-d H:i:s');
    }

    /**
     * 現在の時刻をbigintのミリ秒形式で取得します。
     * @param type $update
     * @return type
     */
    public function getDtMillisecBigint($update = false) {
        if ($update === true) {
            $this->updateTime();
        }
        return ceil($this->millisec * 1000);
    }

    /**
     * ミリ秒をY-m-d H:i:s.u形式の文字列に変換します。
     * @param int $datetime_millisec
     * @param bool $except_milli ミリ秒を含めるか除外するか
     * @return string
     */
    public function convDtMillisecBigintToDateTimeFormat($datetime_millisec, $except_milli = true) {
        $sec = ceil($datetime_millisec / 1000);
        $milli = ($datetime_millisec % 10000);
        $sdt = date('Y-m-d H:i:s', $sec);
        if ($except_milli === true) {
            return $sdt;
        } else {
            return $sdt . ".{$milli}";
        }
    }

}

