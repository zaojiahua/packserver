<?php

/**
 * 前端代码仓库管理
 * 包括仓库的更新 获取仓库版本号 切换分支 打tag等等
 */
class Widget_SanVersionCtrl extends Typecho_Widget
{


	/** 
	 * 更新仓库
	 * @return string
	 */
	public function updateVersion()
	{
		$svnPath = __SAN_WORK_PATH__;
		$message = shell_exec("svn up $svnPath 2>&1");
		return $message;
	}

	/**
	 * 获取仓库的最新版本号
	 * @return string
	 */
	public function getNewestVersion()
	{
		echo $this->updateVersion();
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