<!DOCTYPE html>
<html>
<head>
	<title></title>
	<style type="text/css">
		body {
				font-family: Arial, Helvetica, sans-serif;
				font-size:16px;
				color:#666666;
				background:#fff;
				text-align:center;
			}
		.divcenter{ line-height:100px; height:30px; margin-top:200px;}
	</style>
	<script type="text/javascript" src="<?php $this->getThemeUrl('jquery-3.1.0.js'); ?>"></script>
</head>
<body>
	<center class="divcenter">
		<button id="normal" type="button" style="width:100px;height: 25px">正常包</button>
		<button id="banshu" type="button" style="width:100px;height: 25px">版署包</button>
		<?php if($this->isBusy()){ ?>
			<a href="<?php echo $this->getLastLogFile(); ?>">查看日志</a>	
		<?php } ?>
	</center>
</body>
<script type="text/javascript">
	$('#normal').click(function()
	{
		window.location.href = '/svnup.php';
	});
	$('#banshu').click(function()
	{
		alert("暂未实现");
	})
</script>
</html>