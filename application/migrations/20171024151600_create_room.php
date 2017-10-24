<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_room extends CI_Migration {

        public function up()
        {
        	$this->dbforge->add_field([
        		'room_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        			'unsigned' => TRUE, // 非負數？
                    'auto_increment' => TRUE
        		],
                'room_key_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => TRUE, // 非負數？
                ],
        		'room_user_id' => [
        			'type' => 'int',
        			'constraint' => 10,
                    'unsigned' => TRUE, // 非負數？
        		]
        	]);

        	// 3.2.0 之前的必須這麼寫才能使用 CURRENT_TIMESTAMP
        	$this->dbforge->add_field("room_created_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        	$this->dbforge->add_field("room_updated_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

            $this->dbforge->add_key('room_id', TRUE);
            $this->dbforge->add_key('room_key_id');
            $this->dbforge->add_key('room_user_id');
            
            $this->dbforge->create_table('room');
        }

        public function down()
        {
            $this->dbforge->drop_table('room');
        }
}