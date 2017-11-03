<!DOCTYPE html>
<html>
<head>
	<title>Ratchet Test</title>
	<meta charset="UTF-8"/>
	<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0">
	<base href="<?=site_url();?>">
	<link rel="stylesheet" href="app.css?t=<?=time()?>">
	<script
	  src="https://code.jquery.com/jquery-3.2.1.min.js"
	  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
	  crossorigin="anonymous"></script>
	<script src="bower_components/vmodel.js/src/jquery.vmodel.min.js"></script>
	<script src="bower_components/file-preview.js/src/jquery.file-preview.min.js"></script>
	<script src="bower_components/jquery-form/dist/jquery.form.min.js"></script>
	<script src="bower_components/imagesloaded/imagesloaded.pkgd.min.js"></script>
	<script src="app/form1.js?t=<?=time()?>"></script>
	<script src="app/chatHelper.js?t=<?=time()?>"></script>
	<script src="app/websocket.js?t=<?=time()?>"></script>
</head>
<body>

	<form class="form1" enctype="multipart/form-data" data-attachment-url="<?=site_url("upload");?>">

		<input type="hidden" class="name" value="<?=$_POST['name']?>">
		<input type="hidden" class="room_id" value="<?=$_POST['room_id']?>">

		<div class="chat_wrap">
			<ul class="chat"></ul>
		</div>
		
		<div class="editblock">
			<p>
				<?=$_POST['name']?>: <span class="uploadp"></span> <br>
				<textarea class="message" cols="30" rows="3" placeholder="想說些什麼" required autofocus></textarea>
			
				<input type="file" name="upl[]" class="upl" multiple hidden>
				<!-- <img class="preview"> -->
				
				<br>
				<input type="file" name="attachment[]" class="attachment" multiple>
			</p>
			
			<p>
				<button class="submit">送出訊息</button>
			</p>
		</div>

		<pre class="debug"></pre>

	</form>
 
</body>
</html>