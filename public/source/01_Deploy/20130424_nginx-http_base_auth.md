---
layout:     post
date:       2013-04-24
title:      Nginx设置简单HTTP认证
slug:       nginx-http-base-auth
author:     Ryan Liu
tags:       nginx, auth
comments:   true
---

## 问题描述

设置某个目录，只允许固定的用户使用密码访问

## 解决方法

在nginx.conf的server的某个location中增加


```nginx
root /home/ryan/projects/test/protected;
auth_basic            "Restricted";
auth_basic_user_file  authes/test;
```

对于密码文件authes/test，用户可以随意指定，不必是系统中存在的用户


```bash
yum install httpd-tools
touch authes/test
htpasswd -bc authes/test ryan changeme
htpasswd -b authes/test tony changeme
```