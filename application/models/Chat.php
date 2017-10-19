<?php
namespace Model;
 
class Chat {
 
	use Tool;
 
	public function isnert($param)
	{
		$this->db->set('chat_room_id', $param->chat_room_id);
		$this->db->set('chat_message', $param->chat_message);
		$this->db->set('chat_connect_id', $param->chat_connect_id);
		$this->db->set('chat_option', $param->chat_option);
		
		// die($this->db->get_compiled_insert('chat') . "\n\n");
		$this->db->insert('chat');
		return $this->db->insert_id();
	}
 
}