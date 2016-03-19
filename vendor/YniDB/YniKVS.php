<?php

namespace YniDB;

use YniDB\SQLite\SQLite AS SQLite;
use YniDB\Core\YniTimer AS Timer;
use Iterator;

/**
 * KVS
 *
 * @author yni3
 */
class YniKVS implements Iterator {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-06-08';

    private $db_method;
    private $get_stmt;
    private $get_stmt_sq;
    private $set_stmt;
    private $delete_stmt;
    private $count_stmt;
    private $iterate_stmt;

    /**
     * 
     * @param type $file_path
     * @param type $write_mode
     * @return \self
     */
    public static function createSQLite($file_path, $write_mode = NULL) {
        return new self($file_path, $write_mode);
    }

    private function __construct($file_path, $write_mode) {
        try {
            $this->db_method = new SQLite($file_path, $write_mode);
            $this->db_method->set('
CREATE TABLE IF NOT EXISTS `yni_kvs` (
  `id` integer primary key autoincrement,
  `key` text UNIQUE NOT NULL,
  `value` text NOT NULL DEFAULT \'0\',
  `time` integer NOT NULL);');
            $this->db_method->set('create index IF NOT EXISTS kvs_key on yni_kvs(key);');
            $this->set_stmt = $this->db_method->prepare('REPLACE INTO `yni_kvs` (`key`, `value`, `time`) VALUES (?, ?, ?);');
            $this->get_stmt = $this->db_method->prepare('SELECT value FROM `yni_kvs` WHERE key = ?;');
            $this->get_stmt_sq = $this->db_method->prepare('SELECT key,value FROM `yni_kvs` ORDER BY id LIMIT ?,1;');
            $this->delete_stmt = $this->db_method->prepare('DELETE FROM `yni_kvs` WHERE key = ?;');
            $this->count_stmt = $this->db_method->prepare('select count(*) from `yni_kvs`;');
            $this->iterate_stmt = $this->db_method->prepare('SELECT `id`,`key`,`value` FROM `yni_kvs` where `id` > ? ORDER BY `id` LIMIT 0,1;');
        } catch (SQLException $e) {
            throw $e;
        }
    }

    public function set($key, $value) {
        $time = Timer::getTimeBigint();
        $this->set_stmt->reset();
        if (is_scalar($value) || is_object($value) || is_array($value)) {
            return $this->set_stmt->execute($key, serialize($value), $time);
        } else {
            throw new Exception\UnSurpportedException("this variable type is not supported");
        }
    }

    public function get($key) {
        $this->get_stmt->reset();
        $this->get_stmt->execute($key);
        $r = $this->get_stmt->getResultOne();
        if (!empty($r) && isset($r['value'])) {
            return unserialize($r['value']);
        } else {
            return NULL;
        }
    }

    public function getSequential($index) {
        $this->get_stmt_sq->reset();
        $this->get_stmt_sq->execute($index);
        $r = $this->get_stmt_sq->getResultOne();
        if (!empty($r) && isset($r['value'])) {
            return unserialize($r['value']);
        } else {
            return NULL;
        }
    }

    public function getSequentialKey($index) {
        $this->get_stmt_sq->reset();
        $this->get_stmt_sq->execute($index);
        $r = $this->get_stmt_sq->getResultOne();
        if (!empty($r) && isset($r['key'])) {
            return $r['key'];
        } else {
            return NULL;
        }
    }

    public function delete($key) {
        $this->delete_stmt->execute($key);
    }

    public function count() {
        $this->count_stmt->reset();
        $this->count_stmt->execute();
        $r = $this->count_stmt->getResultOne();
        return $r["count(*)"];
    }

    const DEFAULT_POS = -1;

    private $position = self::DEFAULT_POS;
    private $last_obj = self::DEFAULT_POS;

    public function current() {
        return unserialize($this->last_obj->value);
    }

    public function key() {
        return $this->last_obj->key;
    }

    private function itrGot() {
        $this->iterate_stmt->reset();
        $this->iterate_stmt->execute($this->position);
        $r = $this->iterate_stmt->getResultOne(true);
        if (!empty($r)) {
            $this->position = $r->id;
            return $r;
        } else {
            return NULL;
        }
    }

    public function next() {
        //なにもしない
        //++$this->position;
    }

    public function rewind() {
        $this->position = -1;
        $this->last_obj = self::DEFAULT_POS;
    }

    public function valid() {
        //nextの存在を確認
        if (($this->last_obj = $this->itrGot()) !== NULL) {
            return true;
        } else {
            return false;
        }
    }

}
