<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>svn_update</title>
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
    <script src="<?php $this->getThemeUrl('jquery-3.1.0.js');?>"></script>
    <script>
        var packList = $.ajax('/api/doPack',{
         dataType: 'json'
        });
    </script>
</head>
<body>
    <div class="divcenter">
        <label id="info">正在运行打包程序</label>
        <a href="<?php echo $this->absLogFileName; ?>">查看打包日志</a>
    </div>
</body>
</html>