<?php 
namespace Jsnlib;

class Swoole 
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