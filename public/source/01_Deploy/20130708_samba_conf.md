layout:     post
date:       2013-07-08
title:      Samba简单配置
slug:       samba-conf
author:     Ryan Liu
tags:       samba
comments:   true


简化的samba配置 /etc/samba/smb.conf
----------------------
```nginx
#添加用户ryan，Samba不依赖于系统用户
#gpasswd -a ryan
#生效
#/etc/init.d/smbd restart
 
[global]
  workgroup = WORKGROUP
  server string = Smaba %V
  hosts allow = 192.168.
  security = user
  passdb backend = tdbsam
  log file = /var/log/samba/%m.log
  max log size = 50
 
[www]
  path = /var/www
  browseable = yes
  writable = yes
  valid users = ryan
```
