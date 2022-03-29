<?php

namespace app\admin\controller\user;

use app\common\controller\Backend;
use app\admin\model\UserMoneyLog;
use app\admin\model\User;

class MoneyLog extends Backend
{
    protected $model = null;

    protected $withJoinTable = ['user'];

    // 排除字段
    protected $preExcludeFields = ['createtime'];

    protected $quickSearchField = ['user.username', 'user.nickname'];

    public function initialize()
    {
        parent::initialize();
        $this->model = new UserMoneyLog();
    }

    /**
     * 添加
     */
    public function add($userId = 0)
    {
        if ($this->request->isPost()) {
            parent::add();
        }

        $user = User::where('id', (int)$userId)->find();
        if (!$user) {
            $this->error('用户找不到啦~');
        }
        $this->success('', [
            'user' => $user
        ]);
    }
}