<?php
namespace Model;
 
class Room {
 
	use Tool;
 
	public function insert($param)
	{
		$this->db->set('room_key_id', $param->room_key_id);
		$this->db->set('room_user_id', $param->room_user_id);
		
		// die($this->db->get_compiled_insert('room'));
		$this->db->insert('room');
		return $this->db->insert_id();
	}

	public function leave($param)
	{
		$this->db->where('room_user_id', $param->room_user_id);
		
		// die($this->db->get_compiled_delete('room'));
		$this->db->delete('room');
		return $this->db->affected_rows();
	}

	public function one($param)
	{
		$this->db->select('*');
		$this->db->from('room');
		$this->db->where('room_user_id', $param->room_user_id);
		
		// die($this->db->get_compiled_select());
		$query = $this->db->get();
		return $this->result($query, "info");
	}

	public function list_all($param)
	{
		$this->db->select('*');
		$this->db->from('room');
		$this->db->where('room_key_id', $param->room_key_id);
		
		// die($this->db->get_compiled_select());
		$query = $this->db->get();
		return $this->result($query, "list");
	}
}