<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Utility;

use Docx\Base\EnumType;


/**
 * 文件类型.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class FileType extends EnumType
{
    const __prefix = 'TYPE_';
    const __default = self::UNKNOWN;

    const TYPE_FIFO = 1;     #named pipe
    const TYPE_CHAR = 2;     #character special device
    const TYPE_DIR = 3;      #directory
    const TYPE_BLOCK = 4;    #block special device
    const TYPE_LINK = 5;     #symbolic link
    const TYPE_FILE = 6;     #regular file
    const TYPE_UNKNOWN = 7;  #unknown file type

    public function getConstants()
    {
        return ['TYPE_FIFO', 'TYPE_CHAR', 'TYPE_DIR', 'TYPE_BLOCK',
                'TYPE_LINK', 'TYPE_FILE', 'TYPE_UNKNOWN', ];
    }
    
    public static function getFileType($filename)
    {
        return new self(filetype($filename));
    }
}
