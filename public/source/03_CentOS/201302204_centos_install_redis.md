layout:     post
date:       2013-02-20
title:      安装Linux开发环境之四 —— Redis
slug:       centos-install-redis
author:     Ryan Liu
tags:       linux, redis
comments:   true

安装Redis
----------
下载 [redis-2.6.12](http://redis.googlecode.com/files/redis-2.6.12.tar.gz) 解压并进入目录

```bash
wget http://redis.googlecode.com/files/redis-2.6.12.tar.gz
tar xzf redis-2.6.12.tar.gz
cd redis-2.6.12
make
#make test 在v2.6.12版本root用户下存在bug
if [ `whoami` != "root" ]
    make test
fi
make PREFIX=/opt/redis-2.6.12 install
cd ..
```

启动Redis
------------
```bash
#复制配置
mkdir /opt/redis-2.6.12/etc/
cp redis-2.6.12/redis.conf /opt/redis-2.6.12/etc/
cp redis-2.6.12/sentinel.conf /opt/redis-2.6.12/etc/
#修改配置
sed -i '/^daemonize/c#daemonize no\ndaemonize yes' /opt/redis-2.6.12/etc/redis.conf
sed -i '/^loglevel notice/c#loglevel notice\nloglevel warning' /opt/redis-2.6.12/etc/redis.conf
sed -i '/^logfile stdout/c#logfile stdout\nlogfile /var/log/redis.log' /opt/redis-2.6.12/etc/redis.conf
#建立软连接
ln -s /opt/redis-2.6.12/bin/redis-server /usr/bin/redis-server
ln -s /opt/redis-2.6.12/bin/redis-cli /usr/bin/redis-cli
#启动
redis-server /opt/redis-2.6.12/etc/redis.conf
```

安装phpredis
------------
下载 [phpredis-2.2.2.tar.gz](https://github.com/nicolasff/phpredis/archive/2.2.2.tar.gz) ，重命名为 phpredis-2.2.2.tar.gz，解压并进入目录

```bash
wget https://github.com/nicolasff/phpredis/archive/2.2.2.tar.gz \
mv 2.2.2.tar.gz phpredis-2.2.2.tar.gz \
tar xzf phpredis-2.2.2.tar.gz \
cd phpredis-2.2.2 \
phpize \
./configure \
make && make install \
cd .. \
#添加到php.ini
sed -i '1a\extension=redis.so' /etc/php5/fpm/php.ini

ps -efww | grep php-fpm | grep -v grep | cut -c 9-15 | xargs kill -9
php-fpm -c /etc/php5/fpm/php.ini -y /etc/php5/fpm/php-fpm.conf -D
```
