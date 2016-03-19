<?php

namespace YniDB\Exception;

use YniDB\Exception\SQLException AS SQLException;

/**
 * DB接続に関する例外
 *
 * @author yni3
 */
class ConnectionException extends SQLException {

    const VERSION = '1.0';
    const DATE_APPROVED = '2013-03-30';
    const TAG = 'ConnectionException';
    
    public function __construct($message, $code = NULL, $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }

}
