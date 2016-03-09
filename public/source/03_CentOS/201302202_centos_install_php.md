layout:     post
date:       2013-02-20
title:      安装Linux开发环境之二 —— PHP
slug:       centos-install-php
author:     Ryan Liu
tags:       linux, php
comments:   true

> 2013-4-15更新，使用v5.4.14，增加apc、xdebug和phpunit扩展

安装必须组件
------------
下载 [libmcrypt-2.5.8.tar.gz](http://downloads.sourceforge.net/project/mcrypt/Libmcrypt/2.5.8/libmcrypt-2.5.8.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fmcrypt%2Ffiles%2FLibmcrypt%2F2.5.8%2F&ts=1361339383&use_mirror=nchc) 并解压

```bash
#安装开发库
yum install bzip2-devel gmp-devel openssl-devel curl-devel freetype-devel
yum install libxml2-devel libpng-devel libjpeg-devel libicu-devel libc-client-devel
yum install libtool libtool-ltdl-devel
#安装libmcrypt
wget http://downloads.sourceforge.net/project/mcrypt/Libmcrypt/2.5.8/libmcrypt-2.5.8.tar.gz?r=http%3A%2F%2Fsourceforge.net%2Fprojects%2Fmcrypt%2Ffiles%2FLibmcrypt%2F2.5.8%2F&ts=1361339383&use_mirror=nchc
tar xzf libmcrypt-2.5.8.tar.gz
cd libmcrypt-2.5.8
./configure
make && make install
cd ..
```

安装PHP
------------
下载 [php-5.4.14.tar.gz](http://cn2.php.net/get/php-5.4.14.tar.gz/from/this/mirror) 解压并进入目录

```bash
wget http://cn2.php.net/get/php-5.4.14.tar.gz/from/this/mirror
tar xzf php-5.4.14.tar.gz
cd php-5.4.14
#如果configure报错Note that the MySQL client library is not bundled anymore.
#请将/opt/mysql-5.6.11/lib 建立软连接 /opt/mysql-5.6.11/lib64
#64位Linux
if [ `uname -m` =~ 'x86_64' ]; then
    ln -s /usr/local/lib/libmcrypt.so.4 /usr/local/lib64/libmcrypt.so.4
    ln -s /opt/mysql-5.6.11/lib /opt/mysql-5.6.11/lib64
    gnu_x86_64="--build=x86_64-redhat-linux-gnu --host=x86_64-redhat-linux-gnu --with-libdir=lib64"
else
    gnu_x86_64=""
fi
username=`whoami` #当前用户名

./configure --prefix=/opt/php-5.4.14 \
--with-mysql=/opt/mysql-5.6.11 --with-mysqli=/opt/mysql-5.6.11/bin/mysql_config \
--with-pdo-mysql=/opt/mysql-5.6.11 --with-mysql-sock=/tmp/mysql.sock \
--with-mcrypt=/usr/local --with-zlib \
--with-pic --with-curl=shared --with-freetype-dir --with-png-dir \
--with-gettext=shared --with-gmp=shared --with-iconv --with-jpeg-dir --with-png-dir \
--with-openssl --with-libxml-dir --with-pcre-regex \
--with-kerberos --with-imap --with-imap-ssl \
--with-pear --with-gd --enable-gd-native-ttf --enable-calendar=shared \
--enable-exif --enable-ftp --enable-sockets --enable-bcmath=shared \
--enable-pcntl --enable-intl --enable-mbstring \
--enable-zip --with-bz2=shared \
--enable-sysvsem --enable-sysvshm --enable-sysvmsg \
--without-unixODBC --enable-mbregex \
--enable-tokenizer --disable-phar --with-sqlite3 \
--enable-fpm --with-fpm-user="$username" --with-fpm-group="$username" \
--with-layout=GNU --with-mssql=/opt/freetds-0.91 "$gnu_x86_64"
make && make install
cd ..
```

配置PHP
------------

```bash
#建立软连接
rm -rf /usr/sbin/php-fpm
rm -rf /usr/bin/pear
rm -rf /usr/bin/pecl
rm -rf /usr/bin/php*
ln -s /opt/php-5.4.14/bin/php /usr/bin/php
ln -s /opt/php-5.4.14/bin/phpize /usr/bin/phpize
ln -s /opt/php-5.4.14/bin/pear /usr/bin/pear
ln -s /opt/php-5.4.14/bin/pecl /usr/bin/pecl
ln -s /opt/php-5.4.14/bin/php-config /usr/bin/php-config
ln -s /opt/php-5.4.14/sbin/php-fpm /usr/sbin/php-fpm
#配置文件
mkdir -p /etc/php5/fpm/
cp php-5.4.14/php.ini-production /opt/php-5.4.14/etc/php.ini
cp /opt/php-5.4.14/etc/php-fpm.conf.default /opt/php-5.4.14/etc/php-fpm.conf
ln -s /opt/php-5.4.14/etc/php.ini /etc/php5/fpm/php.ini
ln -s /opt/php-5.4.14/etc/php-fpm.conf /etc/php5/fpm/php-fpm.conf

#修改配置
sed -i '/^max_execution_time/cmax_execution_time = 600' /etc/php5/fpm/php.ini
sed -i '/^error_reporting/cerror_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE' /etc/php5/fpm/php.ini
sed -i '/^display_errors/cdisplay_errors = On' /etc/php5/fpm/php.ini
sed -i '/^display_startup_errors/cdisplay_startup_errors = On' /etc/php5/fpm/php.ini
sed -i '/^track_errors/ctrack_errors = On' /etc/php5/fpm/php.ini
sed -i '/^upload_max_filesize/cupload_max_filesize = 20M' /etc/php5/fpm/php.ini
sed -i '/^;date.timezone/cdate.timezone = Asia/Shanghai' /etc/php5/fpm/php.ini

#添加常用扩展
pecl install apc
sed -i '1aextension=apc.so' /etc/php5/fpm/php.ini
pecl install xdebug
#ThreadSafe版本，带有_ts
sed -i '1azend_extension_ts=xdebug.so' /etc/php5/fpm/php.ini

#修改php-fpm的进程数
username=`whoami` #当前用户名
#sed -i "/^user = $username/c#user = $username" /etc/php5/fpm/php-fpm.conf
#sed -i "/^group = $username/c#group = $username" /etc/php5/fpm/php-fpm.conf
sed -i '/^pm.max_children/cpm.max_children = 30' /etc/php5/fpm/php-fpm.conf
sed -i '/^pm.start_servers/cpm.start_servers = 2' /etc/php5/fpm/php-fpm.conf
sed -i '/^pm.min_spare_servers/cpm.min_spare_servers = 2' /etc/php5/fpm/php-fpm.conf
sed -i '/^pm.max_spare_servers/cpm.max_spare_servers = 30' /etc/php5/fpm/php-fpm.conf
```

管理PHP
------------
```bash
#启动
php-fpm -c /etc/php5/fpm/php.ini -y /etc/php5/fpm/php-fpm.conf -D
#停止
ps -efww | grep php-fpm | grep -v grep | cut -c 9-15 | xargs kill -9
```

> 2013.3.5更新

需要连接SQLServer
------------
```bash
#先安装FreeTDS
wget ftp://ftp.astron.com/pub/freetds/stable/freetds-stable.tgz
tar xzf freetds-stable.tgz
cd freetds-0.91
./configure --prefix=/opt/freetds-0.91 --with-tdsver=8.0 --enable-msdblib
make && make install
cd ..

#64位系统下（CentOS 6.3有这个问题，其他发行版不知道是不是也这样），还需要
if [ `uname -m` =~ 'x86_64' ]; then
    ln -s /opt/freetds-0.91/lib /opt/freetds-0.91/lib64
fi

#再来编译安装PHP，配置参数参考上面，在末尾多加一个 --with-mssql=/opt/freetds-0.91
```
