<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Websocket extends CI_Controller {

    use \Lib\Jsnlib\Swoole\Debug;

    function __construct()
    {
        parent::__construct();
        $this->chat_model = new \Model\Chat;
        $this->room = new \Lib\Jsnlib\Swoole\Room;
    }

    // 執行服務
    public function run()
    {
        // Debug 
        $this->is_print_command_line = false;

        $ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

        $ws->set([
            'worker_num' => 1,    //worker process num
            'backlog' => 128,   //listen backlog
        ]);


        $this->room->use('table');
        $this->room->debug($this->is_print_command_line);

        $ws->on('open', function ($ws, $request) {

            $this->room->connect('add', $request->fd);
            var_dump($this->room->connect('get'));

            $this->command_line("■ 進入者編號：{$request->fd}\n");

        });

        $ws->on('message', function ($ws, $frame) {
            return true;

            $this->command_line("收到進入者 {$frame->fd} 訊息: {$frame->data} \n");

            $this->room->get_message_and_send($ws, $frame);

            $data_decode = json_decode($frame->data, true);
            $message = isset($data_decode['message']) ? $data_decode['message'] : null;

            if (!isset($data_decode['room_id'])) return true;
            
            // 寫入DB
            $last_insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
            [
                'chat_room_id' => $data_decode['room_id'],
                'chat_message' => $message,
                'chat_connect_id' => $frame->fd,
                'chat_option' => $frame->data
            ]));
        });

        $ws->on('close', function ($ws, $fd) {

            $this->command_line("離開者編號：{$fd} ----------- END\n\n");

            $this->room =& $GLOBALS['room'];

            $result = $this->room->leave($ws, $fd);

            if ($result === false) return true;

            // 寫入DB
            $last_insert_id = $this->chat_model->isnert(new \Jsnlib\Ao(
            [
                'chat_room_id' => $result['room_id'],
                'chat_message' => null,
                'chat_connect_id' => $result['user_id'],
                'chat_option' => json_encode([
                    'type' => 'leave',
                    'name' => $result['userdata']['name']
                ])
            ]));

        });

        $ws->start();

    }


    public function test()
    {
        $ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

        $this->storage = new \Lib\Jsnlib\Swoole\Storage\PHPArray;

        $ws->on('open', function ($ws, $request) {
            
        });

        $ws->on('message', function ($ws, $frame) {

            $obj = json_decode($frame->data);

            // 建立房間
            if ( ! $this->storage->exist($obj->room_id))
            {
                // echo "建立房間\n";
                $this->storage->set($obj->room_id, json_encode([null]));

                echo "加入第一人\n";
                $this->storage->set($obj->room_id, json_encode([$frame->fd]));
            }
            else 
            {
                echo "追加 \n";

                // 目前的值
                $encode = $this->storage->get($obj->room_id);
                $decode = json_decode($encode, true);

                // 添加
                array_push($decode, $frame->fd);
                $encode = json_encode($decode);
                $this->storage->set($obj->room_id, $encode);

                
                // print_r($decode); echo "\n";

            }

        });

        $ws->on('close', function ($ws, $fd) {
        });

        $ws->start();
    }
}
