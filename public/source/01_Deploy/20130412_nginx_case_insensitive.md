layout:     post
date:       2013-04-12
title:      让Nginx对URL不区分大小写
slug:       nginx-case-insensitive
author:     Ryan Liu
tags:       nginx, rewrite
comments:   true

问题描述
----------
将Windows上的静态文件移动Linux服务器上，由于Linux文件系统区分大小写，于是/A.jpg将访问不了a.jpg文件

解决方法
----------
将静态文件目录和文件名全部改为小写，然后在Web Server上将URL改为小写

递归地将目录下的文件和目录改为小写

```bash
find -exec sh -c 'rm -f "$0" `echo "$0" | tr "[A-Z]" "[a-z]"` > /dev/null 2>&1' {} \;
```

如果是Apache作为Web Server，它支持RewriteCond中将URL改为小写

如果是Nginx作为Web Server，需要重新编译nginx，加入第三方Module

一种是perl-module的perl_set方法，网上可以搜索到解决方法。

另一种使用 [lower_upper_case](https://github.com/replay/ngx_http_lower_upper_case)

```bash
git clone https://github.com/replay/ngx_http_lower_upper_case.git
wget http://sourceforge.net/projects/pcre/files/pcre/8.32/pcre-8.32.tar.gz
wget http://nginx.org/download/nginx-1.4.0.tar.gz

tar xzf pcre-8.32.tar.gz
tar xzf nginx-1.4.0.tar.gz
cd nginx-1.4.0
./configure --prefix=/opt/nginx-1.4.0 --with-pcre=../pcre-8.32 --add-module=../ngx_http_lower_upper_case
make && make install
cd ..
```

在配置文件server中加入

```nginx
    lower $lower_uri "$uri";
    try_files $uri $lower_uri;

```
nginx中的网站配置改为

```nginx
server {
    listen           80;
    server_name      static.example.com;
    charset          utf-8;
    root             /home/ryan/projects/static;

    # case insensitive
    lower $lower_uri "$uri";
    try_files $uri $lower_uri;

    access_log  off;
    expires     30d;
}
```
