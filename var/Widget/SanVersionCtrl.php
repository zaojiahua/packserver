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

	public function __construct($request, $response, $params = NULL)
	{
		parent::__construct($request, $response, $params);

		/** 创建一个log文件，记录执行shell过程中的日志 */
		$date = date("Y-m-d_H-i-s", time());
		$this->logFileName = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__ . "/{$date}.txt";
	}

	/** 
	 * 更新仓库 获得当前仓库的最新版本号
	 * @return string
	 */
	public function updateVersion()
	{
		/** 创建一个临时文件 将svn 日志信息写入 然后解析这个写有日志信息的xml文件 提取最新版本号 */
		$svnPath = __SAN_WORK_PATH__;
		shell_exec("svn up $svnPath > $this->logFileName 2>&1");
		$svnVersionLog = __TYPECHO_ROOT_DIR__ . __SHELL_LOG_PATH__ . "/svnversion.xml";
		shell_exec("svn log $svnPath -l 1 --xml -q > $svnVersionLog");
		$svnVersionXml = simplexml_load_file($svnVersionLog);
		$svnVersion = $svnVersionXml->revision;
		return $svnVersion;
	}

	/**
	 * 获取仓库的最新版本号
	 * @return string
	 */
	public function getNewestVersion()
	{
		$this->newstVersion = $this->updateVersion();
		return $this->newstVersion();
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
}