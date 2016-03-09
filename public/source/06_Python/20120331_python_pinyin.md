layout:     post
date:       2012-03-31
title:      GB2312中文转拼音
slug:       python-pinyin
author:     Ryan Liu
tags:       python, pinyin
comments:   true

只能转为常见音，不能处理多音字，也不带声调

支持只找每个字的首字母和中英文混合

```python
#-*- coding: utf-8 -*-

from bisect import bisect


FIRST_LETTERS = ["a", "b", "c", "d", "e", "f", "g", "h", "j", "k", "l", "m", "n",
          "o", "p", "q", "r", "s", "t", "w", "x", "y", "z"]
FIRST_NUMBERS = [1, 37, 233, 478, 674, 702, 833, 994, 1187, 1506, 1612, 1872,
            2035, 2122, 2130, 2258, 2427, 2486, 2790, 2958, 3084, 3325, 3649]
PINYIN_LETTERS = [
    ['a', 'ai', 'an', 'ang', 'ao'],
    ['ba', 'bai', 'ban', 'bang', 'bao', 'bei', 'ben', 'beng', 'bi', 'bian', 'biao', 'bie', 'bin', 'bing', 'bo', 'bu'],
    ['ca', 'cai', 'can', 'cang', 'cao', 'ce', 'ceng', 'cha', 'chai', 'chan', 'chang', 'chao', 'che', 'chen', 'cheng', 'chi', 'chong', 'chou', 'chu', 'chuan', 'chuang', 'chui', 'chun', 'chuo', 'ci', 'cong', 'cou', 'cu', 'cuan', 'cui', 'cun', 'cuo'],
    ['da', 'dai', 'dan', 'dang', 'dao', 'de', 'deng', 'di', 'dian', 'diao', 'die', 'ding', 'diu', 'dong', 'dou', 'du', 'duan', 'dui', 'dun', 'duo'],
    ['e', 'en', 'er'],
    ['fa', 'fan', 'fang', 'fei', 'fen', 'feng', 'fo', 'fou', 'fu'],
    ['ga', 'gai', 'gan', 'gang', 'gao', 'ge', 'gei', 'gen', 'geng', 'gong', 'gou', 'gu', 'gua', 'guai', 'guan', 'guang', 'gui', 'gun', 'guo'],
    ['ha', 'hai', 'han', 'hang', 'hao', 'he', 'hei', 'hen', 'heng', 'hong', 'hou', 'hu', 'hua', 'huai', 'huan', 'huang', 'hui', 'hun', 'huo'],
    ['ji', 'jia', 'jian', 'jiang', 'jiao', 'jie', 'jin', 'jing', 'jiong', 'jiu', 'ju', 'juan', 'jue', 'jun'],
    ['ka', 'kai', 'kan', 'kang', 'kao', 'ke', 'ken', 'keng', 'kong', 'kou', 'ku', 'kua', 'kuai', 'kuan', 'kuang', 'kui', 'kun', 'kuo'],
    ['la', 'lai', 'lan', 'lang', 'lao', 'le', 'lei', 'leng', 'li', 'lia', 'lian', 'liang', 'liao', 'lie', 'lin', 'ling', 'liu', 'long', 'lou', 'lu', 'lv', 'lue', 'lv', 'lu', 'luan', 'lue', 'lun', 'luo'],
    ['ma', 'mai', 'man', 'mang', 'mao', 'me', 'mei', 'men', 'meng', 'mi', 'mian', 'miao', 'mie', 'min', 'ming', 'miu', 'mo', 'mou', 'mu'],
    ['na', 'nai', 'nan', 'nang', 'nao', 'ne', 'nei', 'nen', 'neng', 'ni', 'nian', 'niang', 'niao', 'nie', 'nin', 'ning', 'niu', 'nong', 'nu', 'nv', 'nuan', 'nue', 'nuo'],
    ['o', 'ou'],
    ['pa', 'pai', 'pan', 'pang', 'pao', 'pei', 'pen', 'peng', 'pi', 'pian', 'piao', 'pie', 'pin', 'ping', 'po', 'pou', 'pu'],
    ['qi', 'qia', 'qian', 'qiang', 'qiao', 'qie', 'qin', 'qing', 'qiong', 'qiu', 'qu', 'quan', 'que', 'qun'],
    ['ran', 'rang', 'rao', 're', 'ren', 'reng', 'ri', 'rong', 'rou', 'ru', 'ruan', 'rui', 'run', 'ruo'],
    ['sa', 'sai', 'san', 'sang', 'sao', 'se', 'sen', 'seng', 'sha', 'shai', 'shan', 'shang', 'shao', 'she', 'shen', 'sheng', 'shi', 'shou', 'shu', 'shua', 'shuai', 'shuan', 'shuang', 'shui', 'shun', 'shuo', 'si', 'song', 'sou', 'su', 'suan', 'sui', 'sun', 'suo'],
    ['ta', 'tai', 'tan', 'tang', 'tao', 'te', 'teng', 'ti', 'tian', 'tiao', 'tie', 'ting', 'tong', 'tou', 'tu', 'tuan', 'tui', 'tun', 'tuo'],
    ['wa', 'wai', 'wan', 'wang', 'wei', 'wen', 'weng', 'wo', 'wu'],
    ['xi', 'xia', 'xian', 'xiang', 'xiao', 'xie', 'xin', 'xing', 'xiong', 'xiu', 'xu', 'xuan', 'xue', 'xun'],
    ['ya', 'yan', 'yang', 'yao', 'ye', 'yi', 'yin', 'ying', 'yo', 'yong', 'you', 'yu', 'yuan', 'yue', 'yun'],
    ['za', 'zai', 'zan', 'zang', 'zao', 'ze', 'zeng', 'zha', 'zhai', 'zhan', 'zhang', 'zhao', 'zhe', 'zhen', 'zheng', 'zhi', 'zhong', 'zhou', 'zhu', 'zhua', 'zhuai', 'zhuan', 'zhuang', 'zhui', 'zhun', 'zhuo', 'zi', 'zong', 'zou', 'zu', 'zuan', 'zui', 'zun', 'zuo']
]
PINYIN_NUMBERS = [
    [2, 3, 16, 25, 28],
    [37, 55, 63, 78, 90, 113, 128, 132, 138, 162, 174, 178, 182, 188, 203, 222],
    [233, 234, 245, 252, 257, 262, 267, 269, 280, 283, 293, 312, 321, 327, 337, 352, 368, 373, 385, 408, 415, 421, 426, 433, 435, 447, 453, 454, 458, 461, 469, 472],
    [478, 484, 502, 517, 522, 534, 537, 544, 563, 579, 588, 601, 610, 611, 621, 629, 643, 649, 653, 662],
    [674, 687, 688],
    [702, 710, 727, 738, 750, 765, 780, 781, 782],
    [833, 835, 841, 852, 861, 871, 888, 889, 891, 904, 919, 928, 946, 952, 955, 966, 969, 985, 988],
    [994, 1001, 1008, 1027, 1030, 1039, 1057, 1059, 1063, 1068, 1077, 1084, 1108, 1117, 1122, 1136, 1150, 1171, 1177],
    [1187, 1246, 1263, 1309, 1322, 1350, 1377, 1403, 1428, 1430, 1447, 1472, 1479, 1489],
    [1506, 1510, 1515, 1521, 1528, 1532, 1547, 1551, 1553, 1557, 1561, 1568, 1573, 1577, 1579, 1587, 1604, 1608],
    [1612, 1619, 1622, 1637, 1644, 1653, 1655, 1666, 1669, 1709, 1710, 1724, 1735, 1748, 1753, 1765, 1779, 1790, 1805, 1811, 1831, 1842, 1843, 1844, 1845, 1851, 1853, 1860],
    [1872, 1881, 1887, 1902, 1908, 1920, 1921, 1937, 1940, 1948, 1962, 1971, 1979, 1981, 1987, 1993, 1994, 2017, 2020],
    [2035, 2042, 2047, 2050, 2051, 2056, 2057, 2059, 2060, 2061, 2072, 2080, 2081, 2083, 2090, 2091, 2103, 2107, 2111, 2114, 2115, 2116, 2119],
    [2122, 2123],
    [2130, 2136, 2142, 2150, 2155, 2162, 2171, 2173, 2187, 2210, 2214, 2218, 2220, 2225, 2234, 2243, 2244],
    [2258, 2294, 2303, 2325, 2333, 2348, 2353, 2364, 2377, 2379, 2387, 2407, 2417, 2425],
    [2427, 2431, 2436, 2439, 2441, 2451, 2453, 2454, 2464, 2467, 2477, 2479, 2482, 2484],
    [2486, 2489, 2493, 2503, 2506, 2510, 2513, 2514, 2515, 2524, 2526, 2542, 2550, 2561, 2573, 2589, 2606, 2653, 2663, 2702, 2704, 2708, 2710, 2713, 2717, 2721, 2725, 2741, 2749, 2753, 2765, 2768, 2779, 2782],
    [2790, 2805, 2814, 2832, 2845, 2856, 2857, 2861, 2876, 2884, 2889, 2892, 2908, 2921, 2925, 2936, 2938, 2944, 2947],
    [2958, 2965, 2967, 2984, 2994, 3033, 3043, 3046, 3055],
    [3084, 3125, 3138, 3164, 3184, 3208, 3229, 3239, 3254, 3261, 3270, 3289, 3305, 3311],
    [3325, 3341, 3374, 3391, 3412, 3427, 3480, 3502, 3520, 3521, 3536, 3556, 3607, 3627, 3637],
    [3649, 3652, 3659, 3663, 3666, 3680, 3686, 3690, 3710, 3716, 3733, 3748, 3758, 3768, 3784, 3805, 3848, 3859, 3873, 3905, 3907, 3908, 3914, 3921, 3927, 3929, 3940, 3955, 3962, 3966, 3974, 3976, 3980, 3982]
]


def gb2312_pinyin(unichar, first_letter=False):
    assert(isinstance(unichar, unicode))
    gbkchar = unichar.encode("GBK")
    high_code = ord(gbkchar[0]) - 160  #GBK区码
    low_code = ord(gbkchar[1]) - 160   #GBK位码
    char_code = (high_code - 16) * 100 + low_code
    if -1299 <= char_code <= -1206:
        return chr(char_code + 1332) #全角转半角
    elif char_code < 1 or char_code > 3989:
        return "" #不是汉字，或者未被GB2312收录的生僻字

    idx = bisect(FIRST_NUMBERS, char_code)
    if first_letter: #找首字母
        result = FIRST_LETTERS[idx - 1]
    else: #完整拼音
        inidx = bisect(PINYIN_NUMBERS[idx - 1], char_code)
        result = PINYIN_LETTERS[idx - 1][inidx - 1]
    return result


def to_unicode(word):
    if not isinstance(word, unicode):
        try: #尝试当作UTF-8编码转为UNICODE
            word = unicode(word, "UTF-8")
        except UnicodeDecodeError:
            try: #尝试当作GBK编码转为UNICODE
                word = unicode(word, "GBK")
            except UnicodeDecodeError:
                word = ""
    return word


def split_sentence(sentence):
    """ 将中英文混合的句子分割成单个汉字和连续英文 """
    word = ""
    for character in sentence:
        if ord(character) <= 255: #ASCII
            word += character
        else:
            yield True, word
            word = ""
            yield False, character
    yield True, word


def words_to_pinyin(words, first_letter=False, seperator=""):
    uniwords = to_unicode(words)
    letters = []
    for is_ascii, word in split_sentence(uniwords):
        if word:
            if not is_ascii:
                word = gb2312_pinyin(word, first_letter)
            letters.append(word)
    return seperator.join(letters)


def words_pinyin_for_sort(words, first_letter=False):
    uniwords = to_unicode(words)
    word_pinyin = words_to_pinyin(uniwords, first_letter, seperator="~")
    if len(uniwords) > 0 and ord(uniwords[0]) > 255:
        word_pinyin = "~" + word_pinyin
    return word_pinyin


if "__main__"==__name__:
    print words_to_pinyin("好V5的中文", first_letter=True)
    print words_to_pinyin("好V5的中文！", seperator=" ")
    print words_pinyin_for_sort("好V5的中文！")
```