# coding=utf-8

from file_utils import *
import os
import sys

# 合并历次更新的文件 updateDir更新目录 version合并到某一版本号 dirName合并目录必须是空的
def MergeUpdateDir(updateDir, version, dirName):
	allDirFile = os.listdir(updateDir)
	allDir = []
	try:
		updateVersion = int(version.split(".")[3])
	except:
		sys.exit("version format is error...")
	
	# 获得要merge的目录 并且根据版本号从小到大进行排序
	for fileName in allDirFile:
		if True == os.path.isdir(os.path.join(updateDir, fileName)):
			splitFileName = fileName.split(".")
			# 只对更新目录进行操作
			if 4 == len(splitFileName):
				try:
					tem_version = int(splitFileName[3])
					# 合并到需要的版本
					if tem_version <= updateVersion:
						allDir.append(fileName)
				except:
					print("%s is not update dir" % fileName)

	# 排序
	def DirSort(x, y):
		x = int(x.split(".")[3])
		y = int(y.split(".")[3])
		return cmp(x, y)
	allDir.sort(DirSort)

	# 建立合并的目录
	if False == os.path.exists(dirName):
		os.mkdir(dirName)
	else:
		sys.exit("dirName is exists...")

	# merge目录
	for tempDir in allDir:
		for fileName in FileUtils.GetAllFiles(os.path.join(updateDir, tempDir)):
			desFileName = FileUtils.GetFileNameInDir(fileName, os.path.join(updateDir, tempDir) + "/", dirName)
			FileUtils.CopyFile(fileName, desFileName)

# 产生一个最新的版本目录 最后一个参数代表merge出来的目录名称
def MergeNewestDir(updateDir, version, dirName):
	# 不能存在要merge的目录
	if True == os.path.exists(dirName):
		sys.exit("dirName is exists...")
	
	# 拷贝base文件夹的内容到这个目录中
	baseDir = os.path.join(updateDir, "base")
	if False == os.path.exists(baseDir):
		sys.exit("base dir is missing...")

	print("begin merge dir...")
	FileUtils.CopyDir(baseDir, dirName)

	# merge更新目录
	tempMergeUpdateDir = os.path.join(updateDir, "temp/")
	MergeUpdateDir(updateDir, version, tempMergeUpdateDir)

	# 将更新目录和newest目录合并
	for fileName in FileUtils.GetAllFiles(tempMergeUpdateDir):
		desFileName = FileUtils.GetFileNameInDir(fileName, tempMergeUpdateDir, dirName)
		# print("%s %s" % (fileName, desFileName))
		FileUtils.CopyFile(fileName, desFileName)
	
	# 删除临时文件夹
	FileUtils.DeleteDir(tempMergeUpdateDir)

	print("merge dir end...")

if __name__ == "__main__":
	if 2 > len(sys.argv):
		print("use python cocos_newest_update -h for help")
		sys.exit()
	if "-h" == sys.argv[1]:
		print("usage: python cocos_newest_update.py [option] [arg]")
		print("-a arg :arg is updatedir version generatedir")
		print("-b arg :arg is updatedir version generatedir")
		print("sample: python cocos_newest_update.py -b ./Update/etc/ 0.0.0.200 ../Update/etc/newest")
	elif "-a" == sys.argv[1]:
		try:
			MergeUpdateDir(sys.argv[2], sys.argv[3], sys.argv[4])
		except:
			print("porgram is error")
	elif "-b" == sys.argv[1]:
		try:
			MergeNewestDir(sys.argv[2], sys.argv[3], sys.argv[4])
		except:
			print("porgram is error")
