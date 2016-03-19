<?php

namespace YniDB\DBInterface;

/**
 *
 * @author yni3
 */
interface DBInterface {

    /**
     * トランザクションを開始します
     */
    public function beginTransaction();
    
    /**
     * トランザクションを終了します
     */
    public function endTransaction();

    /**
     * トランザクションの処理をコミットします。
     */
    public function commit();

    /**
     * ロールバックします。
     */
    public function rollBack();
    public function __destruct();
    
    /**
     * クエリを実行します。
     * @param string $query
     * @param bool $is_array オプション(配列で受け取るならtrue)
     * @return stdClass|array 結果
     */
    public function get($query, $is_array = false);

    /**
     * クエリを実行し、結果を一つ返します。
     * @param string $query
     * @param bool $is_array オプション(配列で受け取るならtrue)
     * @return stdClass|array 結果
     */
    public function getOne($query, $is_array = false);

    /**
     * 戻り値の不要なクエリを実行します
     * @param string $query
     * @return bool 成功ならtrue
     */
    public function set($query);

    /**
     * 文字列をサニタイズします
     * @param string $string サニタイズする文字列
     */
    public function escape($string);

    /**
     * 最後に挿入を行った行を取得します。
     * @return int 最後に挿入した行数
     */
    public function lastInsertedRow();

    /**
     * テーブルのレコード数を表示します。
     * @param string $table_name テーブル名
     * @return int レコード数
     */
    public function getTableRecodeNum($table_name);

    /**
     * プリペアードステートメントを用意します
     * @param string $query クエリ(bind箇所?記述)
     * @return YniDB\DBInterface\PreparedInterface YniDBのプリペアｰドオブジェクト
     */
    public function prepare($query);
    
    /**
     * 接続先を指定のDBへ変更
     * @param string $db_name
     */
    public function changeDB($db_name);
    
    /**
     * 直前のクエリで変更された行数を取得
     * @return int 影響を受けた行数
     */
    public function getAffectedRow();
}

