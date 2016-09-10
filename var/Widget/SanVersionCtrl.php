<?php

/**
 * 前端代码仓库管理
 * 包括仓库的更新 获取仓库版本号 切换分支 打tag等等
 */
class Widget_SanVersionCtrl extends Typecho_Widget
{
	/**
	 * 用来记录shell输出信息的文件
	 * @var string
	 */
	private $logFileName;

	/**
	 * 相对日志目录
	 * @var string
	 */
	private $absLogFileName;

	/**
	 * 前端工作目录的最新版本号
	 * @var string
	 */
	private $newstVersion = "";

	/**
	 * 前端工作目录的当前版本号
	 * @var string
	 */
	private $currentVersion = "";

	/**
	 * 定义打包脚本的位置
	 * @var string
	 */
	private $autoBuildScript = "";

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);

		/** 创建一个log文件，记录执行shell过程中的日志 */
		$date = date("Y-m-d_H_i_s", time() + 8 * 60 * 60);
		$this->absLogFileName = __SHELL_LOG_PATH__ . "/{$date}.txt";
		$this->logFileName = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__ . "/{$date}.txt";
		$this->autoBuildScript = __TYPECHO_ROOT_DIR__ . '/' . __TYPECHO_TOOLS_DIR__ . '/auto_build.py';
	}

	/**
	 * 进行耗时操作的时候不能再进行其他耗时操作
	 */
	public function inBusy()
	{
		if(!file_exists(__BUSY_FILE__))
		{
			touch(__BUSY_FILE__);
		}
	}
	public function outBusy()
	{
		if(file_exists(__BUSY_FILE__))
		{
			unlink(__BUSY_FILE__);
		}
	}
	public function isBusy()
	{
		return file_exists(__BUSY_FILE__);
	}

	/**
	 * 获取最后一次的log文件
	 * @return string
	 */
	function getLastLogFile() 
    {
    	$dir = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__;
        $fileNames = scandir($dir);
        $lastLogFileName = '0000-00-00_00_00_00';

        foreach ($fileNames as $fileName) 
        {
            if ($fileName == '.' || $fileName == '..')
            {
                continue;
            }

            if(!preg_match('/^[0-9]+/', $fileName))
			{
				continue;
			}
 			
            if($fileName > $lastLogFileName)
            {
            	$lastLogFileName = $fileName;
            }
        }
 
        return __SHELL_LOG_PATH__ . '/' . $lastLogFileName;
    }

	/** 
	 * 更新仓库 获得当前仓库的最新版本号
	 * @var boolean 是否更新svn
	 * @return string
	 */
	public function updateVersion($updateSvn)
	{
		$this->inBusy();

		$svnPath = __SAN_WORK_PATH__;
		if(true == $updateSvn)
		{
			/** 创建一个临时文件 将svn 日志信息写入 然后解析这个写有日志信息的xml文件 提取最新版本号 */
			$ret = system("svn up $svnPath --username=gaohuang --password=gaohuang --no-auth-cache > $this->logFileName 2>&1");
		}
		$svnVersionLog = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__ . "/svnversionlog.xml";
		$ret = system("svn log $svnPath -l 1 --xml --username=gaohuang --password=gaohuang --no-auth-cache -q > $svnVersionLog");
		if(false === $ret)
		{
			$this->outBusy();
			return "版本获取失败";
		}
		$svnVersionXml = simplexml_load_file($svnVersionLog);
		$svnVersion = (string)$svnVersionXml->logentry->attributes()->revision;
		
		$this->outBusy();

		return $svnVersion;
	}

	/**
	 * 获取仓库的最新版本号 用来和第一次访问时候获得的版本号做对比
	 * @return string
	 */
	public function getNewestVersion()
	{
		if(!isset($_SESSION['currentVersion']))
		{
			header('Location: index.php');
			exit;
		}

		/** 不更新仓库 */
		$this->newstVersion = $this->updateVersion(false);
		$_SESSION['newstVersion'] = $this->newstVersion;
		return $this->newstVersion;
	}

	/**
	 * 获取仓库当前的版本号 要更新SVN 第一次访问的时候调用这个接口
	 * @return string
	 */
	public function getCurrentVersion()
	{
		/** 用户获取当前版本号的时候先更新仓库 */
		$this->currentVersion = $this->updateVersion(true);
		/** 将用户访问时候的最新版本存储到session中 */
		$_SESSION['currentVersion'] = $this->currentVersion;
		return $this->currentVersion;
	}

	/**
	 * 为当前的仓库打tag
	 * @param string $tag 要打的tag
	 * @return void
	 */
	public function tagVersion($tag)
	{

	}

	/**
	 * 切换tag分支
	 * @param string $tag
	 * @return void
	 */
	public function checkoutBranch($tag)
	{

	}

	/** 
	 * 获得所有已经打过包的版本号
	 * @return array
	 */
	public function getPackedList($packType)
	{
		$packVersionList = [];

		if(false == $packType)
		{
			echo '参数错误';
			return;
		}

		$packSettingFile = parse_ini_file(__PYTHON_SCRIPT_PATH__ . '/packsetting.ini');
		$packListFile = __PYTHON_SCRIPT_PATH__ . '/' . $packSettingFile["update{$packType}version"];
		if(file_exists($packListFile))
		{
			$allList = simplexml_load_file($packListFile);
			foreach ($allList->version as $key => $value) {
				$temArray = [];
				$temArray['currentVersion'] = (string)$value->attributes()->currentVersion;
				$temArray['minVersion'] = (string)$value->attributes()->minVersion;
				$temArray['time'] = (string)$value->attributes()->time;
				$temArray['typeCommand'] = (string)$value->attributes()->typeCommand;
				$packVersionList[] = $temArray;
			}
		}

		/** 倒序输出 */
		$packVersionList = array_reverse($packVersionList);
		return $packVersionList;
	}

	/**
	 * 获得打包的类型 整包还是更新包
	 * @param  string etc or pvr
	 * @param  string 版本号
	 * @param  string 时间
	 * @return string 整包还是更新包
	 */
	public function getPackType()
	{
		$packType = $this->request->packType;
		$version = $this->request->version;
		$time = $this->request->time;

		$packVersionList = $this->getPackedList($packType);
		foreach ($packVersionList as $key => $value) 
		{
			if($value['currentVersion'] == $version && $value['time'] == $time)
			{
				echo $value['typeCommand'];
			}
		}
		echo "";
	}

	/**
	 * 下载打包的url
	 * @return string
	 */
	public function getPackageUrl()
	{
		/** packCommand必须从前端获取 debug release则自己获取 */
		$packCommand = $this->request->packCommand;
		$packType = $this->request->packType;
		$version = $this->request->version;
		$time = str_replace(' ', '_', $this->request->time);
		$releaseType = 'release';

		$fileName = '';

		if('incremental' == $packCommand)
		{
			$fileName = "{$version}_{$time}.zip";
			if('etc' == $packType)
			{
				$fileName = __BIN_ETC_PATH__ . '/' . $fileName;
			}
			elseif('pvr' == $packType)
			{
				$fileName = __BIN_PVR_PATH__. '/' . $fileName;
			}
		}
		elseif('whole' == $packCommand)
		{
			$fileName = "{$version}_{$time}";
			if('etc' == $packType)
			{
				$fileName = __APK_PATH__ . '/' . "san_slg-{$releaseType}-" . $fileName . '.apk';
			}
			elseif('pvr' == $packType)
			{
				$fileName = __IPA_PATH__. '/' . "san_slg-{$releaseType}-" . $fileName . '.ipa';
			}
		}

		echo $fileName;
	}

	/**
	 * 解析参数是否合法
	 * @return mixed
	 */
	public function parsePackParams()
	{
		/** 设置打包参数 */
		$packParams = [];
		
		if(isset($this->request->incremental))
		{
			$packParams['packCommand'] = 'incremental';
		}
		elseif(isset($this->request->whole))
		{
			$packParams['packCommand'] = 'whole';
		}
		$packParams['packType'] = $this->request->packtype;
		$packParams['packVersion'] = $this->request->bigversion . '.' . $this->request->svnversion;
		$packParams['packMinVersion'] = $this->request->minversion;

		/** 对打包参数进行检查 */
		if($packParams['packType'] != 'etc' and $packParams['packType'] != 'pvr')
		{
			return false;
		}

		/** 格式匹配 */
		if(!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', $packParams['packMinVersion']))
		{
			return false;
		}

		if(!preg_match('/^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$/', $packParams['packVersion']))
		{
			return false;
		}

		/** 将参数记录下来 */
		$_SESSION['packParams'] = $packParams;
		$_SESSION['logFile'] = $this->logFileName;

		return true;
	}

	/**
	 * 打包
	 * @return void
	 */
	public function doPack()
	{
		if(!isset($_SESSION['currentVersion']))
		{
			header('Location: index.php');
			exit;
		}

		$this->inBusy();

		$packCommand = $_SESSION['packParams']['packCommand'];
		if('incremental' == $packCommand)
		{
			$this->doIncrementalPackage();
		}
		else if('whole' == $packCommand)
		{
			$this->doWholePackage();
		}
		else
		{
			echo "参数{$packCommand}错误";
		}
	}

	public function getPackParams()
	{
		$packParams = $_SESSION['packParams'];
		$packCommand = $packParams['packCommand'];
		$packType = $packParams['packType'];
		$packVersion = $packParams['packVersion'];
		$packMinVersion = $packParams['packMinVersion'];
		if("etc" == $packType)
		{
			$packTarget = "android";
		}
		elseif("pvr" == $packType)
		{
			$packTarget = "ios";
		}

		$busyFileName = __BUSY_FILE__;
		if($packCommand == 'incremental')
		{
			return "-m $packType -v $packVersion $packMinVersion -t incremental -d $busyFileName";
		}
		elseif($packCommand == 'whole')
		{
			return "-c $packTarget -m $packType -v $packVersion $packMinVersion -d $busyFileName";
		}
	}

	/**
	 * 打更新包
	 * @return mixed
	 */
	public function doIncrementalPackage()
	{
		$logFileName = $_SESSION['logFile'];
		$packParams = $this->getPackParams();

		$scriptPath = __PYTHON_SCRIPT_PATH__ . '/cocos_pack.py';
		/** 调用自己包装好的一个脚本 */
		$ret = system("python $this->autoBuildScript > $logFileName $scriptPath $packParams 2>&1 &");

		return true;
	}

	/**
	 * 打整包
	 * @return mixed
	 */
	public function doWholePackage()
	{
		$logFileName = $_SESSION['logFile'];
		$packParams = $this->getPackParams();

		$scriptPath = __PYTHON_SCRIPT_PATH__ . '/auto_build.py';
			$ret = system("python $this->autoBuildScript > $logFileName $scriptPath $packParams 2>&1 &");

		return true;
	}

	public function render()
	{
		$pathInfo = $this->request->getPathInfo();

		/** 如何系统忙 则等待 */
		if($this->isBusy() && '/' != $pathInfo)
		{
			echo '<script>alert("打包系统忙!"); window.location.href = "/";</script>';
			exit;
		}

		if('/' == $pathInfo)
		{
			$themeFile = '/index.php';
		}
		elseif('/svnup.php' == $pathInfo)
		{
			$themeFile = '/svnup.php';
		}
		elseif('/packlist.php' == $pathInfo)
		{
			$themeFile = '/packlist.php';
		}
		elseif('/packcommand.php' == $pathInfo)
		{
			if(true == $this->parsePackParams())
			{
				$themeFile = '/packcommand.php';
			}
			else
			{
				echo "<script> alert('参数不合法'); window.location.href = '/packlist.php';</script>";
				exit;
			}
		}

		/** 输出模板 */
        require_once __TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__ . $themeFile;
	}

	public function getThemeUrl($fileName)
	{
		echo __TYPECHO_THEME_DIR__ . '/' . $fileName;
	}
}


