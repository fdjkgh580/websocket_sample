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
	$obj = $room->decode_user_data($frame->data);

	// 加入群組
	if ($obj->type == "join") 
	{
		// 若建立群組
		if (empty( $GLOBALS['table']->get($chatroom_name)))
		{
			echo "建立群組 $chatroom_name \n";

			$GLOBALS['tablebox'][] = $chatroom_name;

			$GLOBALS['table']->set($chatroom_name, 
			[
				'room_id' => $obj->room_id
			]);
		}

		$chatroom = $GLOBALS['table']->get($chatroom_name);

		// 若群組沒有人
		if (empty($chatroom['user_id']))
		{
			echo "群組 $chatroom_name 加入第一個使用者 $frame->fd \n";

			$user_id = json_encode([$frame->fd]);

			$GLOBALS['table']->set($chatroom_name, 
			[
				'room_id' => $obj->room_id,
				'user_id' => $user_id
			]);
		}
		// 追加使用者
		else 
		{
			echo "群組 $chatroom_name 追加使用者 $frame->fd \n";

			$user_id = json_decode($chatroom['user_id'], true);
			array_push($user_id, $frame->fd);
			$json_user_id = json_encode($user_id);

			$GLOBALS['table']->set($chatroom_name, 
			[
				'room_id' => $obj->room_id,
				'user_id' => $json_user_id
			]);
		}

		$chatroom = $GLOBALS['table']->get($chatroom_name);
		$users = json_decode($chatroom['user_id']);

	 	\Jsnlib\Swoole::push_target(
		[
			'ws'           => $ws,
			'target'       => $users,
			'self'         => $frame->fd,
			'is_send_self' => true,
			'data'         => json_encode(
			[
				'type' => 'into', 
				'name' => $obj->name
			])
	 	]);
	}
	// 發送訊息
	else 
	{
		echo "使用者 {$frame->fd} 在群組 {$chatroom_name} 發送: \n\n {$frame->data} \n\n";
		
		$chatroom = $GLOBALS['table']->get($chatroom_name);
		$users = json_decode($chatroom['user_id']);

	 	\Jsnlib\Swoole::push_target(
		[
			'ws'           => $ws,
			'target'       => $users,
			'self'         => $frame->fd,
			'is_send_self' => false,
			'data'         => $frame->data
	 	]);
	}


});


// 今天 WebSocket 連接關閉事件
$ws->on('close', function ($ws, $fd) {
	echo "離開者編號：{$fd}\n";

	foreach ($GLOBALS['tablebox'] as $chatroom_name)
	{
		$chatroom = $GLOBALS['table']->get($chatroom_name);
		$users = json_decode($chatroom['user_id'], true);

		if (in_array($fd, $users))
		{
			$key = array_search($fd, $users);
			echo "KEY: " . $key . "\n";
			unset($users[$key]);
		}
		
		$user_encode = json_encode($users);
		$GLOBALS['table']->set($chatroom_name, 
		[
			'room_id' => $chatroom['room_id'],
			'user_id' => $user_encode
		]);
	}

});

$ws->start();