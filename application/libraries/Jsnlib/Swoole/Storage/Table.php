<?php 
namespace Lib\Jsnlib\Swoole\Storage;
/**
 * 使用 swoole_table 作為儲存體
 * 不使用 PHP Array 作為儲存，是因為當多線程的時候，彼此無法互通
 */
class Table 
{
	protected $table;

	public function __construct($param = [])
	{
		$param += 
		[
			'size' => 1024
		];

		$this->table = new \swoole_table($param['size']);

		// table
		$this->table->column('room_id', \swoole_table::TYPE_INT);
		$this->table->column('user_id', \swoole_table::TYPE_STRING, 256);
		$this->table->create();
		// print_r($this->table);die;
	}

	public function exist($key)
	{
		return $this->table->exist($key);
	}

	public function set($key, $val)
	{
		return $this->table->set($key, $val);
	}

	public function get($key)
	{
		return $this->table->get($key);
	}

}