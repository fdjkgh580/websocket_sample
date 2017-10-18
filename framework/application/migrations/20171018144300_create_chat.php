<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_chat extends CI_Migration {

        public function up()
        {
        	$this->dbforge->add_field([
        		'chat_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        			'unsigned' => TRUE, // 非負數？
                    'auto_increment' => TRUE
        		],
        		'chat_room_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        		],
        		'chat_message' => [
        			'type' => 'longtext',
        			'null' => true
        		],
        		'chat_connect_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        			'unsigned' => TRUE,
        		],
                'chat_option' => [
                    'type' => 'longtext',
                    'null' => TRUE
                ]
        	]);

        	// 3.2.0 之前的必須這麼寫才能使用 CURRENT_TIMESTAMP
        	$this->dbforge->add_field("chat_created_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        	$this->dbforge->add_field("chat_updated_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

            $this->dbforge->add_key('chat_id', TRUE);
            $this->dbforge->create_table('chat');
        }

        public function down()
        {
                $this->dbforge->drop_table('chat');
        }
}