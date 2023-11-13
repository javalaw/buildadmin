<?php

declare(strict_types=1);

namespace app\middleware;

use app\ServerSideEvent;
use think\App;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\ServerSentEvents;

class LongLifeApp
{
    public function __construct(protected App $app, protected bool $sseRegistered = false)
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
        if (!$this->sseRegistered) {
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
            $this->sseRegistered = true;
        }


        $resp = $next($request);
        ServerSideEvent::reset();
        return $resp;
    }


}
