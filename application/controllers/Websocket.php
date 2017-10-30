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
        $this->is_print_command_line = true;

        $ws = new swoole_websocket_server("0.0.0.0", 8080); // 0.0.0.0 等於 localhost

        // $ws->set([
        //     // 'reactor_num' => 2,
        //     // 'worker_num' => 1,    //worker process num
        //     // 'backlog' => 128,   //listen backlog
        // ]);


        $this->room->debug($this->is_print_command_line);

        $ws->on('open', function ($ws, $request) {
            $this->on_open($ws, $request);
        });

        $ws->on('message', function ($ws, $frame) {
            $this->on_message($ws, $frame);
        });

        $ws->on('close', function ($ws, $fd, $reactorId) {
            $this->on_close($ws, $fd);
        });

        $ws->start();

    }

    public function on_open($ws, $request)
    {
        $this->command_line("\n■ 進入者編號：{$request->fd}\n");
        
        // 紀錄連線編號
        $this->room->connect(new \Jsnlib\Ao(
        [
            'action' => 'add',
            'user_id' => $request->fd,
            'ip' => $this->input->ip_address()
        ]));
    }

    public function on_message($ws, $frame)
    {
        $this->command_line("收到進入者 {$frame->fd} 訊息: {$frame->data} \n");

        $this->room->get_message_and_send($ws, $frame);
    }

    public function on_close($ws, $fd)
    {

        // 離開聊天室
        $result = $this->room->leave($ws, $fd);

        // 移除連線編號
        $result = $this->room->connect(new \Jsnlib\Ao(
        [
            'action' => 'delete',
            'user_id' => $fd
        ]));

        $this->command_line("離線，使用者編號：{$fd} ----------- END\n\n");

        // 使用者編號沒有在任何群組內
        if ($result === false) 
        {
            $ws->push($fd, json_encode([null]));
        }

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
