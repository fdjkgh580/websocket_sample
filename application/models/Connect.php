<?php
namespace Model;
 
class Connect {
 
	use Tool;
 
	public function insert($param)
	{
		$this->db->set('connect_user_id', $param->connect_user_id);
		$this->db->set('connect_ip', $param->connect_ip);
		
		// die($this->db->get_compiled_insert('connect'));
		$this->db->insert('connect');
		return $this->db->insert_id();
	}
 
 	public function delete($param)
 	{
 		$this->db->where('connect_user_id', $param->connect_user_id);
 		
 		// die($this->db->get_compiled_delete('connect'));
 		$this->db->delete('connect');
 		return $this->db->affected_rows();
 	}
}