<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Upload extends CI_Controller {

	protected $savedir;

	public function __construct()
	{
		parent::__construct();
		$this->savedir = FCPATH . "attachment";
	}

	public function index()
	{
		$box = [];
		
		foreach ($_FILES['attachment']['name'] as $key => $name)
		{
			// 處理上傳原始影片
			list($filetype, $filename, $pathfile) = $this->copy($key, $name);

			$box[$key] = 
			[
				'type' => $filetype,
				'name' => $filename,
				'url' => site_url("attachment/{$filename}")
			];
		}

		$this->output->set_content_type('application/json')->set_output(json_encode($box));
	}

	// 檔案類型是否為...
	// 如 $this->is_filetype($key, "video")
	private function is_filetype($key, $checkstr)
	{
		if (strstr($_FILES['attachment']['type'][$key], "{$checkstr}/")) 
			return true;
		return false;
	}

	private function copy($key, $name)
	{

		if ($this->is_filetype($key, "video")) 
		{
			list($filename, $pathfile) = $this->copy_file($key, $name);
			$filetype = "video";
		}
		elseif ($this->is_filetype($key, "image"))
		{
			list($filename, $pathfile) = $this->copy_file($key, $name);
			$filetype = "image";
		}
		else 
		{
			list($filename, $pathfile) = $this->copy_file($key, $name);
			$filetype = "application";
		}


		return [$filetype, $filename, $pathfile];
	}

	// 複製檔案
	private function copy_file($key, $name)
	{
		$secondname = $this->secondname($name);
		$filename = uniqid(date("YmdHis")).".{$secondname}";
		$pathfile = "{$this->savedir}/{$filename}";
		$res = copy($_FILES['attachment']['tmp_name'][$key], $pathfile);

		if ($res == false) throw new Exception("影片上傳發生錯誤");

		return [$filename, $pathfile];
	}


	//提取副檔名
	private function secondname($name)
	{
		$name = strrchr($name,".");//取得路徑最後一次出現「.」以後的字串
		$name = ltrim($name,".");//把字串前的特定字串「.」去除。若沒指定字串，則代表去除空白
		return $name;
	}

}
