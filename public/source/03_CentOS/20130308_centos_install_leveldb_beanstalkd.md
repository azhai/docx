layout:     post
date:       2013-03-08
title:      安装Linux开发环境之五 —— LevelDB、Beanstalkd
slug:       centos-install-leveldb-beanstalkd
author:     Ryan Liu
tags:       linux, leveldb, beanstalkd
comments:   true

下载编译LevelDB备用
--------------------
```bash
wget http://leveldb.googlecode.com/files/leveldb-1.9.0.tar.gz
tar xzf leveldb-1.9.0.tar.gz -C /opt/
cd /opt/leveldb-1.9.0
make
ln -s /opt/leveldb-1.9.0/libleveldb.so.1.9 /usr/lib64/libleveldb.so
cd -
```

安装编译php-leveldb客户端
---------------------------
```bash
wget https://github.com/reeze/php-leveldb/archive/V0.1.1.tar.gz
mv V0.1.1.tar.gz php-leveldb-0.1.1.tar.gz
tar xzf php-leveldb-0.1.1.tar.gz
cd php-leveldb-0.1.1
phpize
./configure --with-leveldb=/opt/leveldb-1.9.0
make && make install
cd ..
```

参考 [https://github.com/nil-zhang/php-beanstalk/](https://github.com/nil-zhang/php-beanstalk/)

下载安装Beanstalkd
--------------------
```bash
wget https://github.com/kr/beanstalkd/archive/v1.9.tar.gz
mv v1.9.tar.gz beanstalkd-1.9.tar.gz
tar xzf beanstalkd-1.9.tar.gz
cd beanstalkd-1.9
sed -i '1cPREFIX=/opt/beanstalkd-1.9' Makefile
make && make install
cd ..
```

安装C客户端libbeanstalkclient
------------------------------
```bash
#安装libtool
yum install libtool
#下载安装C客户端
wget https://github.com/bergundy/libbeanstalkclient/tarball/master --no-check-certificate
mv master libbeanstalkclient.tar.gz
tar xzf libbeanstalkclient.tar.gz
cd bergundy-libbeanstalkclient-b6ec294/
sed -i '6c./configure --prefix=/opt/libbeanstalkclient' autogen.sh
./autogen.sh
#添加到ld.so.conf
echo "/opt/libbeanstalkclient/lib" > /etc/ld.so.conf.d/libbeanstalkclient.conf
ldconfig
cd ..
```

安装PHP客户端
--------------
```bash
wget https://github.com/nil-zhang/php-beanstalk/tarball/master --no-check-certificate
mv master php-beanstalk.tar.gz
tar xzf php-beanstalk.tar.gz
cd nil-zhang-php-beanstalk-098935b/
phpize
./configure --with-libbeanstalkclient-dir=/opt/libbeanstalkclient
make && make install
cd ..
```

添加PHP扩展到php.ini，重启php-fpm服务
--------------------------------------
```bash
ln -s /opt/beanstalkd-1.9/bin/beanstalkd /usr/bin/beanstalkd
sed -i '1aextension=leveldb.so' /etc/php5/fpm/php.ini
sed -i '1aextension=beanstalk.so' /etc/php5/fpm/php.ini
ps -efww | grep php-fpm | grep -v grep | cut -c 9-15 | xargs kill -9
php-fpm -c /etc/php5/fpm/php.ini -y /etc/php5/fpm/php-fpm.conf -D
```
