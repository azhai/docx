layout:     post
date:       2013-02-20
title:      安装Linux开发环境之一 —— MySQL
slug:       centos-install-mysql
author:     Ryan Liu
tags:       linux, mysql
comments:   true

服务器环境
------------
阿里云主机 CentOS 6.3 X64

安装编译环境
------------
```bash
yum update  #更新组件
yum install gcc gcc-c++ make cmake binutils
```

安装MySQL v5.6.11
-----------------
安装开发库，建立用户、用户组

```bash
groupadd mysql
useradd -r -g mysql mysql
yum install zlib-devel ncurses-devel readline-devel
```

下载 [mysql-5.6.11.tar.gz](http://cdn.mysql.com/Downloads/MySQL-5.6/mysql-5.6.11.tar.gz)
解压后进入目录

```bash
wget http://cdn.mysql.com/Downloads/MySQL-5.6/mysql-5.6.11.tar.gz
tar xzf mysql-5.6.11.tar.gz
cd mysql-5.6.11
#安装全部字符集请使用 -DEXTRAT_CHARSETS=all
#ENABLED_LOCAL_INFILE选项为“允许从本地导入数据”
cmake . \
    -DCMAKE_INSTALL_PREFIX=/opt/mysql-5.6.11 \
    -DINSTALL_DATADIR=/opt/mysql-5.6.11/data \
    -DDEFAULT_CHARSET=utf8 \
    -DDEFAULT_COLLATION=utf8_general_ci \
    -DEXTRAT_CHARSETS=latin1,gbk \
    -DENABLED_LOCAL_INFILE=1
make && make install
#大于半小时的漫长等待中......
cd ..
```

配置MySQL
--------------
```bash
#必要的软连接和目录
ln -s /opt/mysql-5.6.11/bin/mysqld_safe /usr/bin/mysqld_safe
ln -s /opt/mysql-5.6.11/bin/mysql /usr/bin/mysql
ln -s /opt/mysql-5.6.11/bin/mysqldump /usr/bin/mysqldump
ln -s /opt/mysql-5.6.11/bin/mysqlimport /usr/bin/mysqlimport
ln -s /opt/mysql-5.6.11/bin/mysql_config /usr/bin/mysql_config
mkdir /var/run/mysqld
chmod -R 777 /var/run/mysqld

#初始化MySQL
/opt/mysql-5.6.11/scripts/mysql_install_db --user=mysql --basedir=/opt/mysql-5.6.11 --datadir=/opt/mysql-5.6.11/data
#64位Linux
if [ `uname -m` =~ 'x86_64' ]; then
  ln -s /opt/mysql-5.6.11/lib  /opt/mysql-5.6.11/lib64
fi
#设置配置文件
if [ -f /etc/my.cnf ]; then
  mv /etc/my.cnf /etc/my.cnf.bak
fi
if [ -f /opt/mysql-5.6.11/my.cnf ]; then
  cp /opt/mysql-5.6.11/my.cnf /etc/my.cnf
else
  cp /opt/mysql-5.6.11/support-files/my-default.cnf /etc/my.cnf
fi
sed -i '/# basedir =/cbasedir = \/opt\/mysql-5.6.11' /etc/my.cnf
sed -i '/# datadir =/cdatadir = \/opt\/mysql-5.6.11\/data' /etc/my.cnf
sed -i '/# port =/cport = 3306' /etc/my.cnf

#手工启动
mysqld_safe --user=mysql &
#更新
/opt/mysql-5.6.11/bin/mysql_upgrade
#手工停止
/opt/mysql-5.6.11/bin/mysqladmin -u root -p shutdown

#设置系统服务
cp /opt/mysql-5.6.11/support-files/mysql.server /etc/init.d/mysql
sed -i '/^basedir=/cbasedir=\/opt\/mysql-5.6.11' /etc/init.d/mysql
sed -i '/^datadir=/cdatadir=\/opt\/mysql-5.6.11\/data' /etc/init.d/mysql
#重启mysql服务
/etc/init.d/mysql restart
```

添加账户和权限
-----------------
```bash
echo "#! /usr/bin/mysql" > /tmp/t.sql
echo "DELETE FROM \`user\` WHERE User='';" >> /tmp/t.sql
echo "DELETE FROM \`db\` WHERE User='';" >> /tmp/t.sql
echo "SET PASSWORD FOR 'root'@'localhost'=PASSWORD('changeme');" >> /tmp/t.sql
echo "SET PASSWORD FOR 'root'@'127.0.0.1'=PASSWORD('changeme');" >> /tmp/t.sql
echo "SET PASSWORD FOR 'root'@'::1'=PASSWORD('changeme');" >> /tmp/t.sql
echo "SET PASSWORD FOR 'root'@'`hostname`'=PASSWORD('changeme');" >> /tmp/t.sql
echo "GRANT ALL PRIVILEGES ON \`db\\_%\`.* TO 'dba'@'localhost' IDENTIFIED BY 'changeme' WITH GRANT OPTION;" >> /tmp/t.sql
echo "GRANT ALL PRIVILEGES ON \`db\\_%\`.* TO 'dba'@'192.168.0.%' IDENTIFIED BY 'changeme' WITH GRANT OPTION;" >> /tmp/t.sql
echo "FLUSH PRIVILEGES;" >> /tmp/t.sql
mysql -u root -p mysql < /tmp/t.sql
rm -f /tmp/t.sql
```
