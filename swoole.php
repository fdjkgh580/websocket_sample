<?php
require_once 'vendor/autoload.php'; 

// 1. 建立 websocket 物件，監聽 0.0.0.0:8080 連接埠
$ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

$GLOBALS['room'] = new \Jsnlib\Swoole\Room;
$GLOBALS['table'] = $GLOBALS['room']->start(1024);

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
	list($obj, $chatroom_name) = $room->decode_user_data($frame->data);


	if ($obj->type == "join") 
	{
		// 若建立群組
		if ( ! $room->is_exits($chatroom_name)) 
		{
			echo "建立群組 $chatroom_name \n";

			$room->create(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $obj->room_id,
			]);
		}

		// 若群組沒有人
		if ($room->is_no_one($chatroom_name))
		{
			echo "群組 $chatroom_name 加入第一個使用者 $frame->fd \n";

			// 加入第一個使用者
			$room->first_user(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $obj->room_id,
				'user_id'       => $frame->fd
			]);

			// 記錄使用者
			$room->user_insert(
			[
				'user_key' => $frame->fd,
				'user_val' => 
				[
					'name' => $obj->name
				]
			]);
		}
		else 
		{
			echo "群組 $chatroom_name 追加使用者 $frame->fd \n";

			// 追加使用者
			$room->add_user(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $obj->room_id,
				'user_id'       => $frame->fd
			]);
		}

		// 提醒使用者
		$room->welcome(
		[
			'chatroom_name' => $chatroom_name,
			'ws' => $ws,
			'self' => $frame->fd,
			'data' => [
				'type' => 'into',
				'name' => $obj->name
			]
		]);

	}
	elseif ($obj->type == "message")
	{
		// 發送給場內的所有使用者
		$room->push_message(
		[
			'chatroom_name' => $chatroom_name,
			'ws'            => $ws,
			'self'          => $frame->fd,
			'data'          => $frame->data
		]);
	}

});


// 今天 WebSocket 連接關閉事件
$ws->on('close', function ($ws, $fd) {
	echo "離開者編號：{$fd}\n";

	$room =& $GLOBALS['room'];

	// 使用者離開前在哪個聊天室
	$result = $room->where($fd);
	if ($result == false) throw new \Exception("發生錯誤");
	// $result['room_id'];
	// $result['user_id'];

	// 取得使用者資料/名稱
	$userdata = $room->user_get($fd);
	
	echo "Leave -------- \n\n";
	print_r($userdata['name']);
	echo "\n";
	echo "Leave -------- \n\n";

	

	// // 訊息通知該聊天室的所有人
	// $room->buybuy(
	// [
	// 	'chatroom_name' => '',
	// 	'ws' => $ws,
	// 	'self' => $fd,
	// 	'data' => 
	// 	[
	// 		'name' => $userdata['name']
	// 	]
	// ]);

	// 離開聊天室
	$room->leave($fd);

});

$ws->start();