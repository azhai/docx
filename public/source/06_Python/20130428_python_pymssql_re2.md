layout:     post
date:       2013-04-28
title:      安装Python支持SqlServer和re2
slug:       python-pymssql-re2
author:     Ryan Liu
tags:       python, sqlserver, re2
comments:   true

如果在Linux下需要python连接Microsoft的SqlServer

新版v2安装

```bash
yum install python-devel python-setuptools
easy_install pip
pip install https://github.com/msabramo/pymssql/archive/master.zip
```

或者

```bash
git clone https://github.com/msabramo/pymssql.git
cd pymssql
python setup.py install

#旧版v1.0.2安装
wget http://dl.fedoraproject.org/pub/epel/6/x86_64/pymssql-1.0.2-4.el6.x86_64.rpm
wget http://dl.fedoraproject.org/pub/epel/6/x86_64/freetds-0.91-2.el6.x86_64.rpm
wget http://mirror.centos.org/centos/6/os/x86_64/Packages/unixODBC-2.2.14-12.el6_3.x86_64.rpm

rpm -ivh unixODBC-2.2.14-12.el6_3.x86_64.rpm
rpm -ivh freetds-0.91-2.el6.x86_64.rpm
rpm -ivh pymssql-1.0.2-4.el6.x86_64.rpm
```

pyre2，比内置模块re更快

```bash
wget https://re2.googlecode.com/files/re2-20130115.tgz
tar xzf re2-20130115.tgz
cd re2
make && make install
pip install re2
```
