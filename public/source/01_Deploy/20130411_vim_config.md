layout:     post
date:       2013-04-11
title:      VIM配置
slug:       vim-config
author:     Ryan Liu
tags:       vim
comments:   true


vim配置文件 ~/.vimrc
----------------------
```vim
" 设定配色方案
" colorscheme molokai
" 显示行号
set nu
" 突出显示当前行
" set cursorline
" 用浅色高亮当前行
autocmd InsertEnter * se cul
" 自动语法高亮
syntax on
" 覆盖文件时不备份
set nobackup
" 自动切换当前目录为当前文件所在的目录
" set autochdir
" 去掉输入错误的提示声音
set noeb

"代码补全
set completeopt=preview,menu
"允许插件
filetype plugin on
"共享剪贴板
set clipboard+=unnamed

" 文件编码
set fencs=utf-8,ucs-bom,shift-jis,gb18030,gbk,gb2312,cp936
set termencoding=utf-8
set encoding=utf-8
set fileencodings=ucs-bom,utf-8,cp936
set fileencoding=utf-8

" 设定 tab 长度为 4
set tabstop=4
" 设置按BackSpace的时候可以一次删除掉4个空格
set softtabstop=4
" 设定 << 和 >> 命令移动时的宽度为 4
set shiftwidth=4
set smarttab
" 智能自动缩进
set smartindent
" 显示空格和制表符
set listchars=tab:>-,trail:-
set list

" 不自动换行
"set nowrap
" 解决自动换行格式下, 如高度在折行之后超过窗口高度结果这一行看不到的问题
set display=lastline

```
常用操作
-------------------
参考

[毛渊的窝: vim(vi)常用操作及记忆方法](http://arhat.blog.51cto.com/101503/114148])

[绿色冰点: VIM常用操作, 插件和vimrc文件](http://www.cnblogs.com/moodlxs/archive/2012/03/24/2415526.html)

VI的三种模式
1，命令模式   2，输入模式   3，末行模式


命令         执行的操作                 记忆方法
:q             退出                     quit
:w             存盘                     write
:e             打开新文件
:r             读取文件到VI             read
:!             强行
:set nu        显示行号                 number
:set nonu      隐藏行号                 no number


h     j     k     l
左    下    上    右

Ctrl + f       翻到下一页（向前翻页）     front
Ctrl + b       翻到上一页（向后翻页）     back
Ctrl + u       向前翻半页
Ctrl + d       向后翻半页


^              移到行头          往上就到行头了（象形）
$              移到行尾          写完一行就要给一行的钱
w              下一个单词        word
b              前一个单词        behind（在。。。后面）
e              下一单词尾        end
#G             跳到某一行        大哥(G)说到哪就到哪
i              光标前插入        insert
a              光标后加入        add
A              在行末加入        在一个词后是小a,一个行后就是大A
o              另起一行加入      一个小鸡蛋（小o）掉下来了摔开了花
O              上一行加入        吐一个大泡泡（大O）飞上去破了


－－－－－－－－－－ c（吃掉）代表行内删除－－－－－－－－
cw       删除一个单词（一部分不包括空格）  吃掉一个 word
c$       删除一行到行尾                    刚写的一行被删了，钱也拿不到了
c^       删除一行到行头                    往上吃，一直吃到头
x        删除一个字符                      看你不爽就打上“x”


－－－－－－－－－－ d 代表删除－－－－－－－－－－－－－－
dd       删除一行                           del dir
dw       删除单词到尾部（包括空格）         del word
de       删除单词到尾部（不包括尾部空格）   del end
d$       删除当前到行尾的所有字符           del $(代表尾部)
d^       删除当前到行首的所有字符           del ^(代表行首)


J       合并当前行                   一个大钩子(J)把下面的一行拉到自己行尾
u       撤销上次操作                 undo
U       撤销当前行所有操作           事情闹大了，得有个更大的UNDO才能恢复
Ctrl + r    恢复undo 前              recover


－－－－－－－－－ y 代表复制到缓存中－－－－－－－－－－－
yy           复制当前行整行的内容到vi缓冲区
yw           复制当前光标到单词尾字符的内容到vi缓冲区
y$           复制当前光标到行尾的内容到vi缓冲区
y^           复制当前光标到行首的内容到vi缓冲区
p            读取vi缓冲区中的内容，并粘贴到光标当前的位置（不覆盖文件已有的内容）


/word       从上而下查              /是从上而下写的吧
?word       从下而上查找            字符在哪儿呢（？）回头找找吧
n           定位下一个匹配的        相当于向下查找下一个 next
N           定位上一个匹配的        相当于向上查找上一个


:s/1/2          搜索当前行第一个1并用2代替      search
:s/1/2/g        搜索当前行所有的1并用2代替      global
:#,#s/1/2/g     在#,#间搜索所有1并用2替换
:%s/1/2/g       在整个文档中将1替换为2          100％（全部）
:s/1/2/c        每次替换都给出提示确认          cue提示


vim 1.txt 2.txt 3.txt  同时打开多个文档
:args             显示多文件信息(会在末行提示当前打开了哪些档)     are globals
:next             切换到下一个文件
:prev             切换到上一个文件
:first            定位首文件
:last             定位尾文件
Ctrl + ^          快速切换到编辑器中切换前的文件
