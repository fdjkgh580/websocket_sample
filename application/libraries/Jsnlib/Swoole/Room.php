<?php 
namespace Lib\Jsnlib\Swoole;

class Room 
{
	// 資料儲存槽
	protected $storage;

	// 所有的聊天室名稱
	protected $box = [];

	// 使用者物件，用來記錄使用者的類別
	protected $uobj;

	// 暫存變數
	protected $temp;

	function __construct()
	{
		$this->uobj = new \Lib\Jsnlib\Swoole\User;
	}

	/**
	 * 開始，會建立表格的大小
	 * @param   $type          table | array
	 * @param   $param['size'] 需要是 2 的次方
	 */
	public function use($type, $param = [])
	{
		if ($type == "table")
		{
			$this->storage = new \Lib\Jsnlib\Swoole\Storage\Table(['size' => $param['size']]);
		} 
		elseif ($type == "array")
		{
			$this->storage = new \Lib\Jsnlib\Swoole\Storage\PHPArray;
		}

	}

	/**
	 * 解碼使用者的數據
	 * @param  string $json_data     使用者的 json 加密數據
	 * @param  string $chatroom_name 房間名稱
	 * @return array                 [接收到 json 解碼後的物件, 房間名稱]
	 */
	public function decode_user_data($json_data, $chatroom_name = "chatroom"): array
	{
		$obj = json_decode($json_data);
		if (!isset($obj->room_id)) return [$obj, false];

		$chatroom_name = "{$chatroom_name}_{$obj->room_id}";
		return [$obj, $chatroom_name];
	}

	/**
	 * 聊天室是否存在
	 * @param    $chatroom_name 房間名稱 
	 */
	public function is_exits($chatroom_name): bool
	{
		return $this->storage->exist($chatroom_name);
	}


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
	 * @param   string 	    chatroom_name
	 * @param   object      ws
	 * @param   int/string  self
	 * @param   array 	    data
	 * @param   string 	    data['type']      into | message
	 * @param   string 	    data['name']      歡迎誰的名字
	 */
	public function welcome($param)
	{
		list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

	 	\Jsnlib\Swoole::push_target(
	 	[
			'ws'           => $param['ws'],
			'target'       => $user_id_box,
			'self'         => $param['self'],
			'is_send_self' => true,
			'data'         => json_encode($param['data'])
	 	]);
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
	 * @return array  [room_id, user_id, userdata]
	 */
	public function leave($ws, $fd): array
	{
		// 使用者離開前在哪個聊天室
		$result = $this->where($fd);
		if ($result === false)
		{
			return true;
		}
		// $result['room_id'];
		// $result['user_id'];
		if ($result == false) throw new \Exception("發生錯誤");

		// 取得使用者資料/名稱
		$userdata = $this->user_get($fd);
		

		// 訊息通知該聊天室的所有人
		$this->buybuy(
		[
			'chatroom_name' => "chatroom_{$result['room_id']}",
			'ws' => $ws,
			'self' => $fd,
			'data' => 
			[
				'type' => 'leave',
				'name' => $userdata['name']
			]
		]);

		// 離開聊天室
		$this->remove_user($fd);

		return 
		[
			'room_id' => $result['room_id'],
			'user_id' => $fd, 
			'userdata' => $userdata
		];
	}


	/**
	 * 將使用者離開聊天室
	 */
	protected function remove_user($fd)
	{
		$this->temp = $fd;


		$this->each(function ($key, $chatroom_name)
		{
			$fd = $this->temp;

			list($chatroom, $user_id_box) = $this->userlist($chatroom_name);

			// 若在陣列中就刪除
			if (in_array($fd, $user_id_box))
			{
				$key = array_search($fd, $user_id_box);
				// echo "KEY: " . $key . "\n";
				array_splice($user_id_box, $key, 1);

			}

			// 重新編碼為 json 後寫入
			$user_encode = json_encode($user_id_box);
			$this->storage->set($chatroom_name, 
			[
				'room_id' => $chatroom['room_id'],
				'user_id' => $user_encode
			]);
		
		});

		unset($this->temp);
	}

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
	 * @return  [int room_id, array user_id]
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

	/**
	 * 記錄使用者
	 * @param   int   user_key  使用者辨識鍵
	 * @param   array user_val  使用者的附加資料
	 */
	public function user_insert($param): bool
	{
		$this->uobj->insert($param['user_key'], $param['user_val']);
		return true;
	}

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
			echo "群組 $chatroom_name 加入第一個使用者 $user_id \n";

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
			echo "群組 $chatroom_name 追加使用者 $user_id \n";

			// 追加使用者
			$this->append_user(
			[
				'chatroom_name' => $chatroom_name,
				'room_id'       => $room_id,
				'user_id'       => $user_id
			]);
		}
	}


	public function get_message($ws, $frame)
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


		if ($obj->type == "join") 
		{
			// 若建立群組
			if ( ! $this->is_exits($chatroom_name)) 
			{
				echo "建立群組 $chatroom_name \n";

				$this->create(
				[
					'chatroom_name' => $chatroom_name,
					'room_id'       => $obj->room_id,
				]);
			}

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