<?php 

class Chat 
{
	public $open;

	function __construct()
	{
		$this->defined_anonymous();
		$this->say($this->open);
		$this->hello($this->open);
	}

	public function say($callback)
	{
		$name = 'Chang';
		$callback($name);
	}

	public function hello($callback)
	{
		$callback('Tom');
	}

	protected function defined_anonymous()
	{
		$this->open = function ($name){
			return $this->open($name);
		};
	}

	protected function open($name)
	{
		echo $name . "<br>";
	}
}


$TEST = function ($name)
{
	echo $name;
};

$chat = new Chat;
