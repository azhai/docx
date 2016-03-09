layout:     post
date:       2013-04-16
title:      安装Postfix邮件服务器
slug:       install-postfix
author:     Ryan Liu
tags:       postfix, mail, server
comments:   true

## 安装Postfix

下载 [postfix-2.10.0](ftp://ftp.cuhk.edu.hk/pub/packages/mail-server/postfix/official/postfix-2.10.0.tar.gz) 

 [cyrus-sasl-2.1.26](ftp://ftp.cyrusimap.org/cyrus-sasl/cyrus-sasl-2.1.26.tar.gz) 

 [cyrus-imapd-2.4.17](ftp://ftp.cyrusimap.org/cyrus-imapd/cyrus-imapd-2.4.17.tar.gz) 



    yum install openssl db4-devel libsnmp-devel
    yum -y install perl-CPAN
    wget ftp://ftp.cuhk.edu.hk/pub/packages/mail-server/postfix/official/postfix-2.10.0.tar.gz
    wget ftp://ftp.cyrusimap.org/cyrus-sasl/cyrus-sasl-2.1.26.tar.gz
    wget ftp://ftp.cyrusimap.org/cyrus-imapd/cyrus-imapd-2.4.17.tar.gz
    
    #创建用户
    useradd -M -s /bin/false -p* postfix
    groupadd postdrop
    #安装cyrus-sasl
    tar xzf cyrus-sasl-2.1.26.tar.gz
    cd cyrus-sasl-2.1.26
    ./configure --prefix=/opt/cyrus/cyrus-sasl-2.1.26 \
            --enable-login --enable-ntlm
    make && make install
    cd ..
    #安装cyrus-imapd
    tar xzf cyrus-imapd-2.4.17.tar.gz
    cd cyrus-imapd-2.4.17
    ./configure --prefix=/opt/cyrus/cyrus-imapd-2.4.17 --with-lock=fcntl \
            --with-sasl=/opt/cyrus/cyrus-sasl-2.1.26 \
            --with-openssl
    make && make install
    cd ..
    
    #安装postfix
    tar xzf postfix-2.10.0.tar.gz
    cd postfix-2.10.0
    if [ `uname -m` =~ &#39;x86_64&#39; ]; then
        ln -s /opt/cyrus/cyrus-sasl-2.1.26/lib/libsasl2.so.3.0.0 /usr/lib64/libsasl2.so.3
    else
        ln -s /opt/cyrus/cyrus-sasl-2.1.26/lib/libsasl2.so.3.0.0 /usr/lib/libsasl2.so.3
    fi
    make makefiles --always-make CCARGS=&#39;-I/opt/cyrus/cyrus-sasl-2.1.26/include/sasl \
            -DDEF_CONFIG_DIR=\&#34;/etc/postfix\&#34; -DFD_SETSIZE=2048 -DUSE_CYRUS_SASL -DUSE_TLS&#39; \
            AUXLIBS=&#39;-L/opt/cyrus/cyrus-sasl-2.1.26/lib/sasl2 -lssl -lcrypto&#39;
    make
    make install
    
    #postfix2.7的问题
    #安装过程中需要回答问题，下面三个问题，请使用右边的回答，其他直接按回车用默认值
    #  [/usr/bin/mailq]                     /usr/bin/mailq.postfix
    #  [/usr/bin/newaliases]                /usr/bin/newaliases.postfix
    #  [/usr/sbin/sendmail]                 /usr/sbin/sendmail.postfix
    
    #postfix-2.10的问题，一路回车使用默认值
    #install_root: [/]
    #tempdir: [/home/xxx/yyy/postfix-2.10.0]
    #config_directory: []
    #command_directory: [/usr/sbin]
    #daemon_directory: [/usr/libexec/postfix]
    #data_directory: [/var/lib/postfix]
    #html_directory: [no]
    #mail_owner: [postfix]
    #mailq_path: [/usr/bin/mailq]
    #manpage_directory: [/usr/local/man]
    #newaliases_path: [/usr/bin/newaliases]
    #queue_directory: [/var/spool/postfix]
    #readme_directory: [no]
    #sendmail_path: [/usr/sbin/sendmail]
    #setgid_group: [postdrop]
    
    cd ..

## 配置postfix



    # 添加以下内容到main.cf echo [BELOW TEXT] >> /etc/postfix/main.cf
    local_recipient_maps =
    #请将所有的mail.example.com和example.com替换为您的MX域名
    ###############postfix#################################
    myhostname = mail.example.com
    myorigin = example.com
    mydomain = example.com
    append_dot_mydomain = no
    mydestination = $myhostname, localhost.$mydomain, localhost, $mydomain
    #mynetworks = 192.168.1.0/24, 10.0.0.0/24, 127.0.0.0/8
    mynetworks = 127.0.0.1, 192.168.0.0/24  #只接收本机和局域网的邮件
    #body_checks = regexp:/etc/postfix/body_checks  #用于从邮件中提取信息记录到postfix日志
    
    ############################CYRUS-SASL############################
    broken_sasl_auth_clients = yes
    smtpd_recipient_restrictions=permit_mynetworks,permit_sasl_authenticated,reject_invalid_hostname,reject_non_fqdn_hostname,reject_unknown_sender_domain,reject_non_fqdn_sender,reject_non_fqdn_recipient,reject_unknown_recipient_domain,reject_unauth_pipelining,reject_unauth_destination
    smtpd_sasl_auth_enable = yes
    smtpd_sasl_local_domain = $myhostname
    smtpd_sasl_security_options = noanonymous
    #postfix v2.9以上请注释下面这行，否则启动postfix时弹出警告信息
    #smtpd_sasl_application_name = smtpd
    smtpd_banner = Welcome to our $myhostname ESMTP,Warning: Version not Available!
    
    # echo [BELOW TEXT] > /etc/postfix/smtpd.conf
    echo &#34;pwcheck_method: saslauthd&#34; >> /etc/postfix/smtpd.conf
    echo &#34;mech_list: PLAIN LOGIN&#34; >> /etc/postfix/smtpd.conf
    
    # echo [BELOW TEXT] >> /etc/postfix/body_checks
    /\/account\/([a-z_]+)\/veri/  WARN &#34;$1&#34;
    /verify=3D([0-9a-f]+)/  WARN &#34;$1&#34;
    
    # 设置
    rm -f /usr/sbin/sendmail
    ln -s /usr/sbin/sendmail.postfix /usr/sbin/sendmail
    rm -f /etc/alternatives/mta
    ln -s /usr/sbin/sendmail.postfix /etc/alternatives/mta
    
    rm -f /usr/sbin/saslauthd
    ln -s /opt/cyrus/cyrus-sasl-2.1.26/sbin/saslauthd /usr/sbin/saslauthd
    rm -rf /etc/postfix
    ln -s /etc/postfix /etc/postfix

## 启动、测试、过滤日志



    # 启动
    #如果没有chkconfig，请用sysv-rc-conf代替
    #apt-get install sysv-rc-conf
    /etc/rc.d/init.d/sendmail stop
    chkconfig sendmail off
    chkconfig --list sendmail
    chkconfig saslauthd on
    chkconfig --list saslauthd
    /etc/rc.d/init.d/saslauthd start
    postfix start
    
    # 测试，收件人为who@where.com
    telnet localhost 25
    EHLO mail.example.com
    MAIL FROM:admin@example.com
    RCPT TO:who@where.com NOTIFY=success,failure
    DATA
    subject:Mail test!
    This is just a mail test!!!
    .
    #上面单独一行的点表示消息结束
    
    #从日志中统计退回、拒收、超时失败的邮件
    #grep &#34;status=bounced&#34; /var/log/maillog | gawk &#39;match($0,/to=/){print substr($0,RSTART+4,RLENGTH-5)}&#39;
    #grep &#34;status=deferred&#34; /var/log/maillog | gawk &#39;match($0,/to=/){print substr($0,RSTART+4,RLENGTH-5)}&#39;
    #grep &#34;status=expired&#34; /var/log/maillog | gawk &#39;match($0,/to=/){print substr($0,RSTART+4,RLENGTH-5)}&#39;