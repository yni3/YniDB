<?php

namespace YniDB\DBInterface;

use YniCore\YniBaseInterface AS YniBaseInterface;

/**
 * 初期の際に呼ばれるコールバック
 * @author yni3
 */
interface InitializementCallBack extends YniBaseInterface{
    public function initCallBack();
}

