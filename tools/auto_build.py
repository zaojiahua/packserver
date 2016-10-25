# coding=utf-8

# 服务器打包外部包装的一个脚本 内部调用san_slg工程下面的脚本

import os
import sys
from file_utils import *

# # 定义本地热更新目录
# LOCAL_UPDATE_PATH = "/Users/san/packserver/bin/Update_server_"
# # 定义内网热跟新目录
# INTRANET_UPDATE_PATN = "10.241.107.31:/app/frontPack/intratest/"
# # 定义外网热更新目录
# EXTRANET_UPDATE_PATH = "10.241.107.31:/"

# 添加环境变量 这里主要针对外部进程调用Python语言 Python的环境变量继承了父进程
NDK_PATH = "/Users/san/enviroment/sdk/ndk-bundle"
NDK_MODULE_PATH = "/Users/san/frontend/cocos2d/cocos:/Users/san/frontend/cocos2d/external:/Users/san/frontend/cocos2d"
GRADLE_PATH = "/Users/san/enviroment/gradle-2.14.1/bin"
GRADLE_HOME = "/Users/san/enviroment/gradle-2.14.1"
if 'PATH' in os.environ:
	os.environ['PATH'] = os.environ['PATH'] + os.pathsep + NDK_PATH + os.pathsep + GRADLE_PATH
# 服务器运行脚本的时候需要这些环境变量
else:
	os.environ['PATH'] = NDK_PATH + os.pathsep + GRADLE_PATH
	os.environ['PATH'] = os.environ['PATH'] + os.pathsep + '/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin' + ':/Library/Java/JavaVirtualMachines/jdk1.8.0_101.jdk/Contents/Home/bin'
	os.environ['NDK_MODULE_PATH'] = NDK_MODULE_PATH
	os.environ['GRADLE_USER_HOME'] = GRADLE_HOME

packParams = ""
for params in sys.argv[1:]:
	packParams = packParams + " " + params

os.system("python %s" % packParams)

# 将热更新文件上传到服务器
# if "-time" in sys.argv:
# 	PACK_TIME = sys.argv[sys.argv.index("-time") + 1]
# 	if "-v" in sys.argv:
# 		PACK_VERSION = sys.argv[sys.argv.index("-v") + 1]
# 		PACK_NAME = PACK_VERSION + "_" + PACK_TIME + ".zip"
# 	if "-m" in sys.argv:
# 		if "etc" in sys.argv:
# 			LOCAL_UPDATE_FILE = LOCAL_UPDATE_PATH + "etc" + "/" + PACK_NAME
# 		elif "pvr" in sys.argv:
# 			LOCAL_UPDATE_FILE = LOCAL_UPDATE_PATH + "pvr" + "/" + PACK_NAME
# 	uploadCommand = "scp %s root@%s" % (LOCAL_UPDATE_FILE, INTRANET_UPDATE_PATN)
# 	os.system(uploadCommand)
		

# 删除某一个文件
if "-d" in sys.argv:
	FileUtils.DeleteFile(sys.argv[sys.argv.index("-d") + 1])