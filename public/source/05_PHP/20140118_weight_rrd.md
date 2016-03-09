layout:     post
date:       2014-01-18
title:      PHP权重轮询
slug:       weight-rrd
author:     Ryan Liu
tags:       php, rrd
comments:   true

解决方案
------------
轮询节点，按权重代替平均分配。

```php
<?php
function test_rrd()
{
    /* 数据初始化，weight: 权重 */
    $hosts = array('a'=>5, 'b'=>3, 'c'=>2);
    $result = array();
    /* 模拟10次 */
    for ($i = 0; $i < 10; $i++) {
        round_robin($hosts, $result);
    }
    /* 输出结果 */
    return implode(' ', $result);
}


/*
round robin 权重轮循
*/
function round_robin($hosts, &$result)
{
    static $currents = array();
    static $total = 0;
    if (empty($currents)) {
        $keys = array_keys($hosts);
        $currents = array_fill_keys($keys, 0); //PHP5.2+
        $total = array_sum($hosts);
    }

    $best = '';
    $max_weight = 0;
    foreach ($hosts as $key => $weight) {
        $current_weight = $currents[$key] += $weight;
        if ($current_weight >= $max_weight) {
            $best = $key;
            $max_weight = $current_weight;
        }
    }
    $currents[$best] -= $total;
    $result[] = $best;
}
```

