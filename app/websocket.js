$(function (){



    $.vmodel.create({
        selector: window,
        model: '--websocket',
        isautoload: true,
        method: function (){
            var vs = this;
            this.conn;
            this.autoload = ['connect'];
            this.connect = function (){

                vs.conn = new WebSocket('ws://chat.websocket.dennings.org:8080');

                $.extend(vs.conn, {
                    onopen : vs.onopen,
                    onerror : vs.onerror,
                    onclose : vs.onclose,
                    onmessage : vs.onmessage
                });
            }

            this.onerror = function (e){
                $.vmodel.get("chatHelper").debug('伺服器無法連接。')
            }

            this.onclose = function (e){
                $.vmodel.get("chatHelper").debug('伺服器斷線。')
            }

            this.onmessage = function (e){
                var obj = JSON.parse(e.data);

                if (obj.type == "into") {
                    $.vmodel.get("chatHelper").welcome(obj);
                } 
                else if (obj.type == "message") {
                    $.vmodel.get("chatHelper").new_message(obj)
                }
                else if (obj.type == "leave") {
                    $.vmodel.get("chatHelper").leave(obj)
                }
                else {
                    $.vmodel.get("chatHelper").debug('接收到的訊息無法分類');
                }
            }

            this.onopen = function (e){
                $.vmodel.get("chatHelper").debug('連線成功。')
                $.vmodel.get("chatHelper").join_chatroom();
            }

            this.send = function (obj){
                vs.conn.send(JSON.stringify(obj));
            }
        }
    });

})