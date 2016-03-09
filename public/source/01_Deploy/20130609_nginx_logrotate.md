layout:     post
date:       2013-06-09
title:      Nginx日志按日期切割
slug:       nginx-logrotate
author:     Ryan Liu
tags:       log, logrotate, nginx
comments:   true


```bash
#!/bin/bash
 
cd /opt/nginx-1.4.7/logs/
#分解旧日志
mkdir -p mysite
gawk '{
    dt=substr($4,2,11); 
    gsub(/\//," ",dt); 
    "date -d \""dt"\" +%Y%m%d"|getline dd; 
    print $0 >> "mysite/mysite.access.log-"dd
}' mysite.access.log
 
#将当天部分重命名，避免被覆盖
today=`date +%Y%m%d`
todaylog="mysite.access.log-$today" 
if [ -f "$todaylog" ]; then
    mv $todaylog "{$todaylog}.head"
fi
 
 
#配置切割日志
cat > /etc/logrotate.d/nginx <<EOD
/opt/nginx-1.4.7/logs/mysite.access.log {
    notifempty
    nocompress
    daily
    dateext
    olddir /opt/nginx-1.4.7/logs/mysite/
    copytruncate
    create 0600 root root
    rotate 30 #保存最近30天的日志

    prerotate
        sleep 59 #休眠59秒
    endscript

    postrotate
        kill -USR1 `cat /opt/nginx-1.4.7/logs/nginx.pid`
    endscript
}
EOD
 
#将下面的脚本加入crontab
cronline='59 23 * * *  /usr/sbin/logrotate -f /etc/logrotate.d/nginx'
#确保当前用户的crontab文件存在，否则crontab -l会有输出no crontab for xxx
curruser=`id -un`
touch "/var/spool/cron/$curruser"
(crontab -l; echo "$cronline") | crontab -
```
