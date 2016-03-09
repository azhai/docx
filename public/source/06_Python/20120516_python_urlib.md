layout:     post
date:       2012-05-16
title:      Python网址操作函数
slug:       python-urlib
author:     Ryan Liu
tags:       python, http, curl
comments:   true

```python
# -*- coding:utf-8 -*-

import urllib
import urlparse
from httplib import HTTPConnection


def url_add_params(url, **params):
    """ 在网址中加入新参数 """
    pr = urlparse.urlparse(url)
    query = dict(urlparse.parse_qsl(pr.query))
    query.update(params)
    prlist = list(pr)
    prlist[4] = urllib.urlencode(query)
    return urlparse.ParseResult(*prlist).geturl()


class HttpChecker:
    """ 检测网址是否存在 """

    def __init__(self, domain):
        if "//" in domain: #网址，不止是域名
            self.netloc = urllib.urlsplit(domain).netloc
        else:
            self.netloc = domain

    def __enter__(self):
        self.connection = HTTPConnection(self.netloc)
        return self

    def __exit__(self, exc_type, exc_value, traceback):
        del self.connection

    def check(self, url, splited=False):
        status = 0
        if splited: #已经是网址中域名以后部分，必须以/开头
            path = url
        else:
            netloc, path = urllib.urlsplit(url)[1:3]
            if netloc and netloc != self.netloc:
                self.netloc = netloc
                self.connection = HTTPConnection(self.netloc)
        self.connection.connect()
        self.connection.request("HEAD", path)
        status = self.connection.getresponse().status
        self.connection.close()
        return status == 200


if __name__ == "__main__":
    url = 'http://bbs.163.com/viewthread.php?tid=1660&rpid=5798&ordertype=0&page=1#pid5798'
    print url_add_params(url, token=123, site="bbs")
    with HttpChecker("www.google.com.hk") as hc:
        print hc.check("http://www.google.com.hk/intl/zh-CN/options/")
```