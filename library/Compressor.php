<?php

/*
* Project DocX (http://git.oschina.net/azhai/docx)
* @copyright 2014 MIT License.
* @author Ryan Liu <azhai@126.com>
*
* CODE FROM: http://code.google.com/p/php-compressor/
* AUTHOR: Вариант номер три  http://blog.amartynov.ru/
*/

class Compressor
{
    public static $RESERVED_VARS = array(
        '$GLOBALS' => 1,
        '$_ENV' => 1,
        '$_SERVER' => 1,
        '$_SESSION' => 1,
        '$_REQUEST' => 1,
        '$_GET' => 1,
        '$_POST' => 1,
        '$_FILES' => 1,
        '$_COOKIE' => 1,
        '$HTTP_RAW_POST_DATA' => 1,
        '$php_errormsg' => 1,
        '$http_response_header ' => 1,
        '$argc ' => 1,
        '$argv ' => 1,
        '$this' => 1
    );
    public $comment = null;
    public $options = array(
        'shrink' => true,     //混淆变量名
        'phptag' => true,      //添加PHP开始标记
        'keeplb' => false,     //保持换行
    );
    private $tokens = array();
    private $ns = array();
    
    function Compressor(array $options = array())
    {
        $this->options = array_merge($this->options, $options);
    }
    
    function compress($out_filename, $wildcard)
    {
        $filename = ''; // 先定义变量
        $exist_files = array();
        $wildcards = array_slice(func_get_args(), 1);
        foreach ($wildcards as $wildcard) {
            $filenames = glob($wildcard);
            foreach ($filenames as $filename) {
                if (! in_array($filename, $exist_files)) { // 去重复文件
                    $this->load(file_get_contents($filename));
                    $exist_files[] = $filename;
                }
            }
        }
        if ($content = $this->run()) {
            file_put_contents($out_filename, str_replace("\n\n", "\n", $content), LOCK_EX);
            if ($filename) { // 对照修改文件的所有者
                chown($out_filename, fileowner($filename));
                chgrp($out_filename, filegroup($filename));
            }
            $this->tokens = array();
            $this->ns = array();
        }
        return filesize($out_filename);
    }

    function load($text)
    {
        $start = count($this->tokens);
        $ns_name = $this->add_tokens($text);
        if (empty($ns_name)) {
            if (!empty($this->ns))
                $this->tokens[] = "}\n"; //添加中间结束
        } else if (!isset($this->ns[$ns_name])) {
            $ns_open = (empty($this->ns) ? "" : "}\n") . "namespace $ns_name{";
            array_splice($this->tokens, $start, 0, array(array(-1, $ns_open)));
            $this->ns[$ns_name] = $start;
        }
    }

    function run()
    {
        if ($this->options['shrink']) {
            $this->shrink_var_names();
        }
        $this->remove_public_modifier();
        $result = $this->options['phptag'] ? "<?php\n" : "";
        $content = $this->generate_result($result);
        if (!empty($this->ns) && substr($content, strlen($content)-2) !== "}\n")
            $content .= "}"; //添加全文结束
        return $content;
    }

    private function generate_result($result)
    {
        if ($this->comment) {
            foreach ($this->comment as $line) {
                $result .= "# " . trim($line) . "\n";
            }
        }

        foreach ($this->tokens as $t) {
            $text = $t[1];

            if (!strlen($text))
                continue;

            if (preg_match("~^\\w\\w$~", $result[strlen($result) - 1] . $text[0]))
                $result .= " ";

            $result .= $text;
        }

        return $result;
    }

    private function remove_public_modifier()
    {
        for ($i = 0; $i < count($this->tokens) - 1; $i++) {
            if ($this->tokens[$i][0] == T_PUBLIC)
                $this->tokens[$i] = $this->tokens[$i + 1][1][0] == '$' ? array(T_VAR, "var") : array(-1, "");
        }
    }

    private function shrink_var_names()
    {
        $stat = array();
        $indices = array();

        for ($i = 0; $i < count($this->tokens); $i++) {
            list($type, $text) = $this->tokens[$i];

            if ($type != T_VARIABLE)
                continue;

            if (isset(self::$RESERVED_VARS[$text]))
                continue;

            if ($i > 0) {
                $prev_type = $this->tokens[$i - 1][0];
                if ($prev_type == T_DOUBLE_COLON)
                    continue;
                if ($this->is_class_scope($i))
                    continue;
            }

            $indices[] = $i;
            if (!isset($stat[$text]))
                $stat[$text] = 0;
            $stat[$text] ++;
        }

        arsort($stat);

        $aliases = array();
        foreach (array_keys($stat) as $i => $name) {
            $aliases[$name] = $this->encode_id($i);
        }
        unset($stat);

        foreach ($indices as $index) {
            $name = $this->tokens[$index][1];
            $this->tokens[$index][1] = '$' . $aliases[$name];
        }
    }

    private function is_class_scope($index)
    {
        while ($index--) {
            $type = $this->tokens[$index][0];
            if ($type == T_CLASS)
                return true;
            if ($type == T_FUNCTION)
                return false;
        }
        return false;
    }

    private function add_tokens($text)
    {
        $tokens = token_get_all(trim($text));
        if (!count($tokens))
            return;

        if (is_array($tokens[0]) && $tokens[0][0] == T_OPEN_TAG)
            array_shift($tokens);

        $last = count($tokens) - 1;
        if (is_array($tokens[$last]) && $tokens[$last][0] == T_CLOSE_TAG)
            array_pop($tokens);

        $pending_whitespace = count($this->tokens) ? "\n" : "";

        $ns_name = false;
        $ns_uses = array();
        $i = 0;
        $count = count($tokens);
        while ($i < $count) {
            $t = $tokens[$i++];
            if (!is_array($t))
                $t = array(-1, $t);

            if ($t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT)
                continue;

            if ($t[0] === T_WHITESPACE) {
                $pending_whitespace .= $t[1];
                continue;
            }

            if ($t[0] === T_NAMESPACE) { //处理namespace，顶部声明改为体内声明的形式
                do {
                    $t = $tokens[$i++];
                    if (!is_array($t))
                        $t = array(-1, $t);
                    if ($t[1] === ";")
                        break;
                    if (!in_array($t[0], array(T_NAMESPACE,T_COMMENT,T_WHITESPACE), true)) {
                        $ns_name .= $t[1];
                    }
                } while ($i < $count);
                continue;
            }

            if ($this->options['keeplb'] && strpos($pending_whitespace, "\n") !== false) {
                $this->tokens[] = array(-1, "\n");
            }
            $this->tokens[] = $t;
            if ($t[0] === T_END_HEREDOC) { //处理heredoc结尾，单独占一行
                $this->tokens[] = array(-1, "\n");
            }

            $pending_whitespace = "";
        }
        return $ns_name;
    }

    private function encode_id($value)
    {
        $result = "";

        if ($value > 52) {
            $result .= $this->encode_id_digit($value % 53);
            $value = floor($value / 53);
        }

        while ($value > 62) {
            $result .= $this->encode_id_digit($value % 63);
            $value = floor($value / 63);
        }

        $result .= $this->encode_id_digit($value);
        return $result;
    }

    private function encode_id_digit($digit)
    {
        if ($digit < 26)
            return chr(65 + $digit);
        if ($digit < 52)
            return chr(71 + $digit);
        if ($digit == 52)
            return "_";
        return chr($digit - 5);
    }
}
