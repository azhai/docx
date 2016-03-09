layout:     post
date:       2015-05-20
title:      MySQL导出CSV文件（Haskell实现）
slug:       haskell-mysql-dumper
author:     Ryan Liu
tags:       mysql, haskell, dump, csv
comments:   true

为什么要写这个工具
====================

2015年3月份写的一个Haskell的程序，实现和mysql命令select into outfile差不多的功能。mysql的命令只能把文件导出到本机，另一个工具mydumper也能实现差不多的功能。

因为我需要简单地远程使用，又要比Navicat等界面工具使用更灵活。效率比mysql命令差一些，毕竟mysql的命令是C语言实现的，比Navicat好在可以自定义读取的字段和范围限制。

最后吐槽一下Haskell，这个文件编译后居然达到18M多。跟py2exe打包的Python程序一样大了，那个程序用到了PySide，打包了Python、Qt、libsvn的运行库。

代码 hsdump.hs
=================
```haskell
{-# LANGUAGE OverloadedStrings #-}
-- cabal update && cabal install cabal-install
-- cabal install cassava && cabal install yaml-config && cabal install hdbc-mysql && cabal install missingH
-- ghc -threaded -O2 -optc-O3 -funfolding-use-threshold=16 -fforce-recomp --make hsdump.hs

module Main where
import Control.Monad
import Data.Char (ord)
import Data.Csv
import Data.Convertible
import Data.Maybe (fromJust)
import qualified Data.ByteString.Lazy as Bytes (appendFile)
import qualified Data.List.Utils as Utils (join)
import qualified Data.Yaml.Config as Yaml
import Database.HDBC
import Database.HDBC.MySQL
import System.Directory (createDirectoryIfMissing)
import System.Environment (getArgs)


data Task =
    Task { sql :: String,
           params :: [SqlValue],
           outname :: String }

{- 拼接SQL语句，创建任务 -}
createTask :: Yaml.Config -> String -> String -> Task
createTask sqlconf start_day stop_day =
    Task { sql = "SELECT " ++ (Utils.join "," fields) ++ " FROM " ++ table
                ++ " WHERE " ++ condition ++ " ORDER BY " ++ order,
           params = params,
           outname = dayname ++ ".txt" }
    where
        (table:_) = Yaml.lookup "table" sqlconf :: [String]
        (index:_) = Yaml.lookup "index" sqlconf :: [String]
        (order:_) = Yaml.lookup "order" sqlconf :: [String]
        fields = Yaml.lookupDefault "fields" ["*"] sqlconf :: [String]
        dayname = case start_day of
            "" -> "20000000"
            otherwise -> filter (/='-') start_day   -- 去掉日期中间的横杠
        condition = case (start_day, stop_day) of
            ("", _) -> index ++ "<? OR " ++ index ++ " IS NULL"
            (_, "") -> index ++ ">=?"
            otherwise -> index ++ ">=? AND " ++ index ++ "<?"
        params = map toSql $ filter (/="") [start_day, stop_day]


{- 连接MySQL数据库 -}
connectDB :: Yaml.Config -> IO Connection
connectDB dbconf =
    do
        conn <- connectMySQL defaultMySQLConnectInfo {
            mysqlHost       = host,
            mysqlPort       = port,
            mysqlUser       = user,
            mysqlPassword   = password,
            mysqlDatabase   = database,
            mysqlUnixSocket = socket
        }
        runRaw conn "SET NAMES 'utf8'"              -- 设置字符集
        return conn
    where
        host = Yaml.lookupDefault "host" "localhost" dbconf :: String
        port = Yaml.lookupDefault "port" 3306 dbconf :: Int
        user = Yaml.lookupDefault "user" "root" dbconf :: String
        password = Yaml.lookupDefault "password" "" dbconf :: String
        database = Yaml.lookupDefault "database" "test" dbconf :: String
        socket = Yaml.lookupDefault "socket" "/var/lib/mysql.sock" dbconf :: String


{- 连接MS SQL SERVER数据库 -}
{-
import Database.HDBC.ODBC
connectDB :: Yaml.Config -> IO Connection
connectDB dbconf =
    do
        connectODBC conn_string
    where
        dsn = Yaml.lookupDefault "dsn" "" dbconf :: String
        host = Yaml.lookupDefault "host" "localhost" dbconf :: String
        port = Yaml.lookupDefault "port" 1433 dbconf :: Int
        user = Yaml.lookupDefault "user" "root" dbconf :: String
        password = Yaml.lookupDefault "password" "" dbconf :: String
        database = Yaml.lookupDefault "database" "test" dbconf :: String
        servername = case dsn of
            "" -> "Server=" ++ host ++ ";Port=" ++ (show port)
            otherwise -> "DSN=" ++ dsn
        conn_string = "Driver=FreeTDS;TDS_Version=8.0;" ++ servername ++ ";UID=" ++ user 
            ++ ";PWD=" ++ password ++ ";Database=" ++ database ++ ";Options=262144"
-}


{- 将转义字符再转义，即将\n变成\\n -}
escape :: String -> String
escape [] = []
escape (x:xs)
    | x `elem` ['\\', '\b', '\t', '\n', '\r'] = '\\' : x : escape xs
    | otherwise = x : escape xs


{- 将字段值转为字符串，其中NULL转为\N并转义 -}
fromMysql :: SqlValue -> String
fromMysql SqlNull = "\\N"
fromMysql val = fromJust $ fromSql val :: String


{- 将字段值转为字符串，同时将原本为字符串类型的值中的转义字符再转义 -}
escapeFromMysql :: SqlValue -> String
escapeFromMysql val@(SqlString _) = escape $ fromMysql val
escapeFromMysql val@(SqlByteString _) = escape $ fromMysql val
escapeFromMysql val@(SqlWord32 _) = escape $ fromMysql val
escapeFromMysql val@(SqlWord64 _) = escape $ fromMysql val
escapeFromMysql val = fromMysql val


dumpRecord :: [SqlValue] -> [String]
dumpRecord row =
    map escapeFromMysql row


{- 输出结果到Tab分隔的CSV文件 -}
outputRecords :: String -> Connection -> Task -> IO ()
outputRecords outpath conn task =
    do
        stmt <- prepare conn (sql task)
        _ <- execute stmt (params task)
        rows <- fetchAllRows stmt
        Bytes.appendFile outfile $ encodeWith encOpts $ map dumpRecord rows
    where
        encOpts = defaultEncodeOptions {
            encDelimiter = fromIntegral (ord '\t'),
            encQuoting = QuoteNone
        }
        outfile = outpath ++ (outname task)


main :: IO ()
main = do
    args <- getArgs                                 -- 读命令行参数，1个参数：yaml文件名
    conf <- Yaml.load (head args)                   -- 加载yaml中的配置
    dbconf <- Yaml.subconfig "db" conf
    sqlconf <- Yaml.subconfig "sql" conf
    outconf <- Yaml.subconfig "out" conf
    let path = Yaml.lookupDefault "path" "./data" outconf :: String
        pre = Yaml.lookupDefault "pre" "records-" outconf :: String
        outpath = path ++ "/" ++ pre
        (days:_) = Yaml.lookup "days" conf :: [[String]]
        tasks = zipWith (createTask sqlconf) days (tail days)   -- 以相邻两个日期作为最后两个参数
    createDirectoryIfMissing True path                          -- 如果目录不存在，就创建
    conn <- connectDB dbconf
    mapM_ (outputRecords outpath conn) tasks    -- 调用查询输出结果命令
    disconnect conn
```


配置 task.yml
===============
```ini
db:
    host:       localhost
    port:       3306
    user:       dba
    password:   password
    database:   test
    socket:     /var/lib/mysql/mysql.sock

sql:
    table:      users
    index:      created_at
    order:      id
    fields:     
        - id
        - username
        - password
        - created_at
        - modified_at
        - is_active

days:
    - ""
    - "2015-01-01"
    - "2015-02-01"
    - "2015-03-01"
    - "2015-04-01"
    - "2015-05-01"
    
out:
    path:   ./data
    pre:    users-
```