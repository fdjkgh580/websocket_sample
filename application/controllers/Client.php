<?php
defined('BASEPATH') OR exit('No direct script access allowed');
// Client 端用戶測試
class Client extends CI_Controller {

	public function index()
	{
		$this->load->view('form');		
	}

	public function room()
	{
		$this->load->view('room');
	}

	public function run()
	{
		$cli = new swoole_http_client('52.221.147.32', 8080);
		$cli->on('connect', function () {
			echo "連接成功 \n";
		});

		$cli->on('close', function (){
			echo "連接關閉 \n\n\n";
		});

		$cli->on('message', function ($_cli, $frame) {
			echo "收到訊息 \n";
		    var_dump($frame->data);

		    // 關閉連接
		    // $_cli->close();
		});

		$cli->upgrade('/', function ($cli) {
			// echo "使用 WebSocket 發送 \n";
		    $cli->push(json_encode(['say' => 'hello']));
		});
	}

	public function test()
	{
		$this->a = 0;
		die;
		swoole_timer_tick(1, function(){
			
			echo "{$this->a}\n";

			$cli = new swoole_http_client('52.221.147.32', 8080);

			$cli->on('connect', function () {
				// echo "連接成功 \n";
			});

			$cli->on('close', function (){
				// echo "連接關閉 \n";
			});

			$cli->on('message', function ($_cli, $frame) {
				echo "收到訊息 \n";
			    // var_dump($frame->data);

			    // 關閉連接
			    $_cli->close();
			});

			$cli->upgrade('/', function ($cli) {
				// echo "使用 WebSocket 發送 \n";
			    $cli->push(json_encode(['say' => 'hello']));
			});

			if ($this->a % 10 === 0) 
			{
				echo "{$this->a}\n";
			}

			$this->a += 1;
		});

		die;

		
	}



}

