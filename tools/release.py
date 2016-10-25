# coding=utf-8

# 用来发布版本的脚本

import os
import sys

# 导入读取配置文件的模块
from config_read import *
# 导入解析xml的模块
from xml_parser import *
# 导入文件操作模块
from file_utils import *
# 导入压缩模块
from zip_utils import *

# 更新文件所在目录
UPDATE_VERSION_FILE_LOW = "/Users/san/packserver/bin/Update_server_"
UPDATE_VERSION_FILE_HIGH = "/Users/san/packserver/bin/Update_server_"

# 记录发布记录的文件
RELEASE_FILE_LOW = "/Users/san/packserver/release/low/"
RELEASE_FILE_HIGH = "/Users/san/packserver/release/high/"

# 定义内网热跟新目录 包括高清和低质量 etc pvr
INTRANET_UPDATE_PATH = "10.241.107.31:/app/frontPack/intratest/"
# 定义外网热更新目录
EXTRANET_UPDATE_PATH = "10.241.107.31:/"

# 内网zip包所在的url
INTRANET_UPDATE_ZIP_PATH = "10.241.107.31:/app/frontPack/intratest/"
# 外网zip包所在的url
EXTRANET_UPDATE_ZIP_PATH = "10.241.107.31:/app/frontPack/intratest/"

# 获得本地记录热更新版本的目录
def GetLocalUpdateVersionFile(quality, packMode):
	if "low" == quality:
		versionFile = UPDATE_VERSION_FILE_LOW + "%s/updateversion.xml" % (packMode)
	elif "high" == quality:
		versionFile = UPDATE_VERSION_FILE_HIGH + "%s/updateversion.xml" % (packMode)

	return versionFile

# 更新记录本地热更新的文件
def UpdateLocalVersionFile(quality, packMode, currentVersion):
	versionFile = GetLocalUpdateVersionFile(quality, packMode)

	xmlUpdateVersion = XmlParser(versionFile)
	modifyFileList = []
	
	for temVersion in xmlUpdateVersion.GetTagData("version"):
		if currentVersion["currentVersion"] == temVersion["currentVersion"]:
			# 标记为已经发布
			temVersion["release"] = "true"
			modifyFileList.append(temVersion)
			break

	xmlUpdateVersion.UpdateDataItem(modifyFileList, "version", "currentVersion", None)

# 获得需要merge的版本号
def GetNeedMergeVersion(quality, packMode, lastVersion, mergeVersion):
	versionFile = GetLocalUpdateVersionFile(quality, packMode)

	xmlUpdateVersion = XmlParser(versionFile)
	mergeVersionList = []

	needAdd = False
	if "0.0.0.0" == lastVersion:
		needAdd = True

	for temVersion in xmlUpdateVersion.GetTagData("version"):
		if lastVersion == temVersion["currentVersion"]:
			needAdd = True
		if mergeVersion == temVersion["currentVersion"]:
			needAdd = False
		
		# 不包含上次的版本 但是要包含本次的版本
		if True == needAdd and lastVersion != temVersion["currentVersion"]:
			mergeVersionList.append(temVersion)
		if mergeVersion == temVersion["currentVersion"]:
			mergeVersionList.append(temVersion)

	return mergeVersionList

def GetNeedMergeDir(mergeVersionList):
	dirList = []
	for temMeta in mergeVersionList:
		dirName = temMeta["currentVersion"] + "_" + temMeta["time"]
		dirList.append(dirName)
	return dirList

# 获得记录发布版本的文件
def GetReleaseVersionFile(quality, packMode):
	if "low" == quality:
		versionFile = RELEASE_FILE_LOW + "%s/updateversion.xml" % (packMode)
	elif "high" == quality:
		versionFile = RELEASE_FILE_HIGH + "%s/updateversion.xml" % (packMode)

	return versionFile

# 记录发布文件的md5码
def GetMd5TextFile(quality, packMode):
	if "low" == quality:
		versionFile = RELEASE_FILE_LOW + "%s/md5.txt" % (packMode)
	elif "high" == quality:
		versionFile = RELEASE_FILE_HIGH + "%s/md5.txt" % (packMode)

	return versionFile

# 获取上次发布的版本号
def GetLastReleaseVersion(quality, packMode):
	releaseFile = GetReleaseVersionFile(quality, packMode)
	if False == os.path.exists(releaseFile):
		lastVersion = "0.0.0.0"
	else:
		xmlUpdateVersion = XmlParser(releaseFile)
		versionArray = xmlUpdateVersion.GetTagData("version")
		lastVersion = versionArray[len(versionArray) - 1]["currentVersion"]
	
	return lastVersion

# 更新记录发布版本的文件
def UpdateReleaseVersionFile(quality, packMode, temMeta, zipFileName):
	releaseFile = GetReleaseVersionFile(quality, packMode)
	xmlUpdateVersion = XmlParser(releaseFile)
	modifyFileList = []
	modifyFileList.append(temMeta)
	temMeta["size"] = str(os.path.getsize(zipFileName))
	# 新增一个字段，代表zip包所在的url
	temMeta["url"] = INTRANET_UPDATE_ZIP_PATH
	xmlUpdateVersion.WriteItemData(modifyFileList, "versions", "version", None)

# 最后将本次更新的版本号 依赖的最小版本号写入
def UpdateReleaseVersionAttrib(quality, packMode, currentVersion, minVersion):
	releaseFile = GetReleaseVersionFile(quality, packMode)
	xmlUpdateVersion = XmlParser(releaseFile)
	tempVersion = {}
	tempVersion["currentVersion"] = currentVersion
	tempVersion["minVersion"] = minVersion
	xmlUpdateVersion.UpdateRootAttrib(tempVersion, "versions")

# 合并目录
def MergeUpdateDir(updateDir, mergeVersionDir, dirName):
	allDirFile = os.listdir(updateDir)
	allDir = []
	
	# 获得要merge的目录 并且根据版本号从小到大进行排序
	for fileName in mergeVersionDir:
		fileName = os.path.join(updateDir, fileName)
		allDir.append(fileName)

	# 建立合并的目录
	if False == os.path.exists(dirName):
		os.mkdir(dirName)
	else:
		print("%s is exists" % dirName)
		return

	# merge目录 合并的顺序很重要
	for tempDir in allDir:
		print("begin merge %s" % tempDir)
		for fileName in FileUtils.GetAllFiles(os.path.join(updateDir, tempDir)):
			desFileName = FileUtils.GetFileNameInDir(fileName, os.path.join(updateDir, tempDir) + "/", dirName)
			FileUtils.CopyFile(fileName, desFileName)

	return dirName

# 查找需要删除的文件
def FindNeedDeleteFile(dirName):
	print("begin find files need to delete...")
	
	deleteFileList = []

	xmlParser = XmlParser(os.path.join(dirName, "md5/md5.xml"))
	md5Src = xmlParser.GetTagData("file")
	#建立字典的数据结构，方便快速遍历文件目录
	srcFileDict = dict (map(lambda x:[x["filePath"], x["filePath"]], md5Src))

	binFileList = FileUtils.GetAllFiles(dirName)
	
	for index, fileName in enumerate(binFileList):
		originFileName = fileName
		needManage = True
		# 排除md5目录
		exclude = ["md5/", "md5/md5.xml", "md5/md5.txt"]
		for dir in exclude:
			if dir in fileName:
				needManage = False
				break

		if needManage:
			# 对压缩出来的图片做判断
			if "_alpha" in fileName:
				fileName = fileName[:-10] + fileName[-4:] #hero_alpha.png
			# 需要删除
			transferFileName = fileName[len(dirName) + 1:]
			if transferFileName not in srcFileDict:
				# 只需要以下俩个字段
				temMd5Meta = {}
				temMd5Meta["filePath"] = transferFileName
				# 方便删除
				temMd5Meta["deletePath"] = originFileName
				deleteFileList.append(temMd5Meta)
	
	return deleteFileList

# 删除无用文件
def DeleteUnuselessFile(dirName):
	deleteFileList = FindNeedDeleteFile(dirName)
	for fileName in deleteFileList:
		deleteFileName = fileName["deletePath"]
		if os.path.exists(deleteFileName):
			print("delete file is:%s" % deleteFileName)
			FileUtils.DeleteFile(deleteFileName)

# 获取内网服务器上传地址
def GetIntraUploadPath(quality, packMode):
	uploadPath = INTRANET_UPDATE_PATH + "%s/%s/" % (quality, packMode)

	return uploadPath

def GetExtraUploadPath(quality, packMode):
	pass

if __name__ == "__main__":

	if "-h" in sys.argv:
		print("python release.py -m etc -v 0.0.0.0 -quality low ")
		sys.exit()

	# 获得输入参数
	QUALITY = "high"
	PACKMODE = "pvr"
	RELEAE_VERSION = "0.0.0.13896"

	# merge 更新的文件
	lastReleaseVersion = GetLastReleaseVersion(QUALITY, PACKMODE)
	mergeList = GetNeedMergeVersion(QUALITY, PACKMODE, lastReleaseVersion, RELEAE_VERSION)
	mergeDirList = GetNeedMergeDir(mergeList)
	updateDir = os.path.split(GetLocalUpdateVersionFile(QUALITY, PACKMODE))[0]
	mergeDir = os.path.split(GetReleaseVersionFile(QUALITY, PACKMODE))[0]
	mergeDir = os.path.join(mergeDir, mergeDirList[len(mergeDirList) - 1])
	print("begin merge, please wait...")
	MergeUpdateDir(updateDir, mergeDirList, mergeDir)

	# 删除merge以后产生的无用文件
	DeleteUnuselessFile(mergeDir)

	# zip文件
	print("begin zip dir...")
	zipUpgradeDir = mergeDir + ".zip"
	os.system('python zip_utils.py -a %s %s' % (mergeDir, zipUpgradeDir))

	print("\nbegin update version xml file...")
	# 修改记录发布版本的文件
	UpdateReleaseVersionFile(QUALITY, PACKMODE, mergeList[len(mergeList) - 1], zipUpgradeDir)

	# 记录当前热更新的版本号 最小的依赖版本号
	temMeta = mergeList[len(mergeList) - 1]
	UpdateReleaseVersionAttrib(QUALITY, PACKMODE, temMeta["currentVersion"], temMeta["minVersion"])

	# 获得记录版本号的文件md5码
	md5FileMd5 = FileUtils.GetFileMD5(GetReleaseVersionFile(QUALITY, PACKMODE))
	# 写入md5码到md5.txt
	md5Text = open(GetMd5TextFile(QUALITY, PACKMODE), "w")
	md5Text.write("%s\n%s\n%s\n" % (md5FileMd5, md5FileMd5, md5FileMd5))
	md5Text.flush()
	md5Text.close()

	# 上传到服务器
	uploadPath = GetIntraUploadPath(QUALITY, PACKMODE)
	uploadCommand = "scp %s root@%s" % (zipUpgradeDir, uploadPath)
	os.system(uploadCommand)
	uploadCommand = "scp %s root@%s" % (GetReleaseVersionFile(QUALITY, PACKMODE), uploadPath)
	os.system(uploadCommand)

	# 将更新版本设置为已经发布
	for temMeta in mergeList:
		UpdateLocalVersionFile(QUALITY, PACKMODE, temMeta)

	print("release end...")








