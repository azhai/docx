layout:     post
date:       2013-03-21
title:      Zend Framework 2 配置多个数据库
slug:       zf2-multiple-dbs
author:     Ryan Liu
tags:       php, zf2, mysql
comments:   true

问题描述
------------
在一个Zend Framework 2项目，用到同一个MySQL下的几个数据库，甚至不同主机不同种类的数据库。

解决方案
------------
参考 [How to use multi database with zf2](http://giaule.com/2012/10/24/how-to-use-multi-database-with-zf2/)

将全局的dbAdapter的自动创建，推迟到每个Module的初始化文件中。

config/autoload/global.php 中

```php
<?php
'db' => array(
    'driver'         => 'Pdo',
    'dsn'            => 'mysql:host=localhost;dbname=',
    'driver_options' => array(
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
    ),
),

'service_manager' => array(
    'factories' => array(
        /*
        //被推迟为Module.php中的dbAdapter
        'Zend\Db\Adapter\Adapter'
                => 'Zend\Db\Adapter\AdapterServiceFactory',
        */
    ),
),
```
config/autoload/local.php 中

```php
<?php
'db' => array(
    'username' => 'dba',
    'password' => 'changeme',
),

```
module/Album/Module.php 中

```php
<?php
public function getServiceConfig()
{
    return array (
        'factories' => array (
            'dbAdapter' => function ($sm) {
                $config = $sm->get('config');
                $config['db']['dsn'] .= 'db_album';
                $dbAdapter = new \Zend\Db\Adapter\Adapter($config['db']);
                return $dbAdapter;
            },
            'Album\Model\AlbumTable' =>  function($sm) {
                $tableGateway = $sm->get('AlbumTableGateway');
                $table = new AlbumTable($tableGateway);
                return $table;
            },
            'AlbumTableGateway' => function ($sm) {
                $dbAdapter = $sm->get('dbAdapter');
                $resultSetPrototype = new ResultSet();
                $resultSetPrototype->setArrayObjectPrototype(new Album());
                return new TableGateway('t_album', $dbAdapter, null, $resultSetPrototype);
            },
        )
    );
}
```
