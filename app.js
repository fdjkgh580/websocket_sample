$(function() {

	$(".form1").on("change", ".upl", function (){
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

	$(".form1").on("click", ".preview", function (){
		$(".upl").click();
	});

	$(".form1").on("submit", function (){

		var name    = $(this).find('.name').val();
		var message = $(this).find(".message").val();
		var img     = $(this).find(".preview").attr("data-base64");

		var data    = {
			name: name,
			message: message,
			img: img
		};

		// 發送到對方
		_conn.send(JSON.stringify(data));

		// 也傳到己方
		chatHelper.add(data)
		chatHelper.name_lcok();

		return false;
	});


	// 輔助行為類別
	const chatHelper = new function (){
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
			_conn.send(JSON.stringify({
				name: 'new_user',
				message: '加入聊天',
				img: null
			}));
		}
	}
	
	chatHelper.debug('系統連線中...')

	// WebSocket
	var _conn = new WebSocket('ws://websocket.localhost:8080');

	$.extend(_conn, {
		onopen : function(e) {
			
			chatHelper.new_user();
			chatHelper.debug('連線成功。')
		},
		onerror : function(e) {
			chatHelper.debug('伺服器無法連接。')
		},
		onclose : function(e) {
			chatHelper.debug('伺服器斷線。')
		},
		onmessage : function(e) {
			var obj = JSON.parse(e.data);
			chatHelper.add(obj)
		}
	});
	



	

});

