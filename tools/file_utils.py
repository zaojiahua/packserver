# coding=utf-8

import sys
import os
# 高层次文件操作工具
import shutil
#导入获取md5码的模块
import hashlib

# 文件工具类
class FileUtils:

	# 获取某一目录下的所有文件列表
	@staticmethod
	def GetAllFiles(filePath):
		srcFileList = []
		for root, dir, files in os.walk(filePath):
			for file in files:
				srcFileList.append(os.path.join(root, file))
		return srcFileList

	# 获得文件的md5码
	@staticmethod
	def GetFileMD5(filepath):
	    with open(filepath, 'rb') as f:
	        md5obj = hashlib.md5()
	        md5obj.update(f.read())
	        md5 = md5obj.hexdigest()
	        return md5

	# 获得某一目录下的同名文件
	@staticmethod
	def GetFileNameInDir(fileName, oldDirName, newDirName):
		filePath, fileName = os.path.split(fileName)
		desFileName = os.path.join(newDirName, filePath[len(oldDirName):], fileName)
		return desFileName.replace("\\", "/")

	# 文件拷贝
	@staticmethod
	def CopyFile(srcFileName, desFileName):
		desFilePath, _ = os.path.split(desFileName)
		if not os.path.exists(desFilePath):
			os.makedirs(desFilePath)
		if os.path.isfile(srcFileName):
			open(desFileName, "wb").write(open(srcFileName, "rb").read())

	# 目录删除
	@staticmethod
	def DeleteDir(dirName):
		if os.path.exists(dirName):
			shutil.rmtree(dirName)

	# 删除文件
	@staticmethod
	def DeleteFile(fileName):
		if os.path.isfile(fileName):
			os.remove(fileName)

	# 拷贝目录 olddir和newdir都只能是目录，且newdir必须不存在
	@staticmethod
	def CopyDir(oldDir, newDir):
		shutil.copytree(oldDir, newDir)
