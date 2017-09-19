$(function() {

	// 輔助行為類別
	const chatHelper = new function (){
		this.add = function (obj){
			var li = '<li>' + obj.name + " 說：" + obj.message + '</li>';
			$(".chat").append(li)
			this.empty_message();
		}
		this.empty_message = function (){
			$(".message").val('').focus();
		}
		this.name_lcok = function (){
			$(".name").prop("disabled", true);
		}
		this.debug = function (msg){
			$(".debug").append(msg + "\n")
		}
	}
	
	chatHelper.debug('系統連線中...')

	// WebSocket
	var _conn = new WebSocket('ws://test.websocket.dennings.org:8080');

	$.extend(_conn, {
		onopen : function(e) {
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
	

	


	$(".form1").on("submit", function (){

		var name    = $(this).find('.name').val();
		var message = $(this).find(".message").val();
		var data    = {
			name: name,
			message: message
		};

		// 發送到對方
		_conn.send(JSON.stringify(data));

		// 也傳到己方
		chatHelper.add(data)
		chatHelper.name_lcok();

		return false;
	})

});

