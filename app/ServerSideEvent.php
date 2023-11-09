<?php

namespace app;

use Closure;
use think\facade\Log;

class ServerSideEvent
{

    protected static ?Closure $callback = null;

    protected static ?Closure $headerCallback = null;

    protected static bool $headerSent = false;

    public static function headers()
    {
        return [
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
        ];
    }

    /**
     * 注册sse处理器
     * @param callable $callback 
     * @return void 
     */
    public static function registerHandler(callable $callback)
    {
        self::$headerSent = false;
        self::$callback = Closure::fromCallable($callback);
    }

    /**
     * 注册sse header处理器
     * @param callable $callback 
     * @return void 
     */
    public static function registerHeaderHandler(callable $callback)
    {
        self::$headerCallback = Closure::fromCallable($callback);
    }

    /**
     * PHPFPM 默认处理器
     * @param mixed $data 
     * @return void 
     */
    public static function defaultHandler($data)
    {
        echo 'data: ' . $data . "\n\n";
        @ob_flush();
    }

    public static function defaultHeaderHandler(array $headers)
    {
        foreach ($headers as $key => $value) {
            header("$key: $value");
        }
        while (ob_get_level()) {
            ob_end_clean();
        }
        if (!ob_get_level()) ob_start();
    }

    /**
     * 发布一个SSE
     * @param string $data 
     * @return void 
     */
    public static function emit(string $data)
    {
        if(!static::$headerSent) {
            $headerCallback = self::$headerCallback ?? Closure::fromCallable([self::class, 'defaultHeaderHandler']);
            call_user_func($headerCallback, self::headers());
            self::$headerSent = true;
        }
        $callback = self::$callback ?? Closure::fromCallable([self::class, 'defaultHandler']);
        call_user_func($callback, $data);
    }
}
