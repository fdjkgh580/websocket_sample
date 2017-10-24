<?php 
namespace Lib\Jsnlib\Swoole;

class Room 
{
	use Debug;

	// 資料儲存槽
	protected $storage;

	// 所有的聊天室名稱
	protected $box = [];

	// 使用者物件，用來記錄使用者的類別
	protected $uobj;

	// 暫存變數
	protected $temp;

	protected $connect_model;
	protected $room_model;
	protected $user_model;

	function __construct()
	{
		$this->uobj = new \Lib\Jsnlib\Swoole\User;
		// $this->storage = new \Lib\Jsnlib\Swoole\Storage\Table(['size' => 1024 * 200]);
		$this->storage = new \Lib\Jsnlib\Swoole\Storage\MySQL;
		$this->debug(false);

		$this->connect_model = new \Model\Connect;
		$this->room_model = new \Model\Room;
		$this->user_model = new \Model\User;
	}

	public function debug($bool = false)
	{
		$this->is_print_command_line = $bool;
	}

	/**
	 * 連接
	 * @param   $param->action     add | remove
	 * @param   $param->user_id    使用者連線編號
	 * @param   $param->ip         連線 IP (action = add)
	 */
	public function connect($param)
	{
		if (!isset($param->action)) die("須要參數 action");
		
		if ($param->action == "add") 
		{
			return $this->connect_model->insert(new \Jsnlib\Ao(
			[
				'connect_user_id' => $param->user_id,
				'connect_ip' => $param->ip
			]));
		}
		elseif ($param->action == "delete") 
		{
			return $this->connect_model->delete(new \Jsnlib\Ao(
			[
			    'connect_user_id' => $param->user_id
			]));
		}
		else 
			die('action 參數錯誤\n');
		
	}


	

	// /**
	//  * 聊天室是否存在
	//  * @param    $chatroom_name 房間名稱 
	//  */
	// public function is_exits($chatroom_name): bool
	// {
	// 	return $this->storage->exist($chatroom_name);
	// }


	/**
	 * 有任何人在房間嗎？
	 * @param  string   $chatroom_name 
	 * @return boolean                
	 */
	public function is_no_one($chatroom_name): bool
	{
		$chatroom = $this->chatroom($chatroom_name);
		return empty($chatroom['user_id']);
	}

	/**
	 * 加入第一個使用者
	 * @param   chatroom_name
	 * @param   room_id
	 * @param   user_id
	 */
	public function first_user($param)
	{
		$user_id_encode = json_encode([$param['user_id']]);

		$this->storage->set($param['chatroom_name'], 
		[
			'room_id' => $param['room_id'],
			'user_id' => $user_id_encode
		]);
	}

	/**
	 * 追加使用者
	 * @param   chatroom_name
	 * @param   room_id
	 * @param   user_id
	 */
	public function append_user($param)
	{
		list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

		// 在最後加入新的使用者編號
		array_push($user_id_box, $param['user_id']);
		
		// 編碼為 json
		$user_id_encode = json_encode($user_id_box);

		$this->storage->set($param['chatroom_name'], 
		[
			'room_id' => $param['room_id'],
			'user_id' => $user_id_encode
		]);
	}


	/**
	 * 建立房間
	 * @param   chatroom_name
	 * @param   room_id
	 */
	public function create($param)
	{
		$this->box[] = $param['chatroom_name'];
		
		$this->storage->set($param['chatroom_name'], 
		[
			'room_id' => $param['room_id'],
		]);
	}

	/**
	 * 發送歡迎訊息
	 * @param ws
	 * @param user_id
	 * @param room_id
	 * @param data[type, name]
	 */
	public function welcome(array $param)
	{
		$roomlist = $this->room_model->list_all(new \Jsnlib\Ao(
		[
		    'room_key_id' => $param['room_id']
		]));

		$target = [];
		foreach ($roomlist as $roominfo)
		{
			$target[] = $roominfo['room_user_id'];
		}
		$mixuser = implode(",", $mixuser);

		$this->command_line("使用者 {$param['user_id']} 發送歡迎訊息給成員： $mixuser \n");


		\Jsnlib\Swoole::push_target(
		[
			'ws' => $param['ws'],
			'target' => $target,
			'self' => $param['user_id'],
			'is_send_self' => true,
			'data' => json_encode($param['data'])
		]);

		// list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

		// $mix_user = implode(",", $user_id_box);
		// $this->command_line("使用者 {$param['self']} 發送歡迎訊息到成員 {$mix_user} \n");

	 // 	\Jsnlib\Swoole::push_target(
	 // 	[
		// 	'ws'           => $param['ws'],
		// 	'target'       => $user_id_box,
		// 	'self'         => $param['self'],
		// 	'is_send_self' => true,
		// 	'data'         => json_encode($param['data'])
	 // 	]);
	}

	/**
	 * 發送訊息到聊天室內所有的成員
	 * @param   string 	    chatroom_name
	 * @param   object      ws
	 * @param   int/string  self
	 * @param   array 	    data
	 */
	public function push_message($param)
	{
		list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

		$mix_user = implode(",", $user_id_box);
		$this->command_line("發送訊息到聊天室內所有的成員 {$mix_user} \n");

	 	\Jsnlib\Swoole::push_target(
		[
			'ws'           => $param['ws'],
			'target'       => $user_id_box,
			'self'         => $param['self'],
			'is_send_self' => false,
			'data'         => $param['data']
	 	]);
	}

	/**
	 * 離開
	 * @param  object $ws websocket
	 * @param  int    $fd 使用者編號
	 * @return array  false/[room_id, user_id, userdata]
	 */
	public function leave($ws, $fd)
	{
		// 在哪個群組
		$roominfo = $this->room_model->one(new \Jsnlib\Ao(
		[
		    'room_user_id' => $fd
		]));

		// 沒有在群組
		if ($roominfo === false) return false;


		// 離開群組
		$result = $this->room_model->leave(new \Jsnlib\Ao(
		[
		    'room_user_id' => $fd
		]));
		if ($result == 0)
			$this->command_line("錯誤！使用者 {$fd} 沒有離開聊天室\n");

		// 刪除使用者紀錄
		$result = $this->user_model->delete(new \Jsnlib\Ao(
		[
		    'user_key_id' => $fd
		]));
		if ($result == 0)
			$this->command_line("錯誤！使用者 {$fd} 紀錄未刪除\n");


		return 
		[
			'room_id' => $roominfo['room_key_id'],
			'user_id' => $fd, 
			'userdata' => $userdata
		];
	}


	// /**
	//  * 將使用者離開聊天室
	//  */
	// protected function remove_user($fd)
	// {
	// 	$this->temp = $fd;


	// 	$this->each(function ($key, $chatroom_name)
	// 	{
	// 		$fd = $this->temp;

	// 		list($chatroom, $user_id_box) = $this->userlist($chatroom_name);

	// 		// 若在陣列中就刪除
	// 		if (in_array($fd, $user_id_box))
	// 		{
	// 			$key = array_search($fd, $user_id_box);
	// 			// echo "KEY: " . $key . "\n";
	// 			array_splice($user_id_box, $key, 1);

	// 		}

	// 		// 重新編碼為 json 後寫入
	// 		$user_encode = json_encode($user_id_box);
	// 		$this->storage->set($chatroom_name, 
	// 		[
	// 			'room_id' => $chatroom['room_id'],
	// 			'user_id' => $user_encode
	// 		]);
		
	// 	});

	// 	unset($this->temp);
	// }

	/**
	 * 取得指定聊天室內的所有使用者編號
	 * @param   string $chatroom_name   聊天室名稱
	 * @return  array  [array 聊天室, array 使用者編號]
	 */
	public function userlist($chatroom_name): array
	{
		$chatroom = $this->chatroom($chatroom_name);
		
		if (empty($chatroom['user_id'])) return [$chatroom, false];

		$user_id_box = json_decode($chatroom['user_id'], true);

		return [$chatroom, $user_id_box];
	}


	/**
	 * 取得聊天室
	 * @param   $chatroom_name 
	 */
	public function chatroom($chatroom_name = false): array
	{
		if ($chatroom_name !== false) return $this->storage->get($chatroom_name);

		$this->collection = [];

		$this->each(function ($key, $chatroom_name)
		{
			$this->collection[] = $this->storage->get($box_chatroom_name);
		});

		$c = $this->collection;
		unset($this->collection);

		return $c;
	}


	/**
	 * 搜尋使用者在哪個聊天室
	 * @param   fd
	 * @return  false/[int room_id, array user_id]
	 */
	public function where($fd)
	{
		foreach ($this->map() as $key => $chatroom)
		{
			if ($this->is_in_room($fd, $chatroom) == false) continue;

			return $chatroom;
		}

		return false;
	}

	/**
	 * 使用者是否在某個房間內？
	 * @param    $fd       
	 * @param    $chatroom 
	 */
	public function is_in_room($fd, array $chatroom): bool
	{
		return in_array($fd, $chatroom['user_id']);
	}

	/**
	 * 取得所有房間與內部的使用者編號
	 */
	public function map(): array
	{
		$this->temp = [];

		$this->each(function ($key, $chatroom_name)
		{
			$result = $this->storage->get($chatroom_name);

			$this->temp[] = 
			[
				'room_id' => $result['room_id'],
				'user_id' => json_decode($result['user_id'], true)
			];
		});

		$box = $this->temp;
		unset($this->temp);

		return $box;
	}

	/**
	 * 取出所有聊天室
	 * @param  callable $callback(鍵, 聊天室名稱)
	 */
	protected function each(callable $callback)
	{
		foreach ($this->box as $key => $chatroom_name)
		{
			$callback($key, $chatroom_name);
		}

		return $this;
	}

	// /**
	//  * 記錄使用者
	//  * @param   int   user_key  使用者辨識鍵
	//  * @param   array user_val  使用者的附加資料
	//  */
	// public function user_insert($param): bool
	// {
	// 	$this->uobj->insert($param['user_key'], $param['user_val']);
	// 	return true;
	// }

	public function user_get($user_key)
	{
		return $this->uobj->get($user_key);
	}

	/**
	 * @param   string 	    chatroom_name
	 * @param   object      ws
	 * @param   int/string  self
	 * @param   array 	    data
	 * @param   string 	    data['name']      誰離開了？
	 */
	public function buybuy($param)
	{
		list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

	 	\Jsnlib\Swoole::push_target(
	 	[
			'ws'           => $param['ws'],
			'target'       => $user_id_box,
			'self'         => $param['self'],
			'is_send_self' => false,
			'data'         => json_encode($param['data'])
	 	]);
		// echo "Leave -------- \n\n";
		// print_r($param);
		// echo "\n";
		// echo "Leave -------- \n\n";

	}


	public function add_user($chatroom_name, $room_id, $user_id)
	{
		// 若群組沒有人
		if ($this->is_no_one($chatroom_name))
		{
			$this->command_line("群組 $chatroom_name 加入第一個使用者 $user_id \n");

			// 加入第一個使用者
			$this->first_user(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $room_id,
				'user_id'       => $user_id
			]);
		}
		else 
		{
			$this->command_line("群組 $chatroom_name 追加使用者 $user_id \n");

			// 追加使用者
			$this->append_user(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $room_id,
				'user_id'       => $user_id
			]);
		}
	}


	public function get_message_and_send2($ws, $frame)
	{
		/**
		 * $userdata[type, name, room_id]
		 */
		$userdata = json_decode($frame->data, true);

		// 若沒有房間編號，代表一般文字訊息，那發送給所有使用者
		if (empty($userdata['room_id']))
		{
			
		}
		else
		{
			if (!isset($userdata['type'])) $this->command_line("須要參數 type \n");
			if (!isset($userdata['room_id'])) $this->command_line("須要參數 room_id \n");
		}

		// 加入群組
		if ($userdata['type'] == "join") 
		{
			$this->command_line("使用者 {$frame->fd} 進入群組 {$userdata['room_id']} \n");

			// 記錄使用者
			$this->user_model->insert(new \Jsnlib\Ao(
			[
			    'user_key_id' => $frame->fd,
			    'user_name' => $userdata['name']
			]));

			// 進入的房間
			$this->room_model->insert(new \Jsnlib\Ao(
			[
			    'room_key_id' => $userdata['room_id'],
			    'room_user_id' => $frame->fd,
			]));

			// 發送歡迎訊息
			$this->welcome(
			[
				'ws' => $ws,
				'user_id' => $frame->fd,
				'room_id' => $userdata['room_id'],
				'data' => 
				[
					'type' => 'into',
					'name' => $userdata['name']
				]
			]);

		}
		// 發送訊息
		elseif ($userdata['type'] == "message")
		{
		}
		else
			$this->command_line("無法識別參數 type \n");


	}


	public function get_message_and_send($ws, $frame)
	{
		list($obj, $chatroom_name) = $this->decode_user_data($frame->data);

		// 若沒有房間編號，代表一般文字訊息，那發送給所有使用者
		if (empty($chatroom_name))
		{
			// 發送給場內的所有使用者
		 	\Jsnlib\Swoole::push_all(
			[
				'ws'           => $ws,
				'self'         => $frame->fd,
				'is_send_self' => true,
				'data'         => $frame->data
		 	]);

			return true;
		}


		if (!isset($obj->type)) $this->command_line("須要參數 type \n");
		if (!isset($obj->room_id)) $this->command_line("須要參數 room_id \n");

		if ($obj->type == "join") 
		{
			// 若建立群組
			if ( ! $this->is_exits($chatroom_name)) 
			{
				$this->command_line("建立群組 $chatroom_name \n");

				$this->create(
				[
					'chatroom_name' => $chatroom_name,
					'room_id'       => $obj->room_id,
				]);
			}

			
			if (!isset($obj->name)) $this->command_line("須要參數 name \n");

			// 將使用者加入群組
			$this->add_user($chatroom_name, $obj->room_id, $frame->fd);

			// 記錄使用者
			$this->user_insert(
			[
				'user_key' => $frame->fd,
				'user_val' => 
				[
					'name' => $obj->name
				]
			]);

			// 提醒使用者
			$this->welcome(
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
			$this->push_message(
			[
				'chatroom_name' => $chatroom_name,
				'ws'            => $ws,
				'self'          => $frame->fd,
				'data'          => $frame->data
			]);
		}
	}
}