---
layout: blog_contents
title: phpMyAdmin数据库结构页速度优化
categories: blog
---

当数据库中存在大量表时，数据库结构页将显著变慢，这里得解决办法是增加结构数据缓存

__以下修改基于 phpMyAdmin 3.5.8__
首先是优化导航页：
在navigation.php中找到（大约在240行）

```php
if ($GLOBALS['cfg']['LeftFrameLight'] && strlen($GLOBALS['db'])) {
```

找到类似下面两行代码

```php
$table_list = PMA_getTableList($GLOBALS['db'], null, $tpos, $cfg['MaxTableList']);
$table_count = PMA_getTableCount($GLOBALS['db']);
```

将其替换为下列代码：

```php
$table_count = PMA_getTableCount($GLOBALS['db']);
    if($table_count > 50) {
        $table_list_cache_file = "/tmp/phpMyAdminTableListCache.{$GLOBALS['db']}";
        if(!file_exists($table_list_cache_file) && (time() - 3600 > filemtime($table_list_cache_file))) {
            $table_list = PMA_getTableList($GLOBALS['db'], null, $tpos, $cfg['MaxTableList']);
            $table_list_cache = serialize($table_list);
            file_put_contents($table_list_cache_file, $table_list_cache);
        } else {
            $table_list_cache = file_get_contents($table_list_cache_file);
            $table_list = unserialize($table_list_cache);
            if($table_count != count($table_list)) {
                $table_list = PMA_getTableList($GLOBALS['db'], null, $tpos, $cfg['MaxTableList']);
                $table_list_cache = serialize($table_list);
                file_put_contents($table_list_cache_file, $table_list_cache);
            }
        }
    } else {
        $table_list = PMA_getTableList($GLOBALS['db'], null, $tpos, $cfg['MaxTableList']);
    }
```

上面创建了缓存文件/tmp/phpMyAdminTableListCache.DBNAME

然后打开文件libraries/db_info.inc.php
找到下列注释(大约在106行）

```php
/**
 * @global array information about tables in db
 */
```

然后将

```php
$tables = array();
```

替换为

```php
$used_table_info_cache = false;
$table_info_cache_file = "/tmp/phpMyAdminTableInfoCache.{$GLOBALS['db']}";
$table_list_cache_file = "/tmp/phpMyAdminTableListCache.{$GLOBALS['db']}";
if(file_exists($table_list_cache_file)) {
    $used_table_info_cache = true;
}
$tables = array();
if($used_table_info_cache && file_exists($table_info_cache_file) && abs(filemtime($table_info_cache_file)-filemtime($table_list_cache_file) > 120)) {
    $table_info_cache = file_get_contents($table_info_cache_file);
    $tables = unserialize($table_info_cache);
    $num_tables = count($tables);
    //  (needed for proper working of the MaxTableList feature)
    if (!isset($total_num_tables)) {
        $total_num_tables = $num_tables;
    }
    /**
    * Displays top menu links
    * If in an Ajax request, we do not need to show this
    */
   if ($GLOBALS['is_ajax_request'] != true) {
       include './libraries/db_links.inc.php';
   }
   return;
}
```

然后在找到：

```php
/**
 * @global int count of tables in db
 */
```

在其前面增加如下代码

```php
if($used_table_info_cache) {
    $table_info_cache = serialize($tables);
    file_put_contents($table_info_cache_file, $table_info_cache);
}
```
