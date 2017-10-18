<?php 

class My
{
	
	protected $box;

	function get()
	{
		$this->box = 123;
		$this->each(function ()
		{
			print_r($this);
		});
	}

	public function each(callable $callback)
	{
		$callback();
	}

}

$my = new My;
$my->get();






die;


// 房間編號
$room_id = new stdClass;
$room_id->a = 'A';
$room_id->b = 'B';


$Jason = ['name' => 'Jason'];
$Tom = ['name' => 'Tom'];
$Lee = ['name' => 'Lee'];

$box = [];

// 進入聊天室 A
$box[$room_id->a][1000] = $Jason;
$box[$room_id->a][2000] = $Tom;

// 進入聊天室 B
$box[$room_id->b][3000] = $Lee;

print_r($box);