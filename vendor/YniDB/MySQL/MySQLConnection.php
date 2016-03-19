<?php

namespace YniDB\MySQL;

use \mysqli AS mysqli;
use YniCore\YniBase AS YniCore;
use YniDB\DBInterface\DBConnectionInterface AS DBConnectionInterface;
use YniDB\Exception\ConnectionException;

/**
 * MySQLのコネクター
 *
 * @author yni3
 */
class MySQLConnection extends YniCore implements DBConnectionInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-10-21';

    private static $instance = array();

    /**
     * このクラスのインスタンスを取得します。
     * @param type $host
     * @param type $db_uname
     * @param type $db_password
     * @param type $db_name
     * @param type $inctance_index (オプションDBセッションを別で開く場合)
     * @return MySQLConnection
     * @throws \Yni\SQL\Adopter\Exception
     */
    final public static function getInctance($host, $db_uname, $db_password, $db_name) {
        $connection_uid = md5($host . $db_uname . $db_password . $db_name);
        if (isset(self::$instance[$connection_uid]) === false) {
            try {
                self::$instance[$connection_uid] = new self($host, $db_uname, $db_password, $db_name);
            } catch (Exception $exc) {
                throw $exc;
            }
        }
        return self::$instance[$connection_uid];
    }

    private $mysqli_object = NULL;

    /**
     * 接続開始
     * @param type $host
     * @param type $db_uname
     * @param type $db_password
     * @param type $db_name
     * @throws ConnectionException
     */
    private function __construct($host, $db_uname, $db_password, $db_name) {
        $this->mysqli_object = new mysqli($host, $db_uname, $db_password, $db_name);
        if ($this->mysqli_object->connect_error) {
            throw new ConnectionException($this->mysqli_object->error);
        }
        $this->mysqli_object->set_charset("utf8");
        $this->mysqli_object->query('SET NAMES UTF8;');
    }

    /**
     * 接続を切断します。
     * @throws ConnectionException
     */
    final public function closeConnection() {
        if (is_null($this->mysqli_object) === false) {
            $this->mysqli_object->commit();
            $this->mysqli_object->autoCommit(false);
            if ($this->mysqli_object->close() === false) {
                throw new ConnectionException();
            } else {
                $this->mysqli_object = NULL;
            }
        }
    }

    /**
     * 
     * @throws ConnectionException
     */
    public function __destruct() {
        try {
            $this->closeConnection();
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function getConnectionId() {
        return $this->getObjectId();
    }

    /**
     * MySQLiコネクションを取得します。
     *
     * @return type
     */
    final public function &getConnection() {
        return $this->mysqli_object;
    }

}

