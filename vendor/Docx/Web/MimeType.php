<?php

/*
 * This file is part of Docx.
 *
 * Copyright (c) 2014 MIT License
 */

namespace Docx\Web;

use Docx\Base\EnumType;


/**
 * 文件类型.
 *
 * @author Ryan Liu <azhai@126.com>
 */
class MimeType extends EnumType
{
    const __prefix = 'EXT_';
    const __default = self::EXT_HTML;

    const EXT_HTML = 'text/html';
    const EXT_HTM = 'text/html';
    const EXT_SHTML = 'text/html';
    const EXT_CSS = 'text/css';
    const EXT_XML = 'text/xml';
    const EXT_GIF = 'image/gif';
    const EXT_JPEG = 'image/jpeg';
    const EXT_JPG = 'image/jpeg';
    const EXT_JS = 'application/javascript';
    const EXT_ATOM = 'application/atom+xml';
    const EXT_RSS = 'application/rss+xml';

    const EXT_MML = 'text/mathml';
    const EXT_TXT = 'text/plain';
    const EXT_JAD = 'text/vnd.sun.j2me.app-descriptor';
    const EXT_WML = 'text/vnd.wap.wml';
    const EXT_HTC = 'text/x-component';

    const EXT_PNG = 'image/png';
    const EXT_TIF = 'image/tiff';
    const EXT_TIFF = 'image/tiff';
    const EXT_WBMP = 'image/vnd.wap.wbmp';
    const EXT_ICO = 'image/x-icon';
    const EXT_JNG = 'image/x-jng';
    const EXT_BMP = 'image/x-ms-bmp';
    const EXT_SVG = 'image/svg+xml';
    const EXT_SVGZ = 'image/svg+xml';
    const EXT_WEBP = 'image/webp';

    const EXT_WOFF = 'application/font-woff';
    const EXT_JAR = 'application/java-archive';
    const EXT_WAR = 'application/java-archive';
    const EXT_EAR = 'application/java-archive';
    const EXT_JSON = 'application/json';
    const EXT_HQX = 'application/mac-binhex40';
    const EXT_DOC = 'application/msword';
    const EXT_PDF = 'application/pdf';
    const EXT_PS = 'application/postscript';
    const EXT_EPS = 'application/postscript';
    const EXT_AI = 'application/postscript';
    const EXT_RTF = 'application/rtf';
    const EXT_M3U8 = 'application/vnd.apple.mpegurl';
    const EXT_XLS = 'application/vnd.ms-excel';
    const EXT_EOT = 'application/vnd.ms-fontobject';
    const EXT_PPT = 'application/vnd.ms-powerpoint';
    const EXT_WMLC = 'application/vnd.wap.wmlc';
    const EXT_KML = 'application/vnd.google-earth.kml+xml';
    const EXT_KMZ = 'application/vnd.google-earth.kmz';
    const EXT_7Z = 'application/x-7z-compressed';
    const EXT_CCO = 'application/x-cocoa';
    const EXT_JARDIFF = 'application/x-java-archive-diff';
    const EXT_JNLP = 'application/x-java-jnlp-file';
    const EXT_RUN = 'application/x-makeself';
    const EXT_PL = 'application/x-perl';
    const EXT_PM = 'application/x-perl';
    const EXT_PRC = 'application/x-pilot';
    const EXT_PDB = 'application/x-pilot';
    const EXT_RAR = 'application/x-rar-compressed';
    const EXT_RPM = 'application/x-redhat-package-manager';
    const EXT_SEA = 'application/x-sea';
    const EXT_SWF = 'application/x-shockwave-flash';
    const EXT_SIT = 'application/x-stuffit';
    const EXT_TCL = 'application/x-tcl';
    const EXT_TK = 'application/x-tcl';
    const EXT_DER = 'application/x-x509-ca-cert';
    const EXT_PEM = 'application/x-x509-ca-cert';
    const EXT_CRT = 'application/x-x509-ca-cert';
    const EXT_XPI = 'application/x-xpinstall';
    const EXT_XHTML = 'application/xhtml+xml';
    const EXT_XSPF = 'application/xspf+xml';
    const EXT_ZIP = 'application/zip';

    const EXT_BIN = 'application/octet-stream';
    const EXT_EXE = 'application/octet-stream';
    const EXT_DLL = 'application/octet-stream';
    const EXT_DEB = 'application/octet-stream';
    const EXT_DMG = 'application/octet-stream';
    const EXT_ISO = 'application/octet-stream';
    const EXT_IMG = 'application/octet-stream';
    const EXT_MSI = 'application/octet-stream';
    const EXT_MSP = 'application/octet-stream';
    const EXT_MSM = 'application/octet-stream';

    const EXT_DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    const EXT_XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    const EXT_PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

    const EXT_MID = 'audio/midi';
    const EXT_MIDI = 'audio/midi';
    const EXT_KAR = 'audio/midi';
    const EXT_MP3 = 'audio/mpeg';
    const EXT_OGG = 'audio/ogg';
    const EXT_M4A = 'audio/x-m4a';
    const EXT_RA = 'audio/x-realaudio';

    const EXT_3GPP = 'video/3gpp';
    const EXT_3GP = 'video/3gpp';
    const EXT_TS = 'video/mp2t';
    const EXT_MP4 = 'video/mp4';
    const EXT_MPEG = 'video/mpeg';
    const EXT_MPG = 'video/mpeg';
    const EXT_MOV = 'video/quicktime';
    const EXT_WEBM = 'video/webm';
    const EXT_FLV = 'video/x-flv';
    const EXT_M4V = 'video/x-m4v';
    const EXT_MNG = 'video/x-mng';
    const EXT_ASX = 'video/x-ms-asf';
    const EXT_ASF = 'video/x-ms-asf';
    const EXT_WMV = 'video/x-ms-wmv';
    const EXT_AVI = 'video/x-msvideo';

    public function getConstants()
    {
        return [
            'EXT_HTML', 'EXT_HTM', 'EXT_SHTML', 'EXT_CSS', 'EXT_XML', 'EXT_GIF',
            'EXT_JPEG', 'EXT_JPG', 'EXT_JS', 'EXT_ATOM', 'EXT_RSS', 'EXT_MML',
            'EXT_TXT', 'EXT_JAD', 'EXT_WML', 'EXT_HTC', 'EXT_PNG', 'EXT_TIF',
            'EXT_TIFF', 'EXT_WBMP', 'EXT_ICO', 'EXT_JNG', 'EXT_BMP', 'EXT_SVG',
            'EXT_SVGZ', 'EXT_WEBP', 'EXT_WOFF', 'EXT_JAR', 'EXT_WAR', 'EXT_EAR',
            'EXT_JSON', 'EXT_HQX', 'EXT_DOC', 'EXT_PDF', 'EXT_PS', 'EXT_EPS',
            'EXT_AI', 'EXT_RTF', 'EXT_M3U8', 'EXT_XLS', 'EXT_EOT', 'EXT_PPT',
            'EXT_WMLC', 'EXT_KML', 'EXT_KMZ', 'EXT_7Z', 'EXT_CCO', 'EXT_JARDIFF',
            'EXT_JNLP', 'EXT_RUN', 'EXT_PL', 'EXT_PM', 'EXT_PRC', 'EXT_PDB',
            'EXT_RAR', 'EXT_RPM', 'EXT_SEA', 'EXT_SWF', 'EXT_SIT', 'EXT_TCL',
            'EXT_TK', 'EXT_DER', 'EXT_PEM', 'EXT_CRT', 'EXT_XPI', 'EXT_XHTML',
            'EXT_XSPF', 'EXT_ZIP', 'EXT_BIN', 'EXT_EXE', 'EXT_DLL', 'EXT_DEB',
            'EXT_DMG', 'EXT_ISO', 'EXT_IMG', 'EXT_MSI', 'EXT_MSP', 'EXT_MSM',
            'EXT_DOCX', 'EXT_XLSX', 'EXT_PPTX', 'EXT_MID', 'EXT_MIDI', 'EXT_KAR',
            'EXT_MP3', 'EXT_OGG', 'EXT_M4A', 'EXT_RA', 'EXT_3GPP', 'EXT_3GP',
            'EXT_TS', 'EXT_MP4', 'EXT_MPEG', 'EXT_MPG', 'EXT_MOV', 'EXT_WEBM',
            'EXT_FLV', 'EXT_M4V', 'EXT_MNG', 'EXT_ASX', 'EXT_ASF', 'EXT_WMV',
            'EXT_AVI',
        ];
    }
}
