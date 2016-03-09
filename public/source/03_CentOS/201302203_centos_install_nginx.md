layout:     post
date:       2013-02-20
title:      安装Linux开发环境之三 —— Nginx
slug:       centos-install-nginx
author:     Ryan Liu
tags:       linux, nginx
comments:   true

> 2013-4-15更新，使用v1.4.0，增加模块ngx_http_lower_upper_case

安装Nginx
----------
下载  并解压

下载 [nginx-1.4.0](http://nginx.org/download/nginx-1.4.0.tar.gz) 解压并进入目录

```bash
git clone https://github.com/replay/ngx_http_lower_upper_case.git \
    && wget http://sourceforge.net/projects/pcre/files/pcre/8.32/pcre-8.32.tar.gz \
    && wget http://nginx.org/download/nginx-1.4.0.tar.gz \
    && tar xzf pcre-8.32.tar.gz \
    && tar xzf nginx-1.4.0.tar.gz \
    && cd nginx-1.4.0 \
    && ./configure --prefix=/opt/nginx-1.4.0 --with-pcre=../pcre-8.32 --add-module=../ngx_http_lower_upper_case \
    && make && make install \
    && cd ..
```

配置Nginx
----------
```bash
cd /opt/nginx-1.4.0/conf/
#替换主配置
gawk 'BEGIN{a=""} /    server \{/{a=a NR ","} END{a=a NR; system("sed -i \"" a "d\" nginx.conf")}' nginx.conf
sed -i '$a\    include sites/*.conf;\n}' nginx.conf
sed -i '/^[[:blank:]]*#gzip/c\    gzip  on;' /opt/nginx-1.4.0/conf/nginx.conf
sed -i '/^[[:blank:]]*gzip/a\    gzip_min_length  1k;' /opt/nginx-1.4.0/conf/nginx.conf
#修改用户和进程数
username=`whoami` #当前用户名
sed -i "1auser  $username;" nginx.conf
cpu_cores=`cat /proc/cpuinfo | gawk '/cpu cores/{n+=1} END{print n}'` #CPU内核数
sed -i "/^worker_processes/cworker_processes  $cpu_cores;" nginx.conf
#增加一个PHP配置
mkdir sites authes
touch sites/test.conf
#将最后面的site配置文件写入上面的文件
chmod -R 777 sites
cd -

rm -f /usr/sbin/nginx
ln -s /opt/nginx-1.4.0/sbin/nginx /usr/sbin/nginx
#启动
nginx
#重启
nginx -s reload
```

Nginx的site配置文件
-------------------
```nginx
server {
    listen           80;
    server_name      test.example.com;
    charset          utf-8;
    root             /home/ryan/projects/test;

    access_log  logs/test.access.log  main;

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    #error_page   500 502 503 504  /50x.html;
    #location = /50x.html {
    #    root   html;
    #}

    if ($request_uri ~* ^.*\.(svn|git|hg|bzr|cvs).*$) {
        return 404;
    }

    location ~* ^.+\.(css|js|jpg|png|gif|ico|swf|pdf|txt|xlsx)$ {
        access_log off;
        expires    30d;
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    location / {
        #auth_basic            "Restricted";
        #auth_basic_user_file  authes/test;
        try_files $uri  $uri/  /index.php;
        #fastcgi_pass   127.0.0.1:9000;
        fastcgi_pass    unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_index   index.php;
        include         fastcgi_params;
        fastcgi_param   SCRIPT_FILENAME  $document_root$fastcgi_script_name;
    }
}
```


