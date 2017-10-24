<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_user extends CI_Migration {

        public function up()
        {
        	$this->dbforge->add_field([
        		'user_id' => [
        			'type' => 'int',
        			'constraint' => 10,
        			'unsigned' => TRUE, // 非負數？
                    'auto_increment' => TRUE
        		],
                'user_key_id' => [
                    'type' => 'int',
                    'constraint' => 10,
                    'unsigned' => TRUE, // 非負數？
                ],
                'user_name' => [
                    'type' => 'varchar',
                    'constraint' => 50,
                ],
        	]);

        	// 3.2.0 之前的必須這麼寫才能使用 CURRENT_TIMESTAMP
        	$this->dbforge->add_field("user_created_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
        	$this->dbforge->add_field("user_updated_at datetime on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

            $this->dbforge->add_key('user_id', TRUE);
            $this->dbforge->add_key('user_key_id', TRUE);
            
            $this->dbforge->create_table('user');
        }

        public function down()
        {
            $this->dbforge->drop_table('user');
        }
}