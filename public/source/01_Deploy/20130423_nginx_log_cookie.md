layout:     post
date:       2013-04-23
title:      Nginx记录访问cookie到日志
slug:       nginx-log-cookie
author:     Ryan Liu
tags:       nginx, log, cookie, php
comments:   true

## 问题描述

在某个PHP网站的日志中记录下session id，比使用ip地址能更好地跟踪用户访问

## 解决方法

在nginx.conf的http中增加一种日志格式



    log_format  sess  &#39;$remote_addr - $remote_user [$time_local] &#34;$request&#34; &#39;
                      &#39;$status $body_bytes_sent &#34;$http_referer&#34; &#39;
                      &#39;&#34;$http_user_agent&#34; &#34;$http_x_forwarded_for&#34; &#34;$phpsessid&#34;&#39;

在对应网站的server中添加



    set $phpsessid &#34;-&#34;;
    if ( $http_cookie ~* &#34;PHPSESSID=(\S+)(;.*|$)&#34;)
    {
        set $phpsessid $1;
    }
    
    access_log  logs/test.access.log  sess;

重启nginx服务后，此网站日志文件每行后面多出一列PHP中的session_id()的值