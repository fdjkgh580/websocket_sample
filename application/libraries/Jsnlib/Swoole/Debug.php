<?php 
namespace Lib\Jsnlib\Swoole;

trait Debug 
{
	public $is_print_command_line = false;

	public function command_line($msg = false)
	{
		if ($this->is_print_command_line === false) return false;
		echo $msg;
	}
}