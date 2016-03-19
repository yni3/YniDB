<?php

namespace YniDB\Exception;

use \Exception;

/**
 * デフォルト例外
 *
 * @author yni3
 */
class YniException extends Exception {

    const VERSION = '1.0';
    const DATE_APPROVED = '2013-06-30';
    const TAG = 'YniException';

    public function __construct($message, $code = NULL, $previous = NULL) {
        parent::__construct($message, $code, $previous);
        $this->setMessage($message);
    }

    public function setMessage($messages) {
        $messages = array_merge(array(static::TAG . ' :'), func_get_args());
        $this->message = implode(' ', $messages);
    }

    public function explain($echo = true) {
        if ($echo) {
            echo $this->getMessage();
            echo $this->getTraceAsString();
        } else {
            return $this->getMessage() . PHP_EOL . $this->getTraceAsString();
        }
    }

    public function __toString() {
        return print_r($this);
    }

}

