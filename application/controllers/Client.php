<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client extends CI_Controller {

	public function index()
	{
		$this->load->view('form');		
	}

	public function room()
	{
		$this->load->view('room');
	}

	public function test()
	{
		$cli = new swoole_http_client('52.221.147.32', 8080);

		$cli->on('connect', function () {
			echo "連接成功 \n";
		});

		$cli->on('close', function (){
			echo "連接關閉 \n";
		});

		$cli->on('message', function ($_cli, $frame) {
			echo "收到訊息 \n";
		    var_dump($frame->data);

		    // 關閉連接
		    $_cli->close();
		});

		$cli->upgrade('/', function ($cli) {
			echo "使用 WebSocket 發送 \n";
		    $cli->push(json_encode(['say' => 'hello']));
		});
	}

}

