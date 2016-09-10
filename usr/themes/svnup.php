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
    <script>
        var xmlHttp = new XMLHttpRequest();
        xmlHttp.onreadystatechange = function(){
            if(xmlHttp.readyState == 4 && xmlHttp.status == 200)
            {
                document.getElementById("info").innerHTML = "svn更新成功";
                window.location.href = '/packlist.php';
            }
        }
        xmlHttp.open("get", "/api/curversion", true);
        xmlHttp.send();
    </script>
</head>
<body>
    <div class="divcenter">
        <label id="info">正在更新svn</label>
    </div>
</body>
</html>