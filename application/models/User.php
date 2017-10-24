<?php
namespace Model;
 
class User {
 
	use Tool;
 
	public function insert($param)
	{
		$this->db->set('user_key_id', $param->user_key_id);
		$this->db->set('user_name', $param->user_name);
		
		// die($this->db->get_compiled_insert('user'));
		$this->db->insert('user');
		return $this->db->insert_id();
	}

	public function delete($param)
	{
		$this->db->where('user_key_id', $param->user_key_id);
		
		// die($this->db->get_compiled_delete('user'));
		$this->db->delete('user');
		return $this->db->affected_rows();
	}
}