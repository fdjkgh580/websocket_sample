<?php
require_once 'vendor/autoload.php'; 



// 建立 websocket 物件，監聽 0.0.0.0:8080 連接埠
$ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

// 監聽 WebSocket 連接打開事件
$ws->on('open', function ($ws, $request) {
	echo "進入編號：{$request->fd}\n";
});

// 監聽 WebSocket 訊息事件
$ws->on('message', function ($ws, $frame) {

 	\Jsnlib\Swoole::push_all([
		'ws'           => $ws,
		'self'         => $frame->fd,
		'is_send_self' => false,
		'data'         => $frame->data
 	]);

});

// 今天 WebSocket 連接關閉事件
$ws->on('close', function ($ws, $fd) {
	echo "離開編號：{$fd}\n";
});

$ws->start();