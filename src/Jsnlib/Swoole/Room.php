<?php 
namespace Jsnlib\Swoole;

class Room 
{
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
}