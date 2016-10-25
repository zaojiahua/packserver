# coding=utf-8

#导入解析xml的模块
try:
	import xml.etree.cElementTree as et
except ImportError:
	import xml.etree.ElementTree as et
from xml.dom import minidom

import os

# xml解析的类
class XmlParser:
	
	def __init__(self, xmlfile):
		self.__file = xmlfile

	def GetXmlData(self):
		if False == os.path.isfile(self.__file):
			return False
		else:
			tree = et.ElementTree(file = self.__file)
			root = tree.getroot()
			return root

	def GetTagData(self, tag):
		md5Src = []
		root = self.GetXmlData()
		if False != root:
			#遍历根节点下所有具有tag标签的子元素
			for child in root.iter(tag):
				md5Src.append(child.attrib)
		return md5Src

	# 新增条目 将要写入的内容一块传过来，减少IO
	def WriteItemData(self, dataList, roottag, tag, callback):
		dir, filename = os.path.split(self.__file) 
		if False == os.path.exists(dir):
			os.makedirs(dir)
		
		filePath = self.__file

		#文件不存在
		if False == os.path.isfile(filePath):
			root = et.Element(roottag) 
			tree = et.ElementTree(root)  
		#文件存在
		else:
			tree = et.parse(filePath)
			root = tree.getroot()

		for data in dataList:
			#在root下新建子节点,设置其名称为file 
			fileNode = et.SubElement(root, tag)
			fileNode.attrib = data
			if None != callback:
				callback()
		#美化数据
		self.PrettyXml(root)
		tree.write(filePath, encoding="UTF-8") 

	#更新条目
	def UpdateDataItem(self, dataList, tag, unique, callback):
		filePath = self.__file
		tree = et.parse(filePath)
		root = tree.getroot()
		#一次IO 同时建立字典数据结构，快速遍历
		allChild = dict (map(lambda child:[child.attrib[unique], child], root.iter(tag)))
		for data in dataList:
			child = allChild[data[unique]]
			if None != child:
				child.attrib = data
			if None != callback:
				callback()

		tree.write(filePath, encoding="UTF-8")

	# 跟新root节点的属性
	def UpdateRootAttrib(self, attrib, rootName):
		if None == rootName:
			rootName = "files"

		filePath = self.__file
		tree = et.parse(filePath)
		root = tree.getroot()
		for files in root.iter(rootName):
			files.attrib = attrib
			tree.write(filePath, encoding="UTF-8")
			break

	#删除条目
	def DeleteDataItem(self, dataList, tag, unique, callback):
		filePath = self.__file
		#文件不存在
		if False == os.path.isfile(filePath):
			return
		else:
			tree = et.parse(filePath)
			root = tree.getroot()
			#一次IO 同时建立字典数据结构，快速遍历
			allChild = dict (map(lambda child:[child.attrib[unique], child], root.iter(tag)))
			for data in dataList:
				child = allChild[data[unique]] if data[unique] in allChild else None
				if None != child and child in root:
					root.remove(child)
				callback()
			tree.write(filePath, encoding="UTF-8")

	#美化xml格式
	def PrettyXml(self, element, indent="\t", newline="\n", level = 0): 
	    if element: 
	        if element.text == None or element.text.isspace():  
	            element.text = newline + indent * (level + 1)      
	        else:    
	            element.text = newline + indent * (level + 1) + element.text.strip() + newline + indent * (level + 1)    
	    temp = list(element)
	    for subelement in temp:    
	        if temp.index(subelement) < (len(temp) - 1): 
	            subelement.tail = newline + indent * (level + 1)    
	        else:
	            subelement.tail = newline + indent * level    
	        self.PrettyXml(subelement, indent, newline, level = level + 1)

	        