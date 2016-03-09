layout:     post
date:       2013-04-12
title:      Rsync配置与使用
slug:       rsync-config
author:     Ryan Liu
tags:       rsync
comments:   true

服务端
--------
安装rsync并创建配置目录和文件

```bash
yum install rsync
mkdir -p /etc/rsync
touch /etc/rsync/rsyncd.conf /etc/rsync/rsyncd.secrets /etc/rsync/rsyncd.motd
echo "root:changeme" > /etc/rsync/rsyncd.secrets
echo "Welcome!" > /etc/rsync/rsyncd.motd
chmod 600 /etc/rsync/rsyncd.secrets

```

/etc/rsync/rsyncd.conf文件内容如下，一定要设置为root用户

```nginx
pid file = /var/run/rsyncd.pid
port = 873
address = 192.168.1.23
uid = root
gid = root

use chroot = yes
read only = no

#limit access to private LANs
#设置可访问的主机：如果多个ip则用空格隔开：192.168.0.3 192.168.0.4 192.168.0.5或者设置区间 192.168.0.3/5
hosts allow = 192.168.1.0/24
hosts deny = *

max connections = 5
motd file = /etc/rsync/rsyncd.motd

#This will give you a separate log file
log file = /var/log/rsync.log

#This will log every file transferred - up to 85,000+ per user, per sync
transfer logging = yes

log format = %t %a %m %f %b
syslog facility = local3
timeout = 300


[projects]
#要同步服务器的目录路径
path = /home/ryan/projects
list = yes
ignore errors
#真实的系统用户，多个用户用逗号隔开
auth users = root
#密码文件
secrets file = /etc/rsync/rsyncd.secrets
comment = projects
#不同步的目录或文件，用空格隔开
exclude = .svn/ *.rar
```

客户端
-------
安装rsync并创建传输脚本

```bash
mkdir -p /home/ryan/bin
touch /home/ryan/bin/rsync_projects.sh
chmod +x /home/ryan/bin/rsync_projects.sh
echo "changeme" > /home/ryan/bin/rsync.pass
chmod 600 /home/ryan/bin/rsync.pass
```

/home/ryan/bin/rsync_projects.sh的内容如下，为了在定时任务中使用，要用完整路径

```bash
#!/bin/bash
/usr/bin/rsync -vzrtopg --progress --delete /home/ryan/projects/* \
    root@192.168.1.50::projects --password-file=/home/ryan/bin/rsync.pass
```
