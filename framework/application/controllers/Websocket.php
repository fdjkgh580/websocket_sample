<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Websocket extends CI_Controller {

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

		});

		$ws->on('close', function ($ws, $fd) {

			echo "離開者編號：{$fd}\n";

			$room =& $GLOBALS['room'];

			$room->leave($ws, $fd);

		});

		$ws->start();

	}
}
