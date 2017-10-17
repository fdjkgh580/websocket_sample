<?php
require_once 'vendor/autoload.php'; 

// 1. 建立 websocket 物件，監聽 0.0.0.0:8080 連接埠
$ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

$GLOBALS['room'] = new \Jsnlib\Swoole\Room;
$GLOBALS['room']->start(1024);

// 3.1 所有的聊天室名稱
// $tablebox = [];

// 4. 監聽 WebSocket 連接打開事件
$ws->on('open', function ($ws, $request) {
	
	echo "進入者編號：{$request->fd}\n";

});

// 監聽 WebSocket 訊息事件
$ws->on('message', function ($ws, $frame) {

	echo "收到進入者 {$frame->fd} 訊息: {$frame->data} \n";

	$room =& $GLOBALS['room'];
	$room->get_message($ws, $frame);

});


// 今天 WebSocket 連接關閉事件
$ws->on('close', function ($ws, $fd) {

	echo "離開者編號：{$fd}\n";

	$room =& $GLOBALS['room'];

	$room->leave($ws, $fd);

});

$ws->start();