<?php

namespace YniDB\DBInterface;

use YniDB\Core\YniBaseInterface AS YniBaseInterface;

/**
 *
 * @author yni3
 */
interface PreparedInterface extends YniBaseInterface{
    
    const RESULT_MAX = 6400;
    
    public function __construct($query,$prepared,$connection_id);
    /**
     * ステートメントの解放
     */
    public function __destruct();
    
    /**
     * このステートメントをリセットします。
     */
    public function reset();

    /**
     * パラメーターをバインドし、<br>
     * ステートメントを実行します。<br>
     * 変数の型によりバインドするデータの種類が決定されます。
     * @param mixed $values... バインドする値
     * @throws \YniDB\Exception\QueryException
     */
    public function execute($values = NULL);
    
    /**
     * 結果を取得します。
     * @param bool $isArray デフォルトでfalse
     * @param bool $autoFree デフォルトでtrue
     * @return array 空の場合は空配列
     */
    public function getResult($isArray = false,$autoFree = true);
    
    /**
     * 結果を一つだけ取得します。
     * @param bool $isArray デフォルトでfalse
     * @param bool $autoFree デフォルトでtrue
     * @return array 空の場合はfalse
     */
    public function getResultOne($isArray = false, $autoFree = true);
    
    /**
     * クエリを取得します。
     * @return string クエリ
     */
    public function getQuery();

     /**
     * クエリを取得します。
     * @return string クエリ
     */
    public function getConnectionId();
    
}

