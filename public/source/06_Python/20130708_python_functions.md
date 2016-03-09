layout:     post
date:       2013-07-08
title:      收集用过的一些Python方法
slug:       python-functions
author:     Ryan Liu
tags:       python, function
comments:   true

```python
# -*- coding:utf-8 -*-

#设置字符编码为UTF-8
import sys
import os, os.path
os.chdir(os.path.dirname(os.path.realpath(__file__)))
if sys.getdefaultencoding() != 'utf-8':
    try:
        reload(sys)
        sys.setdefaultencoding('utf-8')
    except AttributeError:
        pass


#用于Python List类型的去重复，并保持元素原有次序
def unique_list(seq, excludes=[]):
    """
    返回包含原列表中所有元素的新列表，将重复元素去掉，并保持元素原有次序
    excludes: 不希望出现在新列表中的元素们
    """
    seen = set(excludes)  # seen是曾经出现的元素集合
    return [x for x in seq if x not in seen and not seen.add(x)]


#CPython
def get_mac_addr():
    """ 获得本机网卡物理地址 """
    import uuid
    node = uuid.getnode()
    mac = uuid.UUID(int=node)
    addr = mac.hex[-12:]
    return addr


#IronPyton
from System.Net.NetworkInformation import NetworkInterface, NetworkInterfaceType

def getMacAddress():
    """ 获得本机网卡物理地址（IronPyton版本） """
    for netcard in NetworkInterface.GetAllNetworkInterfaces():
        if netcard.NetworkInterfaceType == NetworkInterfaceType.Ethernet:
            return netcard.GetPhysicalAddress().ToString()
    return ''


def check_id_num(id_num):
    """ 验证18位身份证号码 """
    assert len(id_num) == 18 and id_num[:17].isdigit()
    factors = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2]
    remainders = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2']
    result = sum([f*long(n) for f,n in zip(factors, id_num)])
    return remainders[result % 11] == id_num[-1]


def get_constellation(ymd='2000-01-01'):
    """ 从生日得到星座 """
    stellates = [
        {'date':120, 'name':u'水瓶座'},
        {'date':219, 'name':u'双鱼座'},
        {'date':321, 'name':u'牡羊座'},
        {'date':420, 'name':u'金牛座'},
        {'date':521, 'name':u'双子座'},
        {'date':622, 'name':u'巨蟹座'},
        {'date':723, 'name':u'狮子座'},
        {'date':823, 'name':u'处女座'},
        {'date':923, 'name':u'天秤座'},
        {'date':1024, 'name':u'天蝎座'},
        {'date':1123, 'name':u'射手座'},
        {'date':1222, 'name':u'魔羯座'}
    ]
    if not ymd or ymd == '0000-00-00':
        return u'未知'
    ymd = int(ymd[5:7]+ymd[8:10])
    index = ymd / 100 - 1
    if ymd >= stellates[index]['date']:
        return stellates[index]['name']
    else:
        return stellates[index-1]['name']
```