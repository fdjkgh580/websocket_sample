<?php
namespace Model;
 
class User {
 
	use Tool;

	public function list_all($param = false)
	{
		$this->db->select('*');
		$this->db->from('user');
		
		// die($this->db->get_compiled_select());
		$query = $this->db->get();
		return $this->result($query, "list");
	}

	public function one($param)
	{
		$this->db->select('*');
		$this->db->from('user');
		$this->db->where('connect_user_id', $param->connect_user_id);
		
		// die($this->db->get_compiled_select());
		$query = $this->db->get();
		return $this->result($query, "info");
	}
 
	public function insert($param)
	{
		$this->db->set('connect_user_id', $param->connect_user_id);
		$this->db->set('user_name', $param->user_name);
		
		// die($this->db->get_compiled_insert('user'));
		$this->db->insert('user');
		return $this->db->insert_id();
	}

	public function delete($param)
	{
		$this->db->where('connect_user_id', $param->connect_user_id);
		
		// die($this->db->get_compiled_delete('user'));
		$this->db->delete('user');
		return $this->db->affected_rows();
	}

	public function clean()
	{
		$this->db->truncate('user');
	}
}