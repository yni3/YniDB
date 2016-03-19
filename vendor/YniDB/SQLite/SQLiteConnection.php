<?php

namespace YniDB\SQLite;

use YniDB\Core\YniBase AS YniCore;
use YniDB\DBInterface\DBConnectionInterface AS DBConnectionInterface;
use YniDB\Exception\ConnectionException AS ConnectionException;
use \SQLite3;
use YniDB\Util\DirectoryUtil AS Dirs;
use \Exception AS Exception;
use YniDB\DBInterface\InitializementCallBack AS InitializementCallBack;

/**
 * SQLiteのコネクター
 *
 * @author yni3
 */
class SQLiteConnection extends YniCore implements DBConnectionInterface {

    const VERSION = '0.1';
    const DATE_APPROVED = '2013-06-08';
    //
    const ReadWrite = 0;
    const ReadOnly = 1;

    private $sqlite_object;
    private static $instance = array();

    /**
     * 
     * @param string $file_name
     * @param int $user_write_mode
     * @return SQLiteConnection
     * @throws Exception
     */
    final public static function getInctance($file_name, $user_write_mode) {
        if (isset(self::$instance[(string) $file_name]) === false) {
            self::$instance[(string) $file_name] = array();
            try {
                self::$instance[(string) $file_name] = new self($file_name, $user_write_mode);
            } catch (Exception $exc) {
                throw $exc;
            }
        }
        return self::$instance[(string) $file_name];
    }

    public function __construct($file_name, $user_write_mode, InitializementCallBack $callBack = NULL) {
        if (file_exists($file_name) === true) { //ファイルが存在する
            //何もしない
        } else {
            if (Dirs::checkIsWritableDirectory(dirname($file_name)) === true) { //書き込みチェック
                if (Dirs::mkdir(dirname($file_name)) === false) { //ディレクトリ作成
                    throw new ConnectionException("directory " . $file_name . " is not created");
                }
                if (is_null($callBack) === false) {
                    $callBack->initCallBack(); //初期化時のコールバック
                }
            } else {
                throw new ConnectionException($file_name . " is not writable.");
            }
        }
        //DBを用意
        switch ($user_write_mode) {
            case self::ReadWrite:
                $write_mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
                break;
            case self::ReadOnly:
                $write_mode = SQLITE3_OPEN_READONLY;
                break;
            default :
                $write_mode = SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
                break;
        }
        try {
            $this->sqlite_object = new SQLite3($file_name, $write_mode);
        } catch (Exception $e) {
            throw new ConnectionException("Error at new SQLite3({$file_name}, {$write_mode}); ");
        }
        $this->sqlite_object->busyTimeout(30000); //30sec
    }

    public function __destruct() {
        try {
            $this->closeConnection();
        } catch (Exception $exc) {
            throw $exc;
        }
    }

    public function closeConnection() {
        if (is_null($this->sqlite_object) === false) {
            if ($this->sqlite_object->close() === false) {
                throw new ConnectionException();
            } else {
                $this->sqlite_object = NULL;
            }
        }
    }

    public function &getConnection() {
        return $this->sqlite_object;
    }

    public function getConnectionId() {
        return $this->getObjectId();
    }

}
