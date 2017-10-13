<?php 
namespace Jsnlib\Swoole;

class Room 
{
	// swoole_table() 存放
	protected $table;

	// 所有的聊天室名稱
	protected $box = [];

	public function start($size = 1024)
	{
		// 2. 建立表格的大小，需要是 2 的次方
		$this->table = new \swoole_table($size);

		// 3. 建立欄位
		$this->table->column('room_id', \swoole_table::TYPE_INT);
		$this->table->column('user_id', \swoole_table::TYPE_STRING, 256);
		$this->table->create();

		return $this->table;
	}

	/**
	 * 解碼使用者的數據
	 * @param  [type] $json_data     使用者的 json 加密數據
	 * @param  string $chatroom_name 房間名稱
	 * @return array                 [接收到 json 解碼後的物件, 房間名稱]
	 */
	public function decode_user_data($json_data, $chatroom_name = "chatroom"): array
	{
		$obj = json_decode($json_data);
		$chatroom_name = "{$chatroom_name}_{$obj->room_id}";

		return [$obj, $chatroom_name];
	}

	/**
	 * 聊天室是否存在
	 * @param    $chatroom_name 房間名稱 
	 */
	public function is_exits($chatroom_name): bool
	{
		return $this->table->exist($chatroom_name);
	}


	/**
	 * 有任何人在房間嗎？
	 * @param  string   $chatroom_name 
	 * @return boolean                
	 */
	public function is_no_one($chatroom_name): bool
	{
		$chatroom = $this->table->get($chatroom_name);
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

		$this->table->set($param['chatroom_name'], 
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
	public function add_user($param)
	{
		list($chatroom, $user_id_box) = $this->userlist($param['chatroom_name']);

		// 在最後加入新的使用者編號
		array_push($user_id_box, $param['user_id']);
		
		// 編碼為 json
		$user_id_encode = json_encode($user_id_box);

		$this->table->set($param['chatroom_name'], 
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
		
		$this->table->set($param['chatroom_name'], 
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
	 * 離開聊天室
	 */
	public function leave($fd)
	{
		foreach($this->box as $chatroom_name)
		{
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
			$this->table->set($chatroom_name, 
			[
				'room_id' => $chatroom['room_id'],
				'user_id' => $user_encode
			]);

			// $chatroom = $this->table->get($chatroom_name);
		}
	}

	/**
	 * 取得指定聊天室內的所有使用者編號
	 * @param   string $chatroom_name   聊天室名稱
	 * @return  array  [array 聊天室, array 使用者編號]
	 */
	protected function userlist($chatroom_name, $chatroom = false): array
	{
		$chatroom = $this->table->get($chatroom_name);
		
		if (empty($chatroom['user_id'])) return [$chatroom, false];

		$user_id_box = json_decode($chatroom['user_id'], true);

		return [$chatroom, $user_id_box];
	}
}