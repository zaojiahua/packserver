# coding:utf-8

import sys,os,time  
import ConfigParser  

# 读取配置文件模块
class Config:
    def __init__(self, path):  
        self.__path = path  
        self.__cf = ConfigParser.ConfigParser()  
        self.__cf.read(self.__path)  
    
    def Read(self, field, key):  
        result = ""  
        try:  
            result = self.__cf.get(field, key)  
        except:  
            result = ""  
        return result  
    
    def Write(self, field, key, value):  
        try:
            self.__cf.set(field, key, value)  
            self.__cf.write(open(self.__path, 'w'))  
        except:  
            return False  
        return True

    def GetFiledValues(self, field): 
        values = []
        try:
            for key, value in self.__cf.items(field):
                values.append(value)
        except:
            pass
        return values      
              
# 测试代码  
# cf = Config("cocos_pack_config.ini")
# ret = cf.Write("compresstype", "compresstype", "etc")
# print(ret)