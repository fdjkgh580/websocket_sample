<?php 
namespace Jsnlib\Swoole\Storage;
/**
 * 使用 PHP 原生 Array 作為儲存體
 */
class PHPArray 
{
	protected $box;

	public function __construct($param = [])
	{
	}

	public function exist($key)
	{
		return (isset($this->box[$key]));
	}

	public function set($key, $val)
	{
		$this->box[$key] = $val;
	}

	public function get($key)
	{
		return $this->box[$key];
	}
}