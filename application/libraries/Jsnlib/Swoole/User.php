<?php 
namespace Lib\Jsnlib\Swoole;

class User 
{
	protected $box;

	function __construct()
	{
		$this->box = [];
	}

	/**
	 * 新增
	 * 紀錄範例
	 * $this->box[777] => [ 'name' => 'Chang' , 'age' => 30]
	 * 
	 * @param   user_id
	 * @param   data
	 *
	 * 
	 */
	public function insert($user_id, array $data = []): bool
	{
		$this->box[$user_id] = $data;

		return true;
	}

	/**
	 * 取得使用者
	 * @param  int $user_id 使用者編號
	 */
	public function get($user_id)
	{
		return $this->box[$user_id];
	}
}