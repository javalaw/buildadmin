<?php

declare(strict_types=1);

namespace app\middleware;

use app\admin\library\Auth;
use app\admin\library\module\Manage;
use app\common\library\Auth as LibraryAuth;
use app\ServerSideEvent;
use ba\TableManager;
use ba\Terminal;
use ba\Tree;
use think\App;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\ServerSentEvents;

class LongLifeApp
{
    public function __construct(protected App $app)
    {
    }
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        if (class_exists(TcpConnection::class) && $this->app->has(TcpConnection::class)) {
            ServerSideEvent::registerHandler(function (string $data) {
                $connection = $this->app->make(TcpConnection::class);
                while (ob_get_level()) {
                    ob_end_clean();
                }
                $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $data]));
            });
            ServerSideEvent::registerHeaderHandler(function (array $headers) {
                $connection = $this->app->make(TcpConnection::class);
                while (ob_get_level()) {
                    ob_end_clean();
                }
                $connection->send(new Response(headers: $headers, body: "\r\n"));
            });
        }

        return $next($request);
    }

    public function end()
    {
        app()->delete(Auth::class);
        app()->delete(LibraryAuth::class);
        app()->delete(Terminal::class);
        app()->delete(Manage::class);
        app()->delete(Tree::class);
        TableManager::clearInstance();
    }
}
