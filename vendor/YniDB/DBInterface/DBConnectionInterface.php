<?php

namespace YniDB\DBInterface;

/**
 *
 * @author yni3
 */
interface DBConnectionInterface{
    
    /**
     * 接続idの取得<br>
     * 接続毎に一意なidを取得する
     * @return int 接続id
     */
    public function getConnectionId();
    
    /**
     * 接続を切断します。
     */
    public function closeConnection();

    /**
     * デストラクタを定義してください。
     */
    public function __destruct();

    /**
     * コネクションを取得します。
     */
    public function &getConnection();
}

