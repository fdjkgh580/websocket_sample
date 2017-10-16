$(function (){
    $.vmodel.create({
        selector: '.form1',
        model: '--event',
        isautoload: true,
        method: function (){
            var vs = this;
            this.autoload = ['choice_image', 'trigger_preview', 'submit'];
            
            // 選擇圖片
            this.choice_image = function (){
                vs.root.on("change", ".upl", function (){
                    var _this = this;
                    
                    $.filePreview.create(this, {
                        isReader: true,
                        progress: function (key, percent){
                            console.log(percent)
                        },
                        success: function (key, obj){
                            $(".preview").css("background-image", "url("+obj.preview+")");
                            $(".preview").attr("data-base64", obj.base64);
                        }
                    });

                });
            }

            // 觸發點擊預覽圖的動作
            this.trigger_preview = function (){
                vs.root.on("click", ".preview", function (){
                    vs.root.find(".upl").click();
                });
            }

            // 送出表單
            this.submit = function (){
                vs.root.on("submit", function (){
                    var name    = vs.root.find('.name').val();
                    var room_id = vs.root.find(".room_id").val();
                    var message = vs.root.find(".message").val();
                    var img     = vs.root.find(".preview").attr("data-base64");

                    var data    = {
                        type: 'message',
                        room_id: room_id,
                        name: name,
                        message: message,
                        img: img
                    };

                    // 發送到對方
                    $.vmodel.get("websocket").send(data)

                    // 也傳到己方
                    $.vmodel.get("chatHelper").new_message(data)
                    $.vmodel.get("chatHelper").name_lcok();

                    // 訊息滾到置底
                    $("ul.chat").scrollTop($("ul.chat").height());

                    return false;
                });
                
            }
        }
    });
})