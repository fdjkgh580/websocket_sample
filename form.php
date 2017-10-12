<!DOCTYPE html>
<html>
<head>
	<title>Ratchet Test</title>
	<meta charset="UTF-8"/>
	<link rel="stylesheet" href="app.css">
	<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	<script src="bower_components/vmodel.js/src/jquery.vmodel.min.js"></script>
	<script src="bower_components/file-preview.js/src/jquery.file-preview.min.js"></script>
</head>
<body>

	<form class="form1" method="post" action="room.php">
		

		<p>
			<select name="room_id" required>
				<option value="">請選擇要進入的群組</option>
				<option value="1">群組A</option>
				<option value="2">群組B</option>
			</select>
		</p>


		<p>
			<input type="text" name="name" placeholder="請輸入姓名" required>
		</p>

		<button type="submit">進入聊天</button>

	</form>
 
</body>
</html>