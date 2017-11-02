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

            this.reset_message = function (){
                $(".message").val('').focus();
                $(".upl").val(null);
                $(".preview").css("background-image", 'url(null)').removeAttr("data-base64")
            }

            // 訊息滾到置底
            this.scroll_bottom = function (){
                $("ul.chat").scrollTop($("ul.chat").height());
            }

            // 是否有夾帶附件
            this.is_choice_attachement = function (){
                var val = vs.root.find(".attachment").val();
                return val == "" ? false: true;
            }

            this.submit_use_http = function (formthis){
                var url = $(formthis).attr("data-attachment-url");

                $(formthis).ajaxSubmit({
                    url: url,
                    data: {
                        say: 'hello',
                    },
                    method: "POST", 
                    uploadProgress: function (event, position, total, percentComplete){
                        console.log("附件上傳了 " + percentComplete + "%")
                    },
                    success: function (obj){
                        // console.log(obj);
                        var box = {};

                        $.each(obj, function (key, item){
                            if (item.type == "video" || item.type == "image") {
                                var filetype = item.type;
                            }
                            else {
                                console.log("Error: image type");
                                return false;
                            }

                            box[key] = _multi_code(filetype, item.url)
                        });

                        var encode = JSON.stringify(box);

                        _set_message(encode);
                    },
                    error: function (data){
                        console.log('error')
                        console.log(data)
                    }
                }); 
            }

            var _multi_code = function (filetype, url) {

                var code = {
                    type: filetype,
                    url: url
                }

                return  code;
            }

            // 將字串放到文字對話框
            var _set_message = function (msg){
                vs.root.find(".message").val(msg);
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

                    // // 也傳到己方
                    // $.vmodel.get("chatHelper").new_message(data)
                    // $.vmodel.get("chatHelper").name_lcok();

                    // 訊息滾到置底
                    vs.scroll_bottom();
                    
                    vs.reset_message();

                    if (vs.is_choice_attachement() === true) {
                        vs.submit_use_http(this);
                    }

                    return false;
                });
                
            }
        }
    });
})