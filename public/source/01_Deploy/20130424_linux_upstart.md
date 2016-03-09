layout:     post
date:       2013-04-24
title:      Linux新的守护进程方式upstart
slug:       linux-upstart
author:     Ryan Liu
tags:       centos, init, upstart
comments:   true

以前的Linux系统都使用System V风格的 rc.d/ rc.conf rc.local等目录和文件，在开机启动时加载额外的进程。
现在Ubuntu和CentOS改用upstart（命令initctl），ArchLinux改用systemd（systemctl），除了开机启动时加载，
还能守护进程，比python写的supervisor好用多了（这是个泥菩萨，常常自己挂掉了）。

upstart配置方法
-----------------
环境：CentOS (v6.3 v6.4)
先在/etc/init/下面写个.conf的配置文件，如PHP-FPM的：/etc/init/php-fpm.conf

```bash
# php-fpm
description "PHP FastCGI Process Manager"

start on (net-device-up and local-filesystems)
stop on runlevel [!2345]

env DAEMON="/usr/sbin/php-fpm -c /etc/php-fpm.conf -D"

expect fork
respawn
exec $DAEMON
```

nginx可以更智能一点： /etc/init/nginx.conf

```bash
# nginx
description "nginx http daemon"

start on (filesystem and net-device-up IFACE=lo)
stop on runlevel [!2345]

env DAEMON=/usr/sbin/nginx
env PID=/var/run/nginx.pid

expect fork
respawn
respawn limit 10 5
#oom never

pre-start script
        $DAEMON -t
        if [ $? -ne 0 ]
                then exit $?
        fi
end script

exec $DAEMON
```

使用 initctl start/stop/restart php-fpm 来启动和关闭php进程，启动后进程被守护。
要注意的是，用别的方式启动的php-fpm进程，用initctl stop无法杀掉。

<s>按网上说法这样就可以在系统开机时启动它们，实际上不行，得自己往/etc/rc.d/rc.local里写</s>
千万别这样，如果有个任务卡在那里，就进不了登录图形界面了。实际上CentOS有在启动阶段使用它们，因为不满足条件
（去掉filesystem and net-device-up IFACE=lo试试），或者执行exec命令失败（比如权限问题，用exec su -s /bin/sh -c）。

对于MySQL，安装文件里提供了旧方式的服务文件，使用下面方式保证开机启动

```bash
cp /opt/mysql-5.6.10/support-files/mysql.server /etc/init.d/mysql
chkconfig enable mysql
```
