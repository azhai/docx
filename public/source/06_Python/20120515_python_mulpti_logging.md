layout:     post
date:       2012-05-15
title:      Python多进程记录日志
slug:       python-mulpti-logging
author:     Ryan Liu
tags:       python, logging, mulpti-process
comments:   true

## 问题描述

用gevent（或封装了gevent的gunicore）启动python进程，会出现多个独立进程同时写一个日志文件， 可以观察到有日志部分丢失：一个进程日志没写完，另一个进程把日志覆盖在同一行的后面；有些日志甚至完全丢失。 用mlogging包可以解决多进程写日志的问题，没有发现不完整的日志，是否丢失日志有待进一步检测。

## 解决方案与代码

下面是一个在python程序中记录重要信息，以便以后解析统计的函数

```python
#-*- coding: utf-8 -*-

import os.path
import logging
from mlogging import FileHandler_MP, TimedRotatingFileHandler_MP
from functools import partial


class LevelFilter(logging.Filter):

    def __init__(self, levelno, *args, **kwargs):
        self.levelno = levelno

    def filter(self, record):
        return record.levelno == self.levelno


def create_logger(name, logging_dir, level = 'NOTSET',
            filter = None, handler_class = TimedRotatingFileHandler_MP):
    logging_file = os.path.join(logging_dir, name+'.log')
    handler = handler_class(logging_file, 'midnight', 1)
    handler.setFormatter( logging.Formatter(
        '%(asctime)s %(levelname)-8s %(name)-20s %(message)s', #设置日志格式，固定宽度便于解析
        datefmt = '%Y-%m-%d %H:%M:%S' #设置asctime时间格式
    ))
    handler.suffix = '%Y%m%d'
    if isinstance(filter, logging.Filter):
        handler.addFilter(filter) #加载过滤器

    logger = logging.getLogger(name)
    logger.setLevel(getattr(logging, level)) #设置级别，DEBUG/INFO/WARNING/ERROR/CRITICAL
    #有些Python版本会报错KeyError，找不到clientip或user，这里用一个短横(-)做默认值
    extra={'clientip':'-', 'user':'-'}
    #exc_info是出错时的Debug详细回溯信息，这里禁止记录，只记录错误信息这一行
    setattr(logger, '_log', partial(logger._log, exc_info=False, extra=extra))
    logger.addHandler(handler)
    return logger


if __name__ == '__main__':
    logger = create_logger('test', './', 'INFO')
    logger.debug('低级别的DEBUG，不会记录。')
    logger.info('哈哈哈，这才是我想要的信息，请记下来。')
```