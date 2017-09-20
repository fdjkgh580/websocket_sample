$(function() {

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
	        	    var message = vs.root.find(".message").val();
	        	    var img     = vs.root.find(".preview").attr("data-base64");

	        	    var data    = {
	        	    	name: name,
	        	    	message: message,
	        	    	img: img
	        	    };

	        	    // 發送到對方
	        	    $.vmodel.get("websocket").conn.send(JSON.stringify(data))

	        	    // 也傳到己方
	        	    $.vmodel.get("chatHelper").add(data)
	        	    $.vmodel.get("chatHelper").name_lcok();

	        	    return false;
	        	});
	        	
	        }
	    }
	});


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
	        this.add = function (obj){

	        	if (obj.name == "new_user") {
	        		var li = 
	        			'<li>' + 
	        				"<span>新訪客進入</span>" + 
	        			'</li>';
	        	}
	        	else {
	        		var li = 
	        			'<li>' + 
	        				"<span>" + obj.name + " 說：" + obj.message + "</span>" + 
	        				"&emsp;<img src='" + obj.img + "' width='120'>" + 
	        			'</li>';
	        	}
	        	$(".chat").append(li)
	        	this.reset_message();
	        }
	        this.reset_message = function (){
	        	$(".message").val('').focus();
	        	$(".upl").val(null);
	        	$(".preview").css("background-image", 'url(null)').removeAttr("data-base64")
	        }
	        this.name_lcok = function (){
	        	$(".name").prop("disabled", true);
	        }
	        this.debug = function (msg){
	        	$(".debug").append(msg + "\n")
	        }
	        this.new_user = function (){
	        	$.vmodel.get("websocket").conn.send(JSON.stringify({
	        		name: 'new_user',
	        		message: '加入聊天',
	        		img: null
	        	}));
	        }
	    }
	});
	

	


	
	$.vmodel.create({
	    selector: window,
	    model: '--websocket',
	    isautoload: true,
	    method: function (){
	        var vs = this;
	        this.conn;
	        this.autoload = ['connect'];
	        this.connect = function (){

				vs.conn = new WebSocket('ws://websocket.localhost:8080');

				$.extend(vs.conn, {
					onopen : function(e) {
						$.vmodel.get("chatHelper").new_user();
						$.vmodel.get("chatHelper").debug('連線成功。')
					},
					onerror : function(e) {
						$.vmodel.get("chatHelper").debug('伺服器無法連接。')
					},
					onclose : function(e) {
						$.vmodel.get("chatHelper").debug('伺服器斷線。')
					},
					onmessage : function(e) {
						var obj = JSON.parse(e.data);
						$.vmodel.get("chatHelper").add(obj)
					}
				});

	        }

	        this.onopen = function (){

	        }
	    }
	});
	
	



	

});

