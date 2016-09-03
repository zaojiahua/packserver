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
	 * 前端工作目录的最新版本号
	 * @var string
	 */
	private $newstVersion = "";

	/**
	 * 前端工作目录的当前版本号
	 * @var string
	 */
	private $currentVersion = "";

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);

		/** 创建一个log文件，记录执行shell过程中的日志 */
		$date = date("Y-m-d_H_i_s", time() + 8 * 60 * 60);
		$this->logFileName = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__ . "/{$date}.txt";
	}

	/** 
	 * 更新仓库 获得当前仓库的最新版本号
	 * @var boolean 是否更新svn
	 * @return string
	 */
	public function updateVersion($updateSvn)
	{
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
			return "版本获取失败";
		}
		$svnVersionXml = simplexml_load_file($svnVersionLog);
		$svnVersion = (string)$svnVersionXml->logentry->attributes()->revision;
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
	public function getPackedList()
	{
		if(!isset($_SESSION['currentVersion']))
		{
			header('Location: index.php');
			exit;
		}

		$packVersionList = [];
		$packType = $this->request->type;
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
		return $packVersionList;
	}

	/**
	 * 解析参数是否合法
	 * @return mixed
	 */
	public function parsePackParams()
	{
		/** 设置打包参数 */
		$packParams = [];
		$packParams['packCommand'] = $this->request->packcommand;
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

		return $packParams;
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

		$packCommand = $this->request->packcommand;
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
			exit;
		}
	}

	/**
	 * 打更新包
	 * @return mixed
	 */
	public function doIncrementalPackage()
	{
		if(false === $this->parsePackParams())
		{
			echo "参数错误";
			exit;
		}

		$scriptPath = __PYTHON_SCRIPT_PATH__ . '/cocos_pack.py';
		$ret = system("python $scriptPath > $this->logFileName 2>&1");

		if(false === $ret)
		{
			echo "打包脚本出错";
			exit;
		}

		return true;
	}

	/**
	 * 打整包
	 * @return mixed
	 */
	public function doWholePackage()
	{
		// if(false === $this->parsePackParams())
		// {
		// 	echo "参数错误";
		// 	exit;
		// }

		$scriptPath = __ANDROID_PROJ_PATH__ . '/auto_build.py';
		$ret = system("python $scriptPath > $this->logFileName 2>&1");

		if(false === $ret)
		{
			echo "打包脚本出错";
			exit;
		}

		return true;
	}
}


