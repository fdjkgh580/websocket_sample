<?php 

class Swoole_helper 
{
	/**
	 * 輔助發送
	 * @param    object $ws           Websocket 物件
	 * @param    int    $self         自己的連線編號
	 * @param    bool   $is_send_self 是否要發送給自己？建議使用 false
	 * @param    string $data         發送的數據
	 */
	static public function push_all($param = []) 
	{
		// 取出所有的連線編號
		foreach ($param['ws']->connections as $fd)
		{
			// 若自己發送的數據，不推送給自己
			if ($fd == $param['self'] and $param['is_send_self'] === false) continue;

			$param['ws']->push($fd, $param['data']);
		}
	}
}



// 建立 websocket 物件，監聽 0.0.0.0:8080 連接埠
$ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

// 監聽 WebSocket 連接打開事件
$ws->on('open', function ($ws, $request) {
	echo "進入編號：{$request->fd}\n";
});

// 監聽 WebSocket 訊息事件
$ws->on('message', function ($ws, $frame) {

 	Swoole_helper::push_all([
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