<?php

namespace YniDB;

use YniDB\DBInterface\DBInterface AS DBInterface;
use YniDB\SQLite\SQLite AS SQLite;
use YniDB\MySQL\MySQL;
use YniDB\Exception\SQLException AS SQLException;
use YniDB\DBInterface\DBClass AS DBClass;

/**
 * DBメインクラス<br>
 * SQLiteモードは、SQLiteの定数をMySQLはMySQLの定数を使うようにしてください。<br>
 * Usage<br>
 * MySQL....<br>
 * DBHelper(host,user,password,db);
 * SQLite....
 * DBHelper(file_path);<br>
 * @author yni3
 */
class YniDB extends DBClass implements DBInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-06-08';

    private $db_method;
    private $throw = true;

    /**
     * YniDBを連続作成します(FACTORY)
     * @param type $file_path_or_host
     * @param type $username_or_write_mode
     * @param type $password
     * @param type $dbname
     * @return YniDB\YniDB
     */
    public static function create($file_path_or_host, $username_or_write_mode = NULL, $password = null, $dbname = null) {
        return new self($file_path_or_host, $username_or_write_mode, $password, $dbname);
    }

    /**
     * SQLite接続の作成
     * @param string $file_path
     * @param string $write_mode
     * @return YniDB\YniDB
     */
    public static function createSQLite($file_path, $write_mode = NULL) {
        return new self($file_path, $write_mode);
    }

    /**
     * MySQL接続の作成
     * @param string $host
     * @param string $username
     * @param string $password
     * @param string $dbname
     * @return YniDB\YniDB
     */
    public static function createMySQL($host, $username = NULL, $password = null, $dbname = null) {
        return new self($host, $username, $password, $dbname);
    }

    public function __construct($file_path_or_host, $username_or_write_mode = NULL, $password = null, $dbname = null) {
        parent::__construct();
        try {
            if (!is_null($password) && !is_null($dbname)) {
                //MySQLMode
                $this->db_method = new MySQL($file_path_or_host, $username_or_write_mode, $password, $dbname);
            } elseif (is_null($username_or_write_mode)) {
                //SQLiteMode
                $this->db_method = new SQLite($file_path_or_host);
            } else {
                //SQLiteMode
                $this->db_method = new SQLite($file_path_or_host, $username_or_write_mode, $password);
            }
        } catch (SQLException $e) {
            throw $e;
        }
    }
    
    /**
     * キャッシュ機能を有効にします。<br>
     * 
     */
    public function setCacheMethod() {
        
    }

    public function disableThrowing() {
        $this->throw = false;
    }

    public function beginTransaction() {
        return $this->db_method->beginTransaction();
    }

    public function commit() {
        return $this->db_method->commit();
    }

    public function endTransaction() {
        return $this->db_method->endTransaction();
    }

    public function escape($arg) {
        return $this->db_method->escape($arg);
    }

    public function get($arg, $is_array = false) {
        return $this->db_method->get($arg, $is_array);
    }

    public function getOne($arg, $is_array = false) {
        return $this->db_method->getOne($arg, $is_array);
    }

    public function getTableRecodeNum($arg) {
        return $this->db_method->getTableRecodeNum($arg);
    }

    public function lastInsertedRow() {
        return $this->db_method->lastInsertedRow();
    }
    
    /**
     * 
     * @param YniDB\DBInterface\PreparedInterface $arg
     * @return type
     */
    public function prepare($arg) {
        return $this->db_method->prepare($arg);
    }

    public function rollBack() {
        return $this->db_method->rollBack();
    }

    public function set($arg) {
        return $this->db_method->set($arg);
    }

    public function changeDB($db_name) {
        return $this->db_method->changeDB($db_name);
    }

    public function getAffectedRow() {
        return $this->db_method->getAffectedRow();
    }

    public function __destruct() {
        unset($this->db_method);
    }

}
