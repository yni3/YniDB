<?php

namespace YniDB\Exception;

use \Exception AS Exception;

/**
 * SQLの基底例外
 *
 * @author yni3
 */
class SQLException extends Exception{

    const VERSION = '1.0';
    const DATE_APPROVED = '2013-03-30';
    const TAG = 'SQLException';

    public function __construct($message, $code = NULL, $previous = NULL) {
        parent::__construct($message, $code, $previous);
        $this->setMessage($message);
    }

    public function setMessage($messages) {
        $messages = array_merge(array(static::TAG.' :'), func_get_args());
        $this->message = implode(' ', $messages);
    }

}

