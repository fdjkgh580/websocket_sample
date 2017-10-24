<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_connect extends CI_Migration {

        public function up()
        {
        	$this->dbforge->add_field([
        		'connect_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        			'unsigned' => TRUE, // 非負數？
                    'auto_increment' => TRUE
        		],
        		'connect_user_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        		],
        		'connect_ip' => [
        			'type' => 'varchar',
        			'constraint' => 20,
                    'null' => TRUE
        		]
        	]);

        	// 3.2.0 之前的必須這麼寫才能使用 CURRENT_TIMESTAMP
        	$this->dbforge->add_field("connect_created_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        	$this->dbforge->add_field("connect_updated_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

            $this->dbforge->add_key('connect_id', TRUE);
            $this->dbforge->add_key('connect_user_id');
            
            $this->dbforge->create_table('connect');
        }

        public function down()
        {
            $this->dbforge->drop_table('connect');
        }
}