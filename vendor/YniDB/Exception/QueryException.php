<?php

namespace YniDB\Exception;

use YniDB\Exception\SQLException AS SQLException;

/**
 * クエリ実行時の例外
 *
 * @author yni3
 */
class QueryException extends SQLException {

    const VERSION = '1.0';
    const DATE_APPROVED = '2013-03-30';
    const TAG = 'QueryException';
    /**
     * 
     * @param type $error_message
     * @param type $query
     */    
    public function __construct($error_message, $query = NULL) {
        parent::__construct($error_message);
        if (is_null($query)) {
            $this->setMessage($error_message);
        } else {
            $this->setMessage($error_message, ' : ', $query);
        }
        
    }

}

