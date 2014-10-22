
<?php
$last_dir = '';
foreach ($docs as $dir => & $files):
    list($dir_url, $backward, $forward) = compare_pathes($dir, $last_dir);
    if ($backward + $forward > 0):
        echo str_repeat("    </ul>\n</li>\n", $backward);
        $remain = substr($dir, strlen($dir_url));
        $dir_url = ltrim($dir_url, '.');
        $items = explode('/', trim($remain, '/'));
        foreach ($items as $dir_item):
            $dir_url .= '/' . $dir_item;
?>
<li<?php echo starts_with($curr_url, $dir_url) ? ' class="open"' : ''; ?>>
    <a href="#" class="aj-nav folder"><?php echo clean_ord($dir_item); ?></a>
    <ul class="nav nav-list">
<?php
        endforeach;
    endif;
    $last_dir = $dir;
    
    foreach ($files as $file => & $metas):
        if ($metas['slug'] === 'home'):
            continue;
        endif;
        if ($curr_url === $metas['url']):
            echo '<li class="active"><a href="#">' . $metas['title'] . '</a></li>'; 
            echo "\n";
        else:
            $link = $urlpre . $metas['url'] . $urlext;
            echo '<li><a href="' . $link . '">' . $metas['title'] . '</a></li>'; 
            echo "\n";
        endif;
    endforeach;
endforeach;
echo str_repeat("    </ul>\n</li>\n", substr_count($last_dir, '/'));
?>
