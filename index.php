<?php 


class Jsn {

	public function get()
	{
		$this->box = [];

		$this->collection(function ($key, $val)
		{
			$this->box[] = $val;
		});

		print_r($this->box);
	}

	public function collection(callable $callback)
	{
		foreach (['A', 'B', 'C'] as $key => $val)
		{
			$callback($key, $val);
		}
	}
}

$jsn = new Jsn;
$jsn->get();