<?php

namespace YniDB\SQLite;

use YniDB\Core\YniBase AS YniCore;
use YniDB\DBInterface\PreparedInterface AS PreparedInterface;
use YniDB\Exception\QueryException AS QueryException;
use YniDB\Core\YniArrayUtil AS ArrayUtil;

/**
 * Description of SQLitePrepared
 *
 * @author yni3
 */
class SQLitePrepared extends YniCore implements PreparedInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-10-20';

    //-----------------------------------------
    protected $query = '';
    protected $prepared_stmt = NULL;
    protected $freed = false;
    protected $result = NULL;
    protected $conn_id = NULL;

    //-----------------------------------------

    const SQLITE3_INTEGER = 'SQLITE3_INTEGER';
    const SQLITE3_FLOAT = 'SQLITE3_FLOAT';
    const SQLITE3_TEXT = 'SQLITE3_TEXT';
    const SQLITE3_BLOB = 'SQLITE3_BLOB';
    const SQLITE3_NULL = 'SQLITE3_NULL';
    const RESULT_TYPE_OBJECT = true;
    const RESULT_TYPE_ARRAY = false;

    public function __construct($query, $prepared, $connection_id) {
        $this->query = $query;
        $this->prepared_stmt = $prepared;
        $this->conn_id = $connection_id;
    }

    public function __destruct() {
        if ($this->freed === false) {
            //$this->reset();
        }
        //$this->prepared_stmt->close();
    }

    public function execute($values = NULL) {
        if (($values !== NULL) && ($this->prepared_stmt->paramCount() !== 0)) {
            $values = func_get_args();
            $max = count($values);
            if ($max != $this->prepared_stmt->paramCount()) {
                throw new QueryException("Paramater num is mismuch! require " . $this->prepared_stmt->paramCount() . " imputed {$max}", $this->query);
            }
            $bind_type = '';
            $ii = 0;
            for ($i = 0; $i < $max; $i++) {
                $ii = $i + 1;
                switch (gettype($values[$i])) {
                    case 'boolean':
                        $bind_type = SQLITE3_INTEGER;
                        $values[$i] = (int) $values[$i];
                        break;
                    case 'integer':
                        $bind_type = SQLITE3_INTEGER;
                        break;
                    case 'float':
                    case 'double':
                        $bind_type = SQLITE3_FLOAT;
                        break;
                    case 'string':
                        $bind_type = SQLITE3_TEXT;
                        break;
                    case 'null':
                        $bind_type = SQLITE3_NULL;
                        break;
                    default:
                        throw new QueryException("binding error values unknown type -> " . implode(',', $values), $this->query);
                }
                if ($this->prepared_stmt->bindValue($ii, $values[$i], $bind_type) === false) {
                    throw new QueryException("binding error values ($ii, $values[$i], $bind_type) -> " . implode(',', $values), $this->query);
                }
            }
        }
        //前回の結果を解放
        if (is_null($this->result) === false) {
            $this->result->finalize();
            $this->result = NULL;
        }
        if (($this->result = $this->prepared_stmt->execute()) === false) {
            throw new QueryException($this->prepared_stmt->error . " executing error values -> " . implode(',', $values), $this->query);
        }
        $this->freed = false;
    }

    public function reset() {
        if (is_null($this->result) === false) {
            $this->result->finalize();
            $this->result = NULL;
        }
        $this->prepared_stmt->reset();
        $this->prepared_stmt->clear();
        $this->freed = true;
    }

    public function getResult($return_type = self::RESULT_TYPE_ARRAY, $autoFree = false) {
        if ($return_type === self::RESULT_TYPE_ARRAY) {
            $returns = array();
            while ($row = $this->result->fetchArray(SQLITE3_ASSOC)) {
                $returns[] = $row;
            }
            if ($autoFree === true) {
                $this->reset();
            }
            return $returns;
        } else {
            return ArrayUtil::arrayToObject($this->getResult(self::RESULT_TYPE_ARRAY, $autoFree));
        }
    }

    public function getResultOne($return_type = self::RESULT_TYPE_ARRAY, $autoFree = false) {
        if ($return_type === self::RESULT_TYPE_ARRAY) {
            if ($autoFree === true) {
                $this->reset();
            }
            return $this->result->fetchArray(SQLITE3_ASSOC);
        } else {
            return ArrayUtil::arrayToObject($this->getResultOne(self::RESULT_TYPE_ARRAY, $autoFree));
        }
    }

    public function getQuery() {
        return $this->query;
    }

    public function getConnectionId() {
        return $this->conn_id;
    }

}

