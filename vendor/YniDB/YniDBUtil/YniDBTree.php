<?php

namespace YniDBUtil;

/**
 * 入れ子区間用プリペアｰドクエリの生成
 *
 * @author yni3
 */
class SQLiteTree {

    public $table_name;
    public $id_name;
    public $left_node_name;
    public $right_node_name;
    protected $insert;

    public function __construct(YniDB $db, $table_name, $id_name = 'id', $left_node_name = 'lft', $right_node_name = 'rgt') {
        ;
        //自分の子をすべて取得
        //http://www.geocities.jp/mickindex/database/db_tree_ns.html
        $query = <<<EOL
                
                
                
EOL;
        //自分の直属の子をすべて取得
        //http://www.geocities.jp/mickindex/database/db_tree_ns.html
        $query = <<<EOL
SELECT Boss.emp AS boss, Worker.emp AS worker 
  FROM {$table_name} AS Boss
       LEFT OUTER JOIN {$table_name} Worker
       ON Boss.{$left_node_name} = (SELECT MAX({$left_node_name})
                     FROM {$table_name}
                    WHERE Worker.{$left_node_name} > {$left_node_name}
                      AND Worker.{$left_node_name} < {$right_node_name});
EOL;
        //指定IDの下に挿入するクエリ
        //http://d.hatena.ne.jp/tociyuki/20110627/1309173098
        $query = <<<EOL
INSERT INTO {$table_name}
    SELECT  L.{$left_node_name} * 0.7 + L.{$right_node_name} * 0.3,
        L.{$left_node_name} * 0.3 + L.rgt * 0.7
    FROM (SELECT 
            CASE WHEN C.{$right_node_name} IS NULL 
            THEN P.{$left_node_name} ELSE MAX(C.{$right_node_name}) END AS lft,
            P.{$right_node_name} AS {$right_node_name}
          FROM {$table_name} AS P LEFT OUTER JOIN {$table_name} AS C
            ON P.lft = (SELECT MAX(Q.{$left_node_name})
          FROM {$table_name} AS Q
          WHERE C.{$left_node_name} > Q.{$left_node_name} AND C.{$left_node_name} < Q.{$right_node_name})
            WHERE P.{$id_name} = ?) AS L;
EOL;
    }

    /**
     * 子供をすべて取得
     * @param type $id
     */
    public function getChild($id) {
        
    }

    /**
     * 直属の子供のみ取得
     * @param type $id
     */
    public function getChildDirect($id) {
        
    }

    /**
     * idの配下に挿入。<br>
     * insert後idを返す。<br>
     * このidをUpdateするなりして、任意の値を挿入してください。
     * @param type $id
     * @return id
     */
    public function insertEntryUnderId($id) {

        return;
    }

}

