layout:     post
date:       2014-04-10
title:      启动PHPUnit测试的简便方式
slug:       phpunit-boot
author:     Ryan Liu
tags:       phpunit, console
comments:   true

问题描述
------------
在命令行下，不管当前目录在哪儿，都可以简单地启动一个项目的PHPUnit测试。

具体一点，我们的项目名为proj，其下有个测试目录tests，测试Namespace为ProjTest。

解决方案
------------
通常我们要进入proj/tests/目录，然后运行类似下面的命令

```bash
cd /path/to/proj/tests/
phpunit --bootstrap bootstrap.php ProjTest
```

如果能做到象下面这样，不是更傻瓜化吗？

```bash
chmod +x /path/to/proj/tests/bootstrap.php #第一次运行时
/path/to/proj/tests/bootstrap.php
```

第一步，很容易想到，用xml配置bootstrap和Namespace，下面proj/tests/config.xml是个简化版本

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit stopOnFailure="false" bootstrap="bootstrap.php">
    <testsuites>
        <testsuite name="ProjTest">
            <directory>ProjTest</directory>
        </testsuite>
    </testsuites>
    <logging>
        <log type="json" target="./runtime/logs.json"/>
    </logging>
    <php>
        <var name="DB_DSN" value="mysql:host=127.0.0.1;charset=utf8" />
        <var name="DB_USER" value="dba" />
        <var name="DB_PASSWD" value="password" />
        <var name="DB_DBNAME" value="db_test" />
        <var name="DB_TBLPRE" value="t_" />
    </php>
</phpunit>
```

第二步，改造bootstrap.php，这个需要打开phpunit文件，才知道要使用类PHPUnit_TextUI_Command

```php
#!/usr/bin/env phpunit
<?php

//--------------------------
//这里是bootstrap代码
//--------------------------

if ($_SERVER['argc'] <= 2) { #/usr/local/bin/phpunit ./bootstrap.php
    //运行测试，相当于命令行下 phpunit -c config.xml
    $phpunit = new \PHPUnit_TextUI_Command();
    $phpunit->run(array('-c', __DIR__ . '/config.xml'));
}
```
