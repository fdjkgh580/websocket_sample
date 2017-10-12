<!DOCTYPE html>
<html>
<head>
	<title>Ratchet Test</title>
	<meta charset="UTF-8"/>
	<link rel="stylesheet" href="app.css">
	<script
	  src="https://code.jquery.com/jquery-3.2.1.min.js"
	  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	  crossorigin="anonymous"></script>
	<script src="bower_components/vmodel.js/src/jquery.vmodel.min.js"></script>
	<script src="bower_components/file-preview.js/src/jquery.file-preview.min.js"></script>
	<script src="app/form1.js?t=<?=time()?>"></script>
	<script src="app/chatHelper.js?t=<?=time()?>"></script>
	<script src="app/websocket.js?t=<?=time()?>"></script>
</head>
<body>

	<form class="form1">

		<input type="text" class="name" value="<?=$_POST['name']?>">
		<input type="text" class="room_id" value="<?=$_POST['room_id']?>">

		<ul class="chat"></ul>
		
		<p>
			<?=$_POST['name']?>: <br>
			<textarea class="message" cols="30" rows="3" placeholder="想說些什麼" required autofocus></textarea>
		</p>

		<p>
			<input type="file" name="upl[]" class="upl" multiple hidden>
			<img class="preview">
		</p>
		
		<p>
			<button class="submit">送出訊息</button>
		</p>

		<pre class="debug"></pre>

	</form>
 
</body>
</html>