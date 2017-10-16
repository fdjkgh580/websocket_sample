$(function (){
    // 輔助行為類別
    $.vmodel.create({
        selector: 'body',
        model: '--chatHelper',
        isautoload: true,
        method: function (){
            var vs = this;
            this.autoload = ['init'];
            this.init = function (){
                vs.debug('系統連線中...')
            }
            /**
             * 在訊息列中，顯示新加入的訊息
             * @param obj.name
             * @param obj.message
             * @param obj.img
             */
            this.new_message = function (obj){

                var li = 
                    '<li>' + 
                        "<span>" + obj.name + " 說：" + obj.message + "</span>" + 
                        "&emsp;<img src='" + obj.img + "' width='120'>" + 
                    '</li>';

                $(".chat").append(li)
            }
            this.welcome = function (obj){
                var username = $(".name").val();

                var li = 
                    '<li>' + 
                        '<span>' + obj.name +'進入群組</span>' + 
                    '</li>';

                $(".chat").append(li);
            }
            this.name_lcok = function (){
                $(".name").prop("disabled", true);
            }
            this.debug = function (msg){
                $(".debug").append(msg + "\n")
            }
            this.new_user = function (){
                $.vmodel.get("websocket").send({
                    name: 'new_user',
                    message: '加入聊天',
                    img: null
                });
            }
            this.join_chatroom = function (){
                var name = $(".name").val();
                var room_id = $(".room_id").val();
                $.vmodel.get("websocket").send({
                    type: 'join',
                    name: name,
                    room_id: room_id
                });
                
            }
        }
    });
})