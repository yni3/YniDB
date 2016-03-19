<?php

namespace YniDB\Exception;

use YniDB\Exception\SQLException AS SQLException;

/**
 * UnSurpportedException
 *
 * @author yni3
 */
class UnSurpportedException extends SQLException {
    const VERSION = '1.0';
    const DATE_APPROVED = '2013-06-07';
    const TAG = 'UnSurpportedException';
    
    public function __construct($message, $code = NULL, $previous = NULL) {
        parent::__construct($message, $code, $previous);
    }
}

