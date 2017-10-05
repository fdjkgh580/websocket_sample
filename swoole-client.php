<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<script>
var wsServer = 'ws://chat.websocket.dennings.org:8080';
var websocket = new WebSocket(wsServer);
websocket.onopen = function (evt) {
    console.log("成功連接到 WebSocket 服務");
};

websocket.onclose = function (evt) {
    console.log("關閉連接服務");
};

websocket.onmessage = function (evt) {
    console.log('接收伺服器數據: ' + evt.data);
};

websocket.onerror = function (evt, e) {
    console.log('發生錯誤: ' + evt.data);
};
	</script>
</head>
<body>
	
</body>
</html>