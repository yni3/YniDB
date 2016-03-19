<?php

namespace YniDB\MySQL;

use YniCore\YniBase AS YniCore;
use YniDB\DBInterface\PreparedInterface AS PreparedInterface;
use YniCore\YniArrayUtil AS ArrayUtil;
use YniCore\Exception\InvalidAugumentException AS InvalidAugumentException;
use YniDB\Exception\QueryException AS QueryException;
use YniDB\Exception\ResultIsTooLargeException AS ResultIsTooLargeException;

/**
 * 
 *
 * @author yni3
 */
class MySQLPrepared extends YniCore implements PreparedInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-10-20';

    protected $query = '';
    protected $prepared_stmt = NULL;
    protected $isFreed = false;
    protected $connection_id = NULL;
    protected $result = NULL;
    protected $result_num = 0;

    public function __construct($query, $prepared, $connection_id) {
        $this->query = $query;
        $this->prepared_stmt = $prepared;
        $this->connection_id = $connection_id;
    }

    public function execute($values = NULL) {
        if (($values !== NULL) && ($this->prepared_stmt->param_count !== 0)) {
            $values = func_get_args();
            $max = count($values);
            if ($max != $this->prepared_stmt->param_count) {//パラメータ数の比較
                throw new QueryException("Paramater num is mismuch! require " . $this->prepared_stmt->param_count . " imputed {$max}", $this->query);
            }
            $bind_type = '';
            $ii = 0;
            //型認識
            for ($i = 0; $i < $max; $i++) {
                $ii = $i + 1;
                switch (gettype($values[$i])) {
                    case 'boolean':
                        $bind_type .= 'i';
                        $values[$i] = (int) $values[$i];
                        break;
                    case 'integer':
                        $bind_type .= 'i';
                        break;
                    case 'float':
                    case 'double':
                        $bind_type .= 'd';
                        break;
                    case 'string':
                        $bind_type .= 's';
                        break;
                    case 'NULL' :
                        $bind_type .= 's';
                        break;
                    case 'object':
                        switch (get_class($values[$i])) {
                            case 'YniCore\YniStdObject':
                                $values[$i] = (string) $values[$i];
                                $bind_type .= 's';
                                break;
                            default:
                                throw new QueryException("binding error values unknown type " . gettype($values[$i]) . " which " . get_class($values[$i]) . " -> (" . implode(',', $values) . ")", $this->query);
                                break;
                        }
                        break;
                    default:
                        throw new QueryException("binding error values unknown type " . gettype($values[$i]) . " -> (" . implode(',', $values) . ")", $this->query);
                        break;
                }
            }
            //バインド
            array_unshift($values, $bind_type);
            if (call_user_func_array(array($this->prepared_stmt, 'bind_param'), $this->refValues($values)) === false) {
                throw new QueryException("binding error : message = [{$this->prepared_stmt->errno}:{$this->prepared_stmt->error}] auguments = [" . implode(',', $values) . "]", $this->query);
            }
        }
        //前回の結果を解放
        if (is_null($this->result) === false) {
            $this->result->free();
            $this->result = NULL;
        }
        //実行
        if ($this->prepared_stmt->execute() === false) {
            throw new QueryException("executing error : message = [{$this->prepared_stmt->errno}:{$this->prepared_stmt->error}] auguments = [" . implode(',', $values) . "]", $this->query);
        }
        $this->isFreed = false;
    }

    public function __destruct() {
        if ($this->isFreed === false) {
            $this->reset();
        }
        $this->prepared_stmt->close();
    }

    public function reset() {
        $this->prepared_stmt->free_result();
        $this->prepared_stmt->reset();
        $this->result = NULL;
        $this->result_num = 0;
        $this->isFreed = true;
    }

    public function getResult($isArray = false, $autoFree = true) {
        if ($isArray === true) {
            //@see http://php.net/manual/ja/mysqli-stmt.bind-param.php
            $results = array();
            $this->prepared_stmt->store_result();
            $meta = $this->prepared_stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }

            call_user_func_array(array($this->prepared_stmt, 'bind_result'), $this->refValues($parameters));

            while ($this->prepared_stmt->fetch()) {
                $x = array();
                $this->result_num++;
                if ($this->result_num > self::RESULT_MAX) {
                    throw new ResultIsTooLargeException();
                }
                foreach ($row as $key => $val) {
                    $x[$key] = $val;
                }
                $results[] = $x;
            }
            if ($autoFree === true) {
                $this->reset();
            }
            return $results;
        } else {
            return ArrayUtil::arrayToObject($this->getResult(true, $autoFree));
        }
    }

    public function getResultOne($isArray = false, $autoFree = true) {
        if ($isArray === true) {
            $this->prepared_stmt->store_result();
            $meta = $this->prepared_stmt->result_metadata();

            while ($field = $meta->fetch_field()) {
                $parameters[] = &$row[$field->name];
            }

            call_user_func_array(array($this->prepared_stmt, 'bind_result'), $this->refValues($parameters));

            $this->prepared_stmt->fetch();
            $x = array();
            foreach ($row as $key => $val) {
                $x[$key] = $val;
            }

            if ($autoFree === true) {
                $this->reset();
            }
            if (empty($x) === true) {
                $x = false;
            }
            return $x;
        } else {
            return ArrayUtil::arrayToObject($this->getResultOne(true, $autoFree));
        }
    }

    /**
     * @see http://www.php.net/manual/ja/mysqli-stmt.bind-param.php PHPManual
     * @param type $arr
     * @return type
     */
    private function refValues($arr) {
        if (!is_array($arr) && !empty($arr)) {
            throw new InvalidAugumentException("augument is not array", $arr);
            return $arr;
        }
        if (strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+
            $refs = array();
            foreach ($arr as $key => $value) {
                $refs[$key] = &$arr[$key];
            }
            return $refs;
        }
        return $arr;
    }

    public function getQuery() {
        return $this->query;
    }

    public function getConnectionId() {
        return $this->connection_id;
    }

}

