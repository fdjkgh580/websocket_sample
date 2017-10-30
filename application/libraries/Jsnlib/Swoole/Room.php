<?php 
namespace Lib\Jsnlib\Swoole;

class Room 
{
	use Debug;

	// 資料儲存槽
	protected $storage;

	// 所有的聊天室名稱
	protected $box = [];

	// 暫存變數
	protected $temp;

	protected $connect_model;
	protected $room_model;
	protected $user_model;
	protected $chat_model;

	function __construct()
	{
		// $this->storage = new \Lib\Jsnlib\Swoole\Storage\Table(['size' => 1024 * 200]);
		$this->storage = new \Lib\Jsnlib\Swoole\Storage\MySQL;
		$this->debug(false);

		$this->connect_model = new \Model\Connect;
		$this->room_model = new \Model\Room;
		$this->user_model = new \Model\User;
		$this->chat_model = new \Model\Chat;
		$this->init();
	}

	public function init()
	{
		$this->room_model->clean();
		$this->user_model->clean();
		$this->connect_model->clean();
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
			$insert_id = $this->connect_model->insert(new \Jsnlib\Ao(
			[
				'connect_user_id' => $param->user_id,
				'connect_ip' => $param->ip
			]));
			if (empty($insert_id))
				$this->command_line("錯誤，無法增加連接紀錄，使用者 {$param->user_id}\n");
		}
		elseif ($param->action == "delete") 
		{
			$num = $this->connect_model->delete(new \Jsnlib\Ao(
			[
			    'connect_user_id' => $param->user_id
			]));
			if ($num == 0)
				$this->command_line("錯誤，無法刪除連接紀錄，使用者 {$param->user_id}\n");
		}
		else 
		{
			die('action 參數錯誤\n');
		}
		
	}



	/**
	 * 所有使用者
	 * @param  *room_id  房間編號
	 */
	protected function all_user($param = false): array
	{
		// 若沒有指定聊天室群組編號
		if ($param === false)
		{
			$connectlist = $this->connect_model->list_all();

			$users = [];
			foreach ($connectlist as $connectinfo) 
			{
				$users[] = $connectinfo->connect_user_id;
			}
		}
		else 
		{
			// 取得與自己相同的坊間所有使用者編號
			$roomlist = $this->room_model->list_all(new \Jsnlib\Ao(
			[
			    'room_key_id' => $param['room_id']
			]));

			$users = [];
			foreach ($roomlist as $roominfo)
			{
				$users[] = $roominfo['connect_user_id'];
			}
		}

		return $users;
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
		$users = $this->all_user(
		[
			'room_id' => $param['room_id']
		]);
		$mixuser = implode(",", $users);

		$this->command_line("使用者 {$param['user_id']} 發送歡迎訊息給成員： $mixuser \n");


		\Jsnlib\Swoole::push_target(
		[
			'ws' => $param['ws'],
			'target' => $users,
			'self' => $param['user_id'],
			'is_send_self' => true,
			'data' => json_encode($param['data'])
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
		$this->command_line(" > > > > Leave \n");

		// 查詢使用者名稱資料
		$userinfo = $this->user_model->one(new \Jsnlib\Ao(
		[
		    'connect_user_id' => $fd
		]));
		if ($userinfo === false)
			$this->command_line("找不到使用者編號 {$fd} \n");

		// 刪除使用者
		$result = $this->user_model->delete(new \Jsnlib\Ao(
		[
		    'connect_user_id' => $fd
		]));
		if ($result == 0)
			$this->command_line("錯誤！使用者 {$fd} 紀錄未刪除\n");


		// 查詢使用者在哪個群組
		$roominfo = $this->room_model->one(new \Jsnlib\Ao(
		[
		    'connect_user_id' => $fd
		]));

		// 沒有在群組
		if ($roominfo === false) return false;

		// 發送離開訊息
		// swoole 已把使用者編號設定為 closed 後才觸發 onClose() ，所以下方不可發送給自己
		$users = $this->push2users(
		[
			'ws' => $ws,
			'room_id' => $roominfo->room_key_id,
			'user_id' => $fd,
			'is_send_self' => false,
			'data' => json_encode(
			[
				'type' => 'leave',
				'name' => $userinfo->user_name
			])
		]);
		if (count($users) > 0)
			$this->command_line("使用者 {$fd} 發送離開訊息到聊天室 {$roominfo->room_key_id}，有成員: " . implode(",", $users) . "\n");
		else
			$this->command_line("使用者 {$fd} 未發送離開訊息到聊天室 {$roominfo->room_key_id}，因為沒有成員\n");

		// 離開群組
		$result = $this->room_model->leave(new \Jsnlib\Ao(
		[
		    'connect_user_id' => $fd
		]));
		if ($result < 1)
			$this->command_line("錯誤！使用者 {$fd} 沒有離開聊天室\n");
		else 
			$this->command_line("使用者 {$fd} 離開聊天室 {$roominfo->room_key_id} \n");

		// 寫入聊天紀錄：離開
		$insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
		[
		    'room_key_id' => $roominfo->room_key_id,
		    'chat_message' => null,
		    'connect_user_id' => $fd,
		    'chat_option' => json_encode
		    ([
		        'type' => 'leave',
		        'name' => $userinfo->user_name
		    ])
		]));
		if (empty($insert_id))
			$this->command_line("寫入離開紀錄錯誤，使用者 {$fd}\n");

		return 
		[
			'room_id' => $roominfo['room_key_id'],
			'user_id' => $fd, 
			'userinfo' => 
			[
				'user_id' => $userinfo->user_id,
				'connect_user_id' => $userinfo->connect_user_id,
				'name' => $userinfo->user_name
			]
		];
	}

	/**
	 * 推送訊息到聊天室的所有人
	 * @param ws
	 * @param room_id
	 * @param user_id
	 * @param *is_send_self 是否包含自己？是
	 * @param data
	 * @return users
	 */
	public function push2users(array $param): array
	{
		$param += 
		[
			'is_send_self' => true
		];

		$users = $this->all_user(
		[
			'room_id' => $param['room_id']
		]);

		\Jsnlib\Swoole::push_target(
		[
			'ws' => $param['ws'],
			'target' => $users,
			'self' => $param['user_id'],
			'is_send_self' => $param['is_send_self'],
			'data' => $param['data']
		]);

		return $users;
	}

	// 取得訊息後立刻發送出去
	public function get_message_and_send($ws, $frame)
	{
		/**
		 * $userdata[type, name, room_id]
		 */
		$userdata = json_decode($frame->data, true);

		// 若沒有房間編號，代表一般文字訊息，那發送給所有使用者
		if (empty($userdata['room_id']))
		{
			$users = $this->all_user();

			if (count($users) == 0) return true;

			$this->command_line("使用者 {$frame->fd} 發送一般訊息給所有人: " . implode(",", $users) . "\n");

			\Jsnlib\Swoole::push_target(
			[
				'ws' => $ws,
				'target' => $users,
				'self' => $frame->fd,
				'is_send_self' => true,
				'data' => $frame->data
			]);

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

			// 記錄使用者名稱
			$insert_id = $this->user_model->insert(new \Jsnlib\Ao(
			[
			    'connect_user_id' => $frame->fd,
			    'user_name' => $userdata['name']
			]));
			if (empty($insert_id))
				$this->command_line("紀錄使用者名稱錯誤：使用者 {$frame->fd}\n");

			// 紀錄進入的房間
			$insert_id = $this->room_model->insert(new \Jsnlib\Ao(
			[
			    'room_key_id' => $userdata['room_id'],
			    'connect_user_id' => $frame->fd,
			]));
			if (empty($insert_id))
				$this->command_line("紀錄進入的房間錯誤：使用者 {$frame->fd} 群組 {$userdata['room_id']}\n");

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

			// 寫入聊天紀錄: 歡迎
			$insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
			[
			    'room_key_id' => $userdata['room_id'],
			    'chat_message' => null,
			    'connect_user_id' => $frame->fd,
			    'chat_option' => json_encode
			    ([
			        'type' => 'join',
			        'name' => $userdata['name']
			    ])
			]));
			if (empty($insert_id))
				$this->command_line("寫入歡迎紀錄發生錯誤，使用者：{$frame->fd}\n");

		}
		// 發送給場內的所有使用者
		elseif ($userdata['type'] == "message")
		{
			$users = $this->push2users(
			[
				'ws' => $ws,
				'room_id' => $userdata['room_id'],
				'user_id' => $frame->fd,
				'data' => $frame->data
			]);
			if (count($users) > 0)
				$this->command_line("使用者 {$frame->fd} 發送訊息給成員: " . implode(",", $users) . "\n");

			// 寫入聊天紀錄: 聊天訊息
			$insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
			[
			    'room_key_id' => $userdata['room_id'],
			    'chat_message' => json_decode($frame->data, true)['message'],
			    'connect_user_id' => $frame->fd,
			    'chat_option' => $frame->data
			]));
			if (empty($insert_id))
				$this->command_line("寫入聊天紀錄錯誤，使用者 {$frame->fd}\n");
		}
		else
		{
			// 一般訊息，不寫入聊天紀錄
		}
	}

}