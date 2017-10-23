<?php 
// namespace Lib\Jsnlib\Swoole\Storage;
// /**
//  * 使用 swoole_table 作為儲存體
//  * 不使用 PHP Array 作為儲存，是因為當多線程的時候，彼此無法互通
//  */
// class Connect 
// {
// 	protected $table;

// 	public function __construct($param = [])
// 	{
// 		$param += 
// 		[
// 			'size' => 1024
// 		];

// 		// connect
// 		$this->table = new \swoole_table($param['size']);
// 		$this->table->column('connect_id', \swoole_table::TYPE_STRING, 256);
// 		$this->table->create();
// 		$this->table->set('client', ['connect_id' => false]);
// 	}

// 	// 自動增加連線編號
// 	public function add($connect_id)
// 	{
// 		$datalist = $this->table->get('client');

// 		if (empty($datalist['connect_id']))
// 		{
// 			echo "ADD------------------------------- $connect_id \n";
			
// 			$this->table->set('client', 
// 			[
// 				'connect_id' => json_encode([$connect_id])
// 			]);

// 		}
// 		else 
// 		{
// 			$datalist = $this->table->get('client');
// 			$encode_string = $datalist['connect_id'];
// 			$box = json_decode($encode_string, true);

// 			array_push($box, $connect_id);
// 			$this->table->set('client', 
// 			[
// 				'connect_id' => json_encode($box)
// 			]);
// 		}

		
// 		return true;
// 	}

// 	public function get(): string
// 	{
// 		$result = $this->table->get('client');
// 		return $result['connect_id'];
// 	}

// 	public function remove()
// 	{
		
// 	}
// }