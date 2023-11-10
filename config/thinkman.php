<?php
// +----------------------------------------------------------------------
// | ThinkMan [A Simple Web Framework For ThinkPHP]
// +----------------------------------------------------------------------
// | ThinkMan
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: axguowen <axguowen@qq.com>
// +----------------------------------------------------------------------

use think\thinkman\App;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Response;

return [
    // 监听地址
    'host' => '0.0.0.0',
    // 监听端口
    'port' => 8000,
    // 上下文
    'context' => [],
    // 是否以守护进程启动
    'daemonize' => false,
    // 内容输出文件路径
    'stdout_file' => '',
    // pid文件路径
    'pid_file' => '',
    // 日志文件路径
    'log_file' => '',
    // 最大请求数, 进程接收到该数量的请求后自动重启防止内存泄露
    'request_limit' => 10000,
    // 静态文件支持
    'static_support' => true,
    // 文件监控配置(仅Linux下有效)
    'monitor' => [
        // 是否开启文件监控
        'enable' => true,
        // 文件监控检测时间间隔(单位：秒)
        'interval' => 2,
        // 文件监控目录, 默认监控app和config目录
        'paths' => [],
        // 最大内存限制, 进程占用内存达到该数值后自动重启防止内存泄露
        'memory_limit' => '129m',
        // 锁文件路径
        'lock_file' => '',
    ],
    // Worker配置
    'worker' => [
        // 进程名称
        'name' => 'thinkman',
        // 进程数量
        'count' => 1,
        // 支持workerman的其它配置参数
    ],
    'hooks' => [
        'beforeWorkerRequest' => function (App $app) {
            $connection = $app->get(TcpConnection::class);
            $reqeust = $app->get(Request::class);
            $rootPath = public_path();
            $server   = $reqeust->get('server') || $reqeust->post('server') || $reqeust->header('server') ||substr($reqeust->path(), 1, 9) == 'index.php';
            if (!$server) {
                // 用户访问前端
                $headers = [];
                // 安装检测-s
                if (!is_file($rootPath . 'install.lock') && is_file($rootPath . 'install' . DIRECTORY_SEPARATOR . 'index.html')) {
                    $headers['Location'] = '/install';
                }
                // 安装检测-e

                /*
                 * 检测是否已编译前端-s
                 * 如果存在 index.html 则访问 index.html
                 */
                if (is_file($rootPath . 'index.html')) {
                    $headers['Location'] = '/index.html';
                }
                // 检测是否已编译前端-e
                if($headers) {
                    $connection->send(new Response(302, $headers));
                    return false;
                }
            }
            return true;
        }
    ]
];
