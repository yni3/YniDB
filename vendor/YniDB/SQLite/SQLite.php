<?php

namespace YniDB\SQLite;

use YniDB\DBInterface\DBInterface AS DBInterface;
use YniDB\DBInterface\DBClass AS DBClass;
use YniDB\SQLite\SQLiteConnection AS SQLiteConnection;
use YniDB\SQLite\SQLitePrepared AS SQLitePrepared;
use YniDB\Exception\ConnectionException AS ConnectionException;
use YniDB\Exception\QueryException AS QueryException;
use YniDB\DBInterface\InitializementCallBack AS InitializementCallBack;
use YniCore\YniArrayUtil AS ArrayUtil;
use YniDB\Common\StmtCacher AS StmtCacher;

/**
 * SQLite
 *
 * @author yni3
 */
class SQLite extends DBClass implements DBInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-06-11';
    //
    const ReadWrite = 'SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE';
    const ReadOnly = 'SQLITE3_OPEN_READONLY';
    const TRANSACTION_DEFERRED = 'DEFERRED';
    const TRANSACTION_IMMEDIATE = 'IMMEDIATE';
    const TRANSACTION_EXCLUSIVE = 'EXCLUSIVE';
    /**
     * メモリデータベースを使用します。
     * PHPスクリプト実行後にこのメモリは解放されます。
     */
    const SQLITE_MEMORY_MODE = ':memory:';

    protected $id;
    protected $file_path;
    protected $write_mode;
    protected $is_transaction = false;
    protected $conn_id = NULL;

    public function __construct($file_path, $write_mode = self::ReadWrite, InitializementCallBack $callBack = NULL) {
        try {
            $this->sql_connection_object = &SQLiteConnection::getInctance($file_path, $write_mode, $callBack)->getConnection();
        } catch (ConnectionException $e) {
            throw $e;
        }
        $this->file_path = $file_path;
        $this->write_mode = $write_mode;
        $this->conn_id = SQLiteConnection::getInctance($file_path, $write_mode, $callBack)->getConnectionId();
        $this->id = md5(microtime() . getmypid());
    }

    public function __destruct() {
        $this->commit();
        $this->endTransaction();
    }

    public function beginTransaction($type = self::TRANSACTION_DEFERRED) {
        if ($this->sql_connection_object->exec("BEGIN {$type};") === false) {
            throw new QueryException($this->sql_connection_object->lastErrorMsg(), "BEGIN {$type};");
        }
        $this->is_transaction = true;
    }

    public function commit() {
        if ($this->is_transaction === true) {
            if (@$this->sql_connection_object->exec("COMMIT;") === false) {
                throw new QueryException($this->sql_connection_object->lastErrorMsg(), "COMMIT;");
            }
        }        
    }

    public function rollBack() {
        if ($this->is_transaction === true) {
            if (@$this->sql_connection_object->exec("ROLLBACK;") === false) {
                throw new QueryException($this->sql_connection_object->lastErrorMsg(), "ROLLBACK;");
            }
        }
        //COMMIT か ROLLBACK が行われると、トランザクションは閉じてしまうので、再度開く。
        try {
            $this->beginTransaction();
        } catch (QueryException $e) {
            throw $e;
        }
    }

    public function endTransaction() {
        if ($this->is_transaction === true) {
            if (@$this->sql_connection_object->exec("END;") === false) {
                throw new QueryException($this->sql_connection_object->lastErrorMsg(), "END;");
            }
            $this->is_transaction = false;
        }
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
                    $value = $this->sql_connection_object->escapeString((string) $value);
                }
            }
            return $string;
        } else {
            return $this->sql_connection_object->escapeString((string) $string);
        }
    }

    public function get($query, $is_array = false) {
        if (($result = @$this->sql_connection_object->query($query)) === false) {
            throw new QueryException($this->sql_connection_object->lastErrorMsg(), $query);
        } else {
            if (empty($result) === true) {
                return false;
            } else {
                $returns = array();
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $returns[] = $row;
                }
                $result->finalize();
                if ($is_array === false) {
                    return ArrayUtil::arrayToObject($returns);
                } else {
                    return $returns;
                }
            }
        }
    }

    public function getOne($query, $is_array = false) {
        if (($result = @$this->sql_connection_object->querySingle($query)) === false) {
            throw new QueryException($this->sql_connection_object->lastErrorMsg(), $query);
        } else {
            if (empty($result) === true) {
                return false;
            } else {
                if ($is_array === false) {
                    return ArrayUtil::arrayToObject($result);
                } else {
                    return $result;
                }
            }
        }
    }

    public function set($query) {
        if ((@$this->sql_connection_object->exec($query)) === false) {
            throw new QueryException($this->sql_connection_object->lastErrorMsg(), $query);
        }
    }

    /**
     * 
     * @param type $query
     * @return \YniDB\SQLite\SQLitePrepared
     * @throws QueryException
     */
    public function prepare($query) {
        if (($stmt = StmtCacher::getIncetance()->getStmt($this->conn_id, $query)) !== false) {
            return $stmt;
        }
        if (($stmt = @$this->sql_connection_object->prepare($query)) === false) {
            throw new QueryException($this->sql_connection_object->lastErrorMsg(), $query);
        } else {
            $return = new SQLitePrepared($query, $stmt, $this->conn_id);
            StmtCacher::getIncetance()->setStmt($return);
            return $return;
        }
    }

    public function getTableRecodeNum($table_name) {
        return @$this->sql_connection_object->querySingle("SELECT COUNT(*) FROM {$table_name};");
    }

    public function lastInsertedRow() {
        return $this->sql_connection_object->lastInsertRowID;
    }

    /**
     * 選択されたファイルを現在のパスで開きます。
     * @param type $db_name
     * @throws \YniDB\Exception\ConnectionException
     */
    public function changeDB($db_name) {
        try {
            $this->sql_connection_object = &SQLiteConnection::getInctance(dirname($this->file_path) . $db_name, $this->write_mode)->getConnection();
        } catch (ConnectionException $e) {
            throw $e;
        }
    }

    public function getAffectedRow() {
        
    }

}

