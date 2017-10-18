<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Websocket extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->chat_model = new \Model\Chat;
	}

	public function run()
	{

		$ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

		$GLOBALS['room'] = new \Lib\Jsnlib\Swoole\Room;

		$GLOBALS['room']->use('array');

		$ws->on('open', function ($ws, $request) {
			
			echo "進入者編號：{$request->fd}\n";

		});

		$ws->on('message', function ($ws, $frame) {

			echo "收到進入者 {$frame->fd} 訊息: {$frame->data} \n";

			$room =& $GLOBALS['room'];
			$room->get_message($ws, $frame);

			$data_decode = json_decode($frame->data, true);
			$message = isset($data_decode['message']) ? $data_decode['message'] : null;
			
			// 寫入DB
			$last_insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
			[
			    'chat_room_id' => $data_decode['room_id'],
			    'chat_message' => $message,
			    'chat_connect_id' => $frame->fd,
			    'chat_option' => $frame->data
			]));
		});

		$ws->on('close', function ($ws, $fd) {

			echo "離開者編號：{$fd}\n";

			$room =& $GLOBALS['room'];

			$result = $room->leave($ws, $fd);

			// 寫入DB
			$last_insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
			[
			    'chat_room_id' => $result['room_id'],
			    'chat_message' => null,
			    'chat_connect_id' => $result['user_id'],
			    'chat_option' => json_encode([
			    	'type' => 'leave',
			    	'name' => $result['userdata']['name']
			    ])
			]));

		});

		$ws->start();

	}


	// 測試寫入 DB
	public function db()
	{
		echo "TEST DB \n\n\n";

		
	}
}
