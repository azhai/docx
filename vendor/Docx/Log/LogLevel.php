<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Log;

use Docx\Base\EnumType;

/**
 * 日志级别.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class LogLevel extends EnumType
{
    const __default = self::DEBUG;

    const EMERGENCY = 1;
    const ALERT = 2;
    const CRITICAL = 3;
    const ERROR = 4;
    const WARNING = 5;
    const NOTICE = 6;
    const INFO = 7;
    const DEBUG = 8;

    public function getConstants()
    {
        return ['EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR',
                'WARNING', 'NOTICE', 'INFO', 'DEBUG', ];
    }
}
