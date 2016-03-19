<?php

namespace YniDB\Common;

use YniDB\Core\YniBase AS YniCore;
use YniDB\DBInterface\PreparedInterface AS PreparedInterface;

/**
 * プリペアｰドステートメントを記憶し、ステートメントがまだ有効であれば、再利用を申請します。
 *
 * @author yni3
 */
class StmtCacher extends YniCore {

    private static $self = NULL;

    /**
     * StmtCacherオブジェクトを取得
     * @return \YniDB\Common\StmtCacher $object
     */
    public static function getIncetance() {
        if (empty(self::$self) === true) {
            self::$self = new self();
        }
        return self::$self;
    }
    
    private function __construct() {
    }

    private $cache = array();

    /**
     * キャッシュするSTMTオブジェクトをセットします。
     * @param \YniDB\DBInterface\PreparedInterface $object
     * @return boolean
     */
    public function setStmt(PreparedInterface $object) {
        $conn_id = $object->getConnectionId();
        $query_hash = md5($object->getQuery());
        $conn_array = NULL;
        if (isset($this->cache[$conn_id]) === false) {
            $this->cache[$conn_id] = array();
            $this->cache[$conn_id][$query_hash] = $object;
        } else { //同じコネクションIdがある-----------------------------------------------
            $conn_array = &$this->cache[$conn_id];
            if (isset($conn_array[$query_hash]) === false) { //----同じクエリIdがない-----
                $conn_array[$query_hash] = $object;
            } else { //--------------------------------------------同じクエリIdがある-----
                $stored_object = &$conn_array[$query_hash];
                if (is_array($stored_object) === true) { //すでに"クエリIdは同じだがクエリ文が異なるobject"が格納されている
                    if ($this->findQuery($object->getQuery(), $stored_object) === false) { //格納されていない
                        $stored_object[] = $object;
                    } else { //格納されている
                        return false;
                    }
                } else if ($stored_object->getQuery() !== $object->getQuery()) {//クエリIdは同じだが、クエリ文が異なる
                    $conn_array[$query_hash] = array($object, $conn_array[$query_hash]);
                } else {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * PreparedInterfaceの配列から、指定のクエリをもつarray-idを取得します。
     * @param string $query
     * @param array $prepared_objects
     * @return int|false
     */
    private function findQuery($query, array $prepared_objects) {
        foreach ($prepared_objects as $key => $value) {
            if ($query === $value->getQuery()) {
                return $key;
            }
        }
        return false; //見つからない
    }

    /**
     * STMTオブジェクトをキャッシュ
     * @param string $query
     * @param string $connection_id
     * @return \YniDB\DBInterface\PreparedInterface|false
     */
    public function getStmt($connection_id, $query) {
        $query_hash = md5($query);
        if (isset($this->cache[$connection_id])) {
            if (isset($this->cache[$connection_id][$query_hash])) {
                $stored_object = &$this->cache[$connection_id][$query_hash];
                if (is_array($stored_object) === true) {
                    if (($stored_id = $this->findQuery($query, $stored_object)) !== false) {
                        return $stored_object[$stored_id];
                    } else {
                        return false;
                    }
                } else {
                    return $stored_object;
                }
            }
        }
        return false;
    }

    /**
     * キャッシュされたステートメントを削除
     * @param string $connection_id
     * @param string $query
     * @return boolean
     */
    public function deleteStmt($connection_id, $query = NULL) {
        if (isset($this->cache[$connection_id])) {
            if (is_null($query) === false) {
                unset($this->cache[$connection_id]);
                return true;
            } else if (isset($this->cache[$connection_id][$query])) {
                unset($this->cache[$connection_id][$query]);
                return true;
            }
        }
        return false;
    }

}

