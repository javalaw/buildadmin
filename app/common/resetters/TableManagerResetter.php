<?php
namespace app\common\resetters;

use ba\TableManager;
use think\thinkman\contracts\Resetter;

class TableManagerResetter implements Resetter
{
    public function reset()
    {
        TableManager::clearInstance();
    }
}