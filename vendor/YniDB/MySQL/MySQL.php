<?php

namespace YniDB\MySQL;

use YniDB\DBInterface\DBClass AS DBClass;
use YniDB\DBInterface\DBInterface AS DBInterface;
use YniDB\Exception\SQLException AS SQLException;
use YniDB\Exception\ConnectionException AS ConnectionException;
use YniDB\Exception\QueryException AS QueryException;
use YniDB\MySQL\MySQLConnection AS MySQLConnection;
use YniDB\MySQL\MySQLPrepared AS MySQLPrepared;
use YniCore\YniArrayUtil AS ArrayUtil;
use YniDB\Common\StmtCacher AS StmtCacher;

/**
 * MySQL
 *
 * @author yni3
 */
class MySQL extends DBClass implements DBInterface {

    const VERSION = '0.3';
    const DATE_APPROVED = '2013-12-05';

    protected $db_host = '';
    protected $db_uname = '';
    protected $db_pass = '';
    protected $db_dbname = '';
    protected $connection_id = NULL;

    public function __construct($db_host, $db_uname, $db_pass, $db_dbname) {
        parent::__construct();
        try {
            $this->sql_connection_object = &MySQLConnection::getInctance($db_host, $db_uname, $db_pass, $db_dbname)->getConnection();
        } catch (ConnectionException $e) {
            throw $e;
        }
        $this->db_host = $db_host;
        $this->db_uname = $db_uname;
        $this->db_pass = $db_pass;
        $this->db_dbname = $db_dbname;
        $this->connection_id = MySQLConnection::getInctance($db_host, $db_uname, $db_pass, $db_dbname)->getConnectionId();
        $this->beginTransaction();
    }

    public function __destruct() {
        if($this->sql_connection_object){
            $this->commit();
            $this->endTransaction();
        }
    }

    public function beginTransaction() {
        $this->sql_connection_object->autoCommit(true);
    }

    public function commit() {
        $this->sql_connection_object->commit();
    }

    public function endTransaction() {
        $this->sql_connection_object->autoCommit(false);
    }

    public function get($query, $is_array = false) {
        $result = $this->sql_connection_object->query($arg);
        if (is_object($result) === true) {
            $r = $result->fetch_all(MYSQLI_ASSOC);
            //メモリ解放
            $result->free();
        } else {
            throw new QuerySyntaxException($this->sql_connection_object->error, $query);
        }
        if (empty($r) === true) {
            return false;
        }
        if ($is_array === true) {
            return $r;
        } else {
            return ArrayUtil::arrayToObject($r);
        }
    }

    public function getOne($query, $is_array = false) {
        $result = $this->sql_connection_object->query($query);
        if (is_object($result) === true) {
            $r = $result->fetch_array(MYSQLI_ASSOC);
            //メモリ解放
            $result->free();
        } else {
            throw new QueryException($this->sql_connection_object->error, $query);
        }
        if (empty($r) === true) {
            return false;
        }
        //要素数が、2以下なら、そのキーの値そのものを返す。
        if (count($r) < 2) {
            $r = array_shift($r);
        }
        if ($is_array === true) {
            return $r;
        } else {
            return ArrayUtil::arrayToObject($r);
        }
    }

    public function rollBack() {
        $this->sql_connection_object->rollback();
    }

    public function set($query) {
        $result = $this->sql_connection_object->query($query);
        if ($result !== true) {
            throw new QueryException($this->sql_connection_object->error, $query);
            return false;
        }
        return true;
    }

    public function escape($string) {
        if (is_null($string)) {
            return "";
        }
        if (is_array($string)) {
            foreach ($string as &$value) {
                if (is_array($value)) {
                    $value = $this->escape($value);
                } else {
                    $value = $this->sql_connection_object->real_escape_string((string) $value);
                }
            }
            return $string;
        } else {
            return $this->sql_connection_object->real_escape_string((string) $string);
        }
    }

    public function lastInsertedRow() {
        return $this->sql_connection_object->insert_id;
    }

    public function getTableRecodeNum($table_name) {
        $query = "SELECT COUNT(*) FROM {$table_name};";
        return $this->sql_connection_object->querySingle($query);
    }

    public function prepare($query) {
        if(($stmt = StmtCacher::getIncetance()->getStmt($this->connection_id, $query)) !== false){
            return $stmt;
        }
        $p = $this->sql_connection_object->prepare($query);
        if ($p !== false) {
            $return = new MySQLPrepared($query, $p, $this->connection_id);
            StmtCacher::getIncetance()->setStmt($return);
            return $return;
        } else {
            throw new QueryException($this->sql_connection_object->error, $query);
        }
    }

    public function changeDB($db_name) {
        $result = $this->sql_connection_object->select_db($db_name);
        if ($result !== true) {
            throw new SQLException($this->sql_connection_object->error);
            return false;
        }
        return true;
    }

    public function getAffectedRow() {
        return $this->sql_connection_object->affected_rows;
    }

}

