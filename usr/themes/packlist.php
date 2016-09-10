<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>san_slg_pack</title>
		<script src="<?php $this->getThemeUrl('jquery-3.1.0.js');?>"></script>
		<script>
		    $(document).ready(function(){
		    	/** 控制按钮的显示和隐藏 */
		    	$('.version[name=etc]').change(function(){
		    		/** 获得参数 */
			    	var params = $(this).val().split('&');
			    	/** 判断是更新包还是整包 */
			    	$.ajax('/api/getpacktype',{
				    	dataType: 'text',
				    	data: {packType:'etc', version:params[0], time:params[1]}
				    }).done(function (data){
				    	if(data == 'whole')
				    	{
				    		$('.etc .downwhole').show();
				    	}
				    	else
				    	{
				    		$('.etc .downwhole').hide();
				    	}
				    }).fail(function (xhr, status){
				    	alert("从服务器获取数据失败");
				    });
			    });
			    $('.version[name=pvr]').change(function(){
		    		/** 获得参数 */
			    	var params = $(this).val().split('&');
			    	/** 判断是更新包还是整包 */
			    	$.ajax('/api/getpacktype',{
				    	dataType: 'text',
				    	data: {packType:'pvr', version:params[0], time:params[1]}
				    }).done(function (data){
				    	if(data == 'whole')
				    	{
				    		$('.pvr .downwhole').show();
				    	}
				    	else
				    	{
				    		$('.pvr .downwhole').hide();
				    	}
				    }).fail(function (xhr, status){
				    	alert("从服务器获取数据失败");
				    });
			    });

			    /** 加载完成以后先主动触发一次 */
			    $('.version[name=etc]').find('option:selected').trigger('change');
			    $('.version[name=pvr]').find('option:selected').trigger('change');

			    /** 添加按钮事件 */
			    function downClick(command, packType, params)
			    {
			    	$.ajax('/api/getpackageurl',{
				    	dataType: 'text',
				    	data: {packCommand:command, packType:packType, version:params[0], time:params[1]}
				    }).done(function (data){
				    	window.location.href = data;
				    }).fail(function (xhr, status){
				    	alert("从服务器获取数据失败");
				    });
			    }
			    $('.etc .releaseversion').click(function(){
			    	var params = $('.version[name=etc]').find('option:selected').val().split('&');
			    });
			    $('.etc .downincremental').click(function(){
			    	var params = $('.version[name=etc]').find('option:selected').val().split('&');
			    	downClick('incremental', 'etc', params);
			    });
			    $('.etc .downwhole').click(function(){
			    	var params = $('.version[name=etc]').find('option:selected').val().split('&');
			    	downClick('whole', 'etc', params);
			    });
			    $('.pvr .releaseversion').click(function(){
			    	var params = $('.version[name=pvr]').find('option:selected').val().split('&');
			    });
			    $('.pvr .downincremental').click(function(){
			    	var params = $('.version[name=pvr]').find('option:selected').val().split('&');
			    	downClick('incremental', 'pvr', params);
			    });
			    $('.pvr .downwhole').click(function(){
			    	var params = $('.version[name=pvr]').find('option:selected').val().split('&');
			    	downClick('whole', 'pvr', params);
			    });
		    });
		</script>
		<style type="text/css">
			<!--
			body {
				font-family: Arial, Helvetica, sans-serif;
				font-size:16px;
				color:#666666;
				background:#fff;
				text-align:center;
			}
			* {
				margin:0;
				padding:0;
			}
			a {
				color:#1E7ACE;
				text-decoration:none;
			}
			a:hover {
				color:#000;
				text-decoration:underline;
			}
			h3 {
				font-size:20px;
				font-weight:bold;
			}
			pre,p {
				color:#1E7ACE;
				margin:4px;
			}
			input, select {
				/*padding:1px;
				margin:2px;*/
				font-size:15px;
			}
			select.version {
				font-size:15px;
				width:150px;
				height:25px;
				margin-top:8px;
			}
			.buttom{
				padding:1px 10px;
				font-size:12px;
				border:1px #1E7ACE solid;
				background:#D0F0FF;
			}
			#formwrapper {
				width:650px;
				margin:15px auto;
				padding:20px;
				text-align:left;
				border:1px solid #A4CDF2;
			}
			fieldset {
				padding:10px;
				margin-top:5px;
				border:1px solid #A4CDF2;
				background:#fff;
			}
			fieldset legend {
				color:#1E7ACE;
				font-weight:bold;
				padding:3px 20px 3px 20px;
				border:1px solid #A4CDF2;
				background:#fff;
			}
			fieldset label {
				float:left;
				width:120px;
				text-align:right;
				padding:4px;
				margin:4px;
			}
			fieldset div {
				clear:left;
				margin-bottom:2px;
			}
			.enter{ text-align:center;}
			.clear {
				clear:both;
			}
			#radio {
				line-height: 32px;
			}
			-->
		</style>
	</head>
	<body>
		<div id="formwrapper">
			<h3>海盗打包平台</h3>
			<form action="/packcommand.php" method="post" id="apForm">
				<fieldset>
					<legend>打最新包</legend>
					<div>
						<label for="Name">打包类型</label>
						<span id='radio'>
							安卓<input style="margin-left: 5px;margin-right: 10px;" type="radio" checked="checked" name="packtype" value="etc" onclick="onSelectAndroid()"/>
							苹果<input style="margin-left: 5px;" type="radio" name="packtype" value="pvr" onclick="onSelectIos()"/>
						</span>
						<br />
					</div>
					<div>
						<label for="Email">大小版本号</label>
						<input type="text" name="bigversion" id="Email" value="" size="20" maxlength="150" /> *(如1.1.2)<br />
					</div>
					<div>
						<label for="password">svn版本号</label>
						<input type="text" name="svnversion" id="Email" value="<?php echo $this->getNewestVersion(); ?>" size="20" maxlength="150" readOnly="true"/>
						*(由程序获取，无需手动输入)<br />
					</div>
					<div>
						<label for="password">最低依赖版本号</label>
						<input type="text" name="minversion" id="Email" value="" size="20" maxlength="150" />
						<!-- <select name="minversion">
							<?php if(0 == count($this->getPackedList())): ?>
								<option>0.0.0.0</option>
							<?php else: ?>
								<option>1.0.0.0</option>
							<?php endif;?>
					    </select> -->
						*(低于该版本号，需要更新整包，如1.0.0.2)<br />
					</div>
					<div class="enter">
						<input name="incremental" type="submit" class="buttom" value="发布更新包" />
						<input name="whole" type="submit" class="buttom" value="发布整包" />
					</div>
				</fieldset>
			</form><br />
			<form action="" method="post" id="Login">
				<fieldset>
					<legend>版本下载</legend>
					<div class='etc'>
						<label for="Name">Android</label>
						<select class='version' name="etc">
							<?php foreach($this->getPackedList('etc') as $list){ ?>
							<option value="<?php echo $list['currentVersion'] . '&' . $list['time']?>" >
								<?php 
									echo '版本：' . $list['currentVersion'] . ' 时间：' . $list['time'] . ' 包类型：' . $list['typeCommand'];
								?>
							</option>
							<?php } ?>
					    </select>
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom releaseversion" value="发布" />
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom downincremental" value="下载更新包" />
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom downwhole" value="下载整包" />
						<br />
					</div>
					<div class='pvr'>
						<label for="password">IOS</label>
						<select class='version' name="pvr">
							<?php foreach($this->getPackedList('pvr') as $list){ ?>
							<option value="<?php echo $list['currentVersion'] . '&' . $list['time']?>">
								<?php 
									echo '版本：' . $list['currentVersion'] . ' 时间：' . $list['time'] . ' 包类型：' . $list['typeCommand'];
								?>
							</option>
							<?php } ?>
					    </select>
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom releaseversion" value="发布" />
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom downincremental" value="下载更新包" />
					    <input style="margin-left: 10px;" name="download" type="button" class="buttom downwhole" value="下载整包" />
					    <br />
					</div>
					<div class="enter">
						
					</div>
				</fieldset>
			</form>
		</div>
	</body>
</html>

