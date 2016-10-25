#coding=utf-8
from __future__ import division

import os, os.path
import zipfile
import math
from file_utils import *

# 压缩文件夹
def ZipFile(dirname, zipfilename):
    fileList = FileUtils.GetAllFiles(dirname)
    zf = zipfile.ZipFile(zipfilename, "w", zipfile.zlib.DEFLATED)
    for index, tar in enumerate(fileList):
        arcname = tar[len(dirname):]
        zf.write(tar,arcname)
        Progressbar(index + 1, len(fileList))
    zf.close()

# 压缩文件
def ZipSingleFile(filename, zipfilename):
	zf = zipfile.ZipFile(zipfilename, "w", zipfile.zlib.DEFLATED)
	arcname = os.path.split(filename)[1]
	zf.write(filename, arcname)
	zf.close()
 
# 解压缩文件
def UnzipFile(zipfilename, unziptodir):
    if not os.path.exists(unziptodir): 
        os.makedirs(unziptodir, 0777)
    zfobj = zipfile.ZipFile(zipfilename)
    for index, name in enumerate(zfobj.namelist()):
        name = name.replace('\\','/')
        if name.endswith('/'):
            os.makedirs(os.path.join(unziptodir, name))
        else:            
            ext_filename = os.path.join(unziptodir, name)
            ext_dir= os.path.dirname(ext_filename)
            if not os.path.exists(ext_dir): 
                os.makedirs(ext_dir,0777)
            outfile = open(ext_filename, 'wb')
            outfile.write(zfobj.read(name))
            outfile.close()
        Progressbar(index + 1, len(zfobj.namelist()))

# 定义一个进度条 用来显示进度
def Progressbar(cur, total):
    percent = '{:.2%}'.format(cur / total)
    sys.stdout.write('\r')
    sys.stdout.write("[%-50s] %s" % (
                            '=' * int(math.floor(cur * 50 / total)),
                            percent))
    sys.stdout.flush()
 
if __name__ == '__main__':
    if 2 > len(sys.argv):
        print("use python zip_utils -h for help")
        sys.exit()
    if "-h" == sys.argv[1]:
        print("usage: python zip_utils.py [option] [arg]")
        print("-a arg :use to zip, arg is dirname zipfilename")
        print("-b arg :use to unzip, arg is zipfilename unziptodir")
        print("sample: python zip_utils.py -b ../test_zip.zip ../Update/etc/newest")
    elif "-a" == sys.argv[1]:
        try:
            ZipFile(sys.argv[2], sys.argv[3])
        except:
            print("porgram is error")
    elif "-b" == sys.argv[1]:
        try:
            UnzipFile(sys.argv[2], sys.argv[3])
        except:
            print("porgram is error")
    elif "-c" == sys.argv[1]:
			ZipSingleFile(sys.argv[2], sys.argv[3])