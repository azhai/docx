layout:     post
date:       2013-05-21
title:      在CentOS上部署lamp环境
slug:       centos-install-lamp
author:     Ryan Liu
tags:       linux, mysql, php, nginx
comments:   true

描述
-----

这里的lamp中web server用的是nginx而不是apache，但还是用传统的叫法lamp。
环境 CentOS 6.4，我的是64位，但在32位上依然可用。

从[StackOverflow](http://stackoverflow.com/questions/64786/error-handling-in-bash)下载一个调试bash的脚本lib.trap.sh，
我把它放在本文最后。

使用的版本如下：

*  mysql 5.6.11
*  php 5.4.14
*  nginx 1.4.1

脚本deploy_lamp.sh
-------------------
```bash
source "lib.trap.sh"

username="ryan"
if [[ -z "$username" ]]; then
    username=`whoami` #当前用户名
fi
cpu_cores=`cat /proc/cpuinfo | gawk '/cpu cores/{n+=1} END{print n}'` #CPU内核数
if [[ `uname -m` =~ "x86_64" ]]; then
    sys64bit="True" #64位系统
else
    sys64bit=""
fi
if [[ `id -un mysql` != "mysql" ]]; then
    groupadd -f mysql
    useradd -r -g mysql mysql
fi

yum install -y gcc gcc-c++ make cmake automake autoconf binutils
yum install -y gawk sed vim wget
yum install -y git httpd-tools
yum install -y zlib-devel ncurses-devel readline-devel
yum install -y bzip2-devel gmp-devel openssl-devel curl-devel freetype-devel
yum install -y libxml2-devel libpng-devel libjpeg-devel libicu-devel libc-client-devel
yum install -y libtool libtool-ltdl-devel


function download()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    if [ ! -d "ngx_http_lower_upper_case" ]; then
        git clone https://github.com/replay/ngx_http_lower_upper_case.git
    fi
    if [ ! -f "pcre-8.32.tar.gz" ]; then
        wget http://sourceforge.net/projects/pcre/files/pcre/8.32/pcre-8.32.tar.gz
    fi
    if [ ! -f "nginx-1.4.1.tar.gz" ]; then
        wget http://nginx.org/download/nginx-1.4.1.tar.gz
    fi
    if [ ! -f "freetds-stable.tgz" ]; then
        wget ftp://ftp.astron.com/pub/freetds/stable/freetds-stable.tgz
    fi
    if [ ! -f "libmcrypt-2.5.8.tar.gz" ]; then
        wget -O libmcrypt-2.5.8.tar.gz http://downloads.sourceforge.net/project/mcrypt/Libmcrypt/2.5.8/libmcrypt-2.5.8.tar.gz?use_mirror=nchc
    fi
    if [ ! -f "php-5.4.14.tar.gz" ]; then
        wget -O php-5.4.14.tar.gz http://cn2.php.net/get/php-5.4.14.tar.gz/from/this/mirror
    fi
    if [ ! -f "mysql-5.6.11.tar.gz" ]; then
        wget http://cdn.mysql.com/Downloads/MySQL-5.6/mysql-5.6.11.tar.gz
    fi
}


function ins_mysql()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    rm -rf mysql-5.6.11
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
}

function ini_mysql()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    #必要的软连接和目录
    ln -sf /opt/mysql-5.6.11/bin/mysqld_safe /usr/bin/mysqld_safe
    ln -sf /opt/mysql-5.6.11/bin/mysql /usr/bin/mysql
    ln -sf /opt/mysql-5.6.11/bin/mysqldump /usr/bin/mysqldump
    ln -sf /opt/mysql-5.6.11/bin/mysqlimport /usr/bin/mysqlimport
    ln -sf /opt/mysql-5.6.11/bin/mysql_config /usr/bin/mysql_config
    mkdir -p /var/run/mysqld
    chmod -R 777 /var/run/mysqld
    chown -R mysql:mysql /opt/mysql-5.6.11/data/

    #初始化MySQL
    /opt/mysql-5.6.11/scripts/mysql_install_db --user=mysql --basedir=/opt/mysql-5.6.11 --datadir=/opt/mysql-5.6.11/data
    #64位Linux
    if [ -n "$sys64bit" ]; then
      ln -sf /opt/mysql-5.6.11/lib  /opt/mysql-5.6.11/lib64
    fi
    #设置配置文件
    if [ ! -f /opt/mysql-5.6.11/my.cnf ]; then
        cp /opt/mysql-5.6.11/support-files/my-default.cnf /opt/mysql-5.6.11/my.cnf
    fi
    rm -f /etc/my.cnf
    sed -i '/# basedir =/cbasedir = \/opt\/mysql-5.6.11' /opt/mysql-5.6.11/my.cnf
    sed -i '/# datadir =/cdatadir = \/opt\/mysql-5.6.11\/data' /opt/mysql-5.6.11/my.cnf
    sed -i '/# port =/cport = 3306' /opt/mysql-5.6.11/my.cnf
    #sed -i '/# innodb_buffer_pool_size = 128M/cinnodb_buffer_pool_size = 256M' /opt/mysql-5.6.11/my.cnf
    sed -i '$alog-error=/var/log/mysqld.log' /opt/mysql-5.6.11/my.cnf
    sed -i '$apid-file=/var/run/mysqld/mysqld.pid' /opt/mysql-5.6.11/my.cnf
    sed -i '$G' /opt/mysql-5.6.11/my.cnf
    sed -i '$a[mysqld_safe]' /opt/mysql-5.6.11/my.cnf
    sed -i '$alog-error=/var/log/mysqld.log' /opt/mysql-5.6.11/my.cnf
    sed -i '$apid-file=/var/run/mysqld/mysqld.pid' /opt/mysql-5.6.11/my.cnf
    ln -sf /opt/mysql-5.6.11/my.cnf /etc/my.cnf

    #设置系统服务
    rm -f /etc/init.d/mysql
    cp /opt/mysql-5.6.11/support-files/mysql.server /etc/init.d/mysql
    sed -i '/^basedir=/cbasedir=\/opt\/mysql-5.6.11' /etc/init.d/mysql
    sed -i '/^datadir=/cdatadir=\/opt\/mysql-5.6.11\/data' /etc/init.d/mysql
    #重启mysql服务
    /etc/init.d/mysql start
}


function ini_mysql_user()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"

    #或者使用/opt/mysql-5.6.11/bin/mysql_secure_installation
    local rootpass=toor
    local dbapass=changeme
    mysql -u root --password="" mysql << EOD
    DELETE FROM \`user\` WHERE User='';
    DELETE FROM \`db\` WHERE User='';
    SET PASSWORD FOR 'root'@'localhost'=PASSWORD('$rootpass');
    SET PASSWORD FOR 'root'@'127.0.0.1'=PASSWORD('$rootpass');
    SET PASSWORD FOR 'root'@'::1'=PASSWORD('$rootpass');
    SET PASSWORD FOR 'root'@'`hostname`'=PASSWORD('$rootpass');
    GRANT ALL PRIVILEGES ON \`db\\_%\`.* TO 'dba'@'localhost' IDENTIFIED BY '$dbapass' WITH GRANT OPTION;
    GRANT SELECT,INSERT,UPDATE,DELETE,CREATE,DROP,ALTER,INDEX ON \`db\\_%\`.* TO 'dba'@'192.168.0.%' IDENTIFIED BY '$dbapass' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
    UPDATE user SET File_priv='Y' WHERE User='dba';
    FLUSH PRIVILEGES;
    GRANT FILE ON *.* TO 'dba'@'localhost';
    GRANT FILE ON *.* TO 'dba'@'192.168.0.%';
    FLUSH PRIVILEGES;
EOD

    #手工停止
    /opt/mysql-5.6.11/bin/mysqladmin --password="$rootpass" shutdown
    /etc/init.d/mysql start
    #更新
    /opt/mysql-5.6.11/bin/mysql_upgrade --password="$rootpass"
    #手工启动
    #mysqld_safe --user=mysql &
    /etc/init.d/mysql restart
}


function ins_php_pre()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    rm -rf freetds-0.91
    rm -rf libmcrypt-2.5.8

    if [ ! -d "/opt/freetds-0.91" ]; then
        tar xzf freetds-stable.tgz
        cd freetds-0.91
        ./configure --prefix=/opt/freetds-0.91 --with-tdsver=8.0 --enable-msdblib
        make && make install
        cd ..
        #64位系统下（CentOS 6.3有这个问题，其他发行版不知道是不是也这样），还需要
        if [ -n "$sys64bit" ]; then
            ln -sf /opt/freetds-0.91/lib /opt/freetds-0.91/lib64
        fi
    fi

    if [ ! -e "/usr/local/lib/libmcrypt.so.4" ]; then
        tar xzf libmcrypt-2.5.8.tar.gz
        cd libmcrypt-2.5.8
        ./configure
        make && make install
        cd ..
        if [ -n "$sys64bit" ]; then
            ln -sf /usr/local/lib/libmcrypt.so.4.4.8 /usr/lib64/libmcrypt.so.4
        else
            ln -sf /usr/local/lib/libmcrypt.so.4.4.8 /usr/lib/libmcrypt.so.4
        fi
    fi
}


function ins_php()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    rm -rf php-5.4.14

    #如果configure报错Note that the MySQL client library is not bundled anymore.
    #请将/opt/mysql-5.6.11/lib 建立软连接 /opt/mysql-5.6.11/lib64
    #64位Linux
    if [ -n "$sys64bit" ]; then
        ln -sf /opt/mysql-5.6.11/lib /opt/mysql-5.6.11/lib64
        gnu_x86_64="--build=x86_64-redhat-linux-gnu --host=x86_64-redhat-linux-gnu --with-libdir=lib64"
    else
        gnu_x86_64=""
    fi

    tar xzf php-5.4.14.tar.gz
    cd php-5.4.14
    #注意：$gnu_x86_64两边不要加引号或双引号
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
    --without-unixODBC --enable-mbregex --enable-embed \
    --enable-tokenizer --disable-phar --with-sqlite3 \
    --enable-fpm --with-fpm-user="$username" --with-fpm-group="$username" \
    --with-layout=GNU --with-mssql=/opt/freetds-0.91 $gnu_x86_64
    make && make install
    cd ..
}


function ini_php()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
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
    mkdir -p /var/run/php-fpm/
    cp php-5.4.14/php.ini-production /opt/php-5.4.14/etc/php.ini
    cp /opt/php-5.4.14/etc/php-fpm.conf.default /opt/php-5.4.14/etc/php-fpm.conf

    #修改配置
    sed -i '/^max_execution_time/cmax_execution_time = 600' /opt/php-5.4.14/etc/php.ini
    sed -i '/^error_reporting/cerror_reporting = E_ALL & ~E_DEPRECATED & ~E_NOTICE' /opt/php-5.4.14/etc/php.ini
    sed -i '/^display_errors/cdisplay_errors = On' /opt/php-5.4.14/etc/php.ini
    sed -i '/^display_startup_errors/cdisplay_startup_errors = On' /opt/php-5.4.14/etc/php.ini
    sed -i '/^track_errors/ctrack_errors = On' /opt/php-5.4.14/etc/php.ini
    sed -i '/^upload_max_filesize/cupload_max_filesize = 20M' /opt/php-5.4.14/etc/php.ini
    sed -i '/^;date.timezone/cdate.timezone = Asia/Shanghai' /opt/php-5.4.14/etc/php.ini

    #修改php-fpm的进程数
    sed -i "/^;daemonize/cdaemonize = yes" /opt/php-5.4.14/etc/php-fpm.conf
    sed -i "/^listen = /clisten = /var/run/php-fpm/php-fpm.sock" /opt/php-5.4.14/etc/php-fpm.conf
    sed -i '/^pm.max_children/cpm.max_children = 30' /opt/php-5.4.14/etc/php-fpm.conf
    sed -i '/^pm.start_servers/cpm.start_servers = 2' /opt/php-5.4.14/etc/php-fpm.conf
    sed -i '/^pm.min_spare_servers/cpm.min_spare_servers = 2' /opt/php-5.4.14/etc/php-fpm.conf
    sed -i '/^pm.max_spare_servers/cpm.max_spare_servers = 30' /opt/php-5.4.14/etc/php-fpm.conf

    mkdir -p /etc/php5/fpm/
    ln -s /opt/php-5.4.14/etc/php.ini /etc/php5/fpm/php.ini
    ln -s /opt/php-5.4.14/etc/php-fpm.conf /etc/php5/fpm/php-fpm.conf
}


function ins_php_ext()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    #添加常用扩展
    if [ ! -f "/opt/php-5.4.14/lib/php/20100525/apc.so" ]; then
        printf "\n" | pecl install apc
        sed -i '1aextension=apc.so' /opt/php-5.4.14/etc/php.ini
    fi
    if [ ! -f "/opt/php-5.4.14/lib/php/20100525/xdebug.so" ]; then
        pecl install xdebug
        #ThreadSafe版本，带有_ts
        sed -i '1azend_extension_ts=xdebug.so' /opt/php-5.4.14/etc/php.ini
    fi
    if [ ! -d "/opt/php-5.4.14/share/pear/PHPUnit" ]; then
        pear upgrade pear && pear install phpunit
    fi
    if [ ! -f "/opt/php-5.4.14/bin/phing" ]; then
        pear channel-discover pear.phing.info
        pear install phing/phing
        ln -sf /opt/php-5.4.14/bin/phing /usr/bin/phing
    fi
}


function ins_nginx()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    rm -rf pcre-8.32
    rm -rf nginx-1.4.1
    tar xzf pcre-8.32.tar.gz
    tar xzf nginx-1.4.1.tar.gz
    cd nginx-1.4.1
    ./configure --prefix=/opt/nginx-1.4.1 --with-pcre=../pcre-8.32 --add-module=../ngx_http_lower_upper_case
    make && make install
    cd ..
}


function ini_nginx()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    cd /opt/nginx-1.4.1/conf/
    #替换主配置
    sed -i "/^#user/cuser  $username;" nginx.conf
    sed -i "/^worker_processes/cworker_processes  $cpu_cores;" nginx.conf
    sed -i "s/^#pid/pid/" nginx.conf
    sed -i "/^[[:space:]]*#log_format/{N;N;s/\([[:space:]]*\)#/\1/g;h;s/main/sess/;s/'\;$/ \"\$var_sessid\"'\;/;G}" nginx.conf
    sed -i '/^[[:space:]]*#gzip/c\    gzip  on;\n    gzip_min_length  1k;' nginx.conf
    sed -i '/^[[:space:]]* server {/,$d' nginx.conf
    sed -i '$a\    include sites/*.conf;\n}' nginx.conf
    #增加一个PHP配置
    mkdir sites authes
    touch sites/test.conf
    #将最后面的site配置文件写入上面的文件
    chmod -R 777 sites
    cd -

    ln -sf /opt/nginx-1.4.1/sbin/nginx /usr/sbin/nginx
}


function ini_nginx_site()
{
    echo ""
    echo ""
    echo "$FUNCNAME start ......"
    cat > /opt/nginx-1.4.1/conf/sites/template.conf.bak << EOD
server {
    listen           80;
    server_name      test.example.com;
    charset          utf-8;
    root             /home/ryan/projects/test;

    # case insensitive
    #lower \$lower_uri "\$uri";
    #try_files \$uri \$lower_uri;

    set \$var_sessid "-";
    if ( \$http_cookie ~* "PHPSESSID=(\S+)(;.*|\$)")
    {
        set \$var_sessid \$1;
    }
    access_log  logs/test.access.log  main;

    #error_page  404              /404.html;

    # redirect server error pages to the static page /50x.html
    #
    #error_page   500 502 503 504  /50x.html;
    #location = /50x.html {
    #    root   html;
    #}

    if (\$request_uri ~* ^.*\\.(svn|git|hg|bzr|cvs).*\$) {
        return 404;
    }

    location ~* ^.+\\.(css|js|jpg|png|gif|ico|swf|pdf|txt|xlsx)\$ {
        access_log off;
        expires    30d;
    }

    # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9000
    #
    location / {
        #auth_basic            "Restricted";
        #auth_basic_user_file  authes/test;
        try_files \$uri  \$uri/  /index.php?\$query_string;
        #fastcgi_pass   127.0.0.1:9000;
        fastcgi_pass    unix:/var/run/php-fpm/php-fpm.sock;
        fastcgi_index   index.php;
        include         fastcgi_params;
        fastcgi_param   SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
    }

    #location ~* /\$ {
    #    index    index.html;
    #}
}
EOD
    chkconfig mysql on
}


softdir="/home/$username/soft/"
mkdir -p $softdir
cd $softdir
download
[ -d "/opt/mysql-5.6.11" ] || { ins_mysql; ini_mysql; ini_mysql_user; }
[ -d "/opt/php-5.4.14" ] || { ins_php_pre; ins_php; ini_php; ins_php_ext; }
[ -d "/opt/nginx-1.4.1" ] || { ins_nginx; ini_nginx; ini_nginx_site; }

exit 0
```

脚本lib.trap.sh
---------------
```bash
lib_name='trap'
lib_version=20121026

stderr_log="/dev/shm/stderr.log"

#
# TO BE SOURCED ONLY ONCE:
#
###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##

if test "${g_libs[$lib_name]+_}"; then
    return 0
else
    if test ${#g_libs[@]} == 0; then
        declare -A g_libs
    fi
    g_libs[$lib_name]=$lib_version
fi


#
# MAIN CODE:
#
###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##

set -o pipefail  # trace ERR through pipes
set -o errtrace  # trace ERR through 'time command' and other functions
set -o nounset   ## set -u : exit the script if you try to use an uninitialised variable
set -o errexit   ## set -e : exit the script if any statement returns a non-true return value

exec 2>"$stderr_log"


###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##
#
# FUNCTION: EXIT_HANDLER
#
###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##

function exit_handler ()
{
    local error_code="$?"

    test $error_code == 0 && return;

    #
    # LOCAL VARIABLES:
    # ------------------------------------------------------------------
    #
    local i=0
    local regex=''
    local mem=''

    local error_file=''
    local error_lineno=''
    local error_message='unknown'

    local lineno=''


    #
    # PRINT THE HEADER:
    # ------------------------------------------------------------------
    #
    # Color the output if it's an interactive terminal
    test -t 1 && tput bold; tput setf 4                                 ## red bold
    echo -e "\n(!) EXIT HANDLER:\n"


    #
    # GETTING LAST ERROR OCCURRED:
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #

    #
    # Read last file from the error log
    # ------------------------------------------------------------------
    #
    if test -f "$stderr_log"
        then
            stderr=$( tail -n 1 "$stderr_log" )
            rm "$stderr_log"
    fi

    #
    # Managing the line to extract information:
    # ------------------------------------------------------------------
    #

    if test -n "$stderr"
        then
            # Exploding stderr on :
            mem="$IFS"
            local shrunk_stderr=$( echo "$stderr" | sed 's/\: /\:/g' )
            IFS=':'
            local stderr_parts=( $shrunk_stderr )
            IFS="$mem"

            # Storing information on the error
            error_file="${stderr_parts[0]}"
            error_lineno="${stderr_parts[1]}"
            error_message=""

            for (( i = 3; i <= ${#stderr_parts[@]}; i++ ))
                do
                    error_message="$error_message "${stderr_parts[$i-1]}": "
            done

            # Removing last ':' (colon character)
            error_message="${error_message%:*}"

            # Trim
            error_message="$( echo "$error_message" | sed -e 's/^[ \t]*//' | sed -e 's/[ \t]*$//' )"
    fi

    #
    # GETTING BACKTRACE:
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #
    _backtrace=$( backtrace 2 )


    #
    # MANAGING THE OUTPUT:
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #

    local lineno=""
    regex='^([a-z]{1,}) ([0-9]{1,})$'

    if [[ $error_lineno =~ $regex ]]

        # The error line was found on the log
        # (e.g. type 'ff' without quotes wherever)
        # --------------------------------------------------------------
        then
            local row="${BASH_REMATCH[1]}"
            lineno="${BASH_REMATCH[2]}"

            echo -e "FILE:\t\t${error_file}"
            echo -e "${row^^}:\t\t${lineno}\n"

            echo -e "ERROR CODE:\t${error_code}"
            test -t 1 && tput setf 6                                    ## white yellow
            echo -e "ERROR MESSAGE:\n$error_message"


        else
            regex="^${error_file}\$|^${error_file}\s+|\s+${error_file}\s+|\s+${error_file}\$"
            if [[ "$_backtrace" =~ $regex ]]

                # The file was found on the log but not the error line
                # (could not reproduce this case so far)
                # ------------------------------------------------------
                then
                    echo -e "FILE:\t\t$error_file"
                    echo -e "ROW:\t\tunknown\n"

                    echo -e "ERROR CODE:\t${error_code}"
                    test -t 1 && tput setf 6                            ## white yellow
                    echo -e "ERROR MESSAGE:\n${stderr}"

                # Neither the error line nor the error file was found on the log
                # (e.g. type 'cp ffd fdf' without quotes wherever)
                # ------------------------------------------------------
                else
                    #
                    # The error file is the first on backtrace list:

                    # Exploding backtrace on newlines
                    mem=$IFS
                    IFS='
                    '
                    #
                    # Substring: I keep only the carriage return
                    # (others needed only for tabbing purpose)
                    IFS=${IFS:0:1}
                    local lines=( $_backtrace )

                    IFS=$mem

                    error_file=""

                    if test -n "${lines[1]}"
                        then
                            array=( ${lines[1]} )

                            for (( i=2; i<${#array[@]}; i++ ))
                                do
                                    error_file="$error_file ${array[$i]}"
                            done

                            # Trim
                            error_file="$( echo "$error_file" | sed -e 's/^[ \t]*//' | sed -e 's/[ \t]*$//' )"
                    fi

                    echo -e "FILE:\t\t$error_file"
                    echo -e "ROW:\t\tunknown\n"

                    echo -e "ERROR CODE:\t${error_code}"
                    test -t 1 && tput setf 6                            ## white yellow
                    if test -n "${stderr}"
                        then
                            echo -e "ERROR MESSAGE:\n${stderr}"
                        else
                            echo -e "ERROR MESSAGE:\n${error_message}"
                    fi
            fi
    fi

    #
    # PRINTING THE BACKTRACE:
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #

    test -t 1 && tput setf 7                                            ## white bold
    echo -e "\n$_backtrace\n"

    #
    # EXITING:
    # ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ #

    test -t 1 && tput setf 4                                            ## red bold
    echo "Exiting!"

    test -t 1 && tput sgr0 # Reset terminal

    exit "$error_code"
}
trap exit_handler EXIT                                                  # ! ! ! TRAP EXIT ! ! !
trap exit ERR                                                           # ! ! ! TRAP ERR ! ! !


###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##
#
# FUNCTION: BACKTRACE
#
###~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~##

function backtrace
{
    local _start_from_=0

    local params=( "$@" )
    if (( "${#params[@]}" >= "1" ))
        then
            _start_from_="$1"
    fi

    local i=0
    local first=false
    while caller $i > /dev/null
    do
        if test -n "$_start_from_" && (( "$i" + 1   >= "$_start_from_" ))
            then
                if test "$first" == false
                    then
                        echo "BACKTRACE IS:"
                        first=true
                fi
                caller $i
        fi
        let "i=i+1"
    done
}
return 0
```
