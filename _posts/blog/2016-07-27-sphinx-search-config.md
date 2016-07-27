---
layout: blog_contents
title: sphinx  search 配置
categories: blog
---

sphinx 是一个全文搜索引擎，按官方文档，其支持mysql mssql等数据源。
官方文档地址(http://sphinxsearch.com/docs/current.html)[http://sphinxsearch.com/docs/current.htm]

#sphinx 运行机制
sphinx 主要包括两部分：搜索服务与索引创建工具。运行安装目录下的`bin/searchd`即可启动搜索的后台运行服务。运行安装目录下的`bin/indexer`即可开始创建索引。

#配置

sphinx配置文件通常会在安装目录下`etc`目录，当然可编译时另外指定。

配置文件主要配置类型：数据源配置与索引数据配置，索引创建器配置，索引服务配置。

###数据源配置

数据源配置项应当位于类似下面的区块中。

```conf
source base
{
  
}
```

上面的`source`是指明这是数据源，`base`是这个数据的名字/key
数据源是创建sphinx indexer程序创建索引数据的依据。

###索引数据配置
索引数据配置项应当位于类似如下区块中。

```conf
index one_index 
{
}
```

上面的`index`是索引配置区块申明，`one_index`是这个索引的名字。

###其他配置项目

```conf
indexer
{
}
```

```conf
searchd
{
}
```

###数据源配置与索引数据配置理解

数据源既定义了创建索引所需要的数据，也定义了索引的结构，这些结构包括索引类型，索引项目等等。而索引数据配置定义了使用这些数据源的数据表。

#配置

以下是一个常规配置，配置说明见注释信息

```conf
source base
{
    type               = mysql  #数据源类型

    sql_host		= localhost #数据源服务器地址
    sql_user		= root	   #数据源连接用户名
    sql_pass		= 123456
    sql_db             = source    #指定数据源的数据库名
    sql_port		= 3306	# optional, default is 3306
    
    #在查询数据源以前执行，就是执行下面语句前执行
    #对于中文，必须要明确申明数据源的编码，否则会导致查询不到结果
    sql_query_pre = SET NAMES utf8  
    
    #获取主源数据的SQL语句
    sql_query = SELECT id, group_id, UNIX_TIMESTAMP(date_added) AS date_added, title, content, author_id FROM documents 

    #以下是定义属性字段，主键字段无需定义，
    #创建索引时`sql_query`中第一个字段将会被当_文档ID_。如果第一个字段的名字不是ID，将会被命名为ID。所以如果要使用字段原有的名字，需要另外增加一个同名查询字段
    #当查询时，查询字段只能是这些已经申明字段。
    #未申明但是出现在源数据中查询语句中的字段，只是作为全文搜索匹配和在`MATCH`函数中明确指明字段，不能用于`select`字段、排序、分组、SQL条件
    sql_attr_uint = group_id  #数字
    sql_field_string = content #字符串
     
    #附加数字数据，需要有主键来做关联，如果该数据仅仅是搜索匹配，必须以_文档ID_作为第一个字段
    #字段作为列存在，可排序分组等SQL操作
    sql_attr_multi = uint hit from query;SELECT id,hit from documents_status  
    
    #附加字符串数据，必须以_文档ID_作为第一个字段
    #这种字段不会出现在结果列中，只会为其创建索引并可在匹配模式中明确指定该字段，但是不能排序分组等SQL操作。
    sql_joined_field = author_name from query;SELECT d.id,a.author_name FROM documents d LEFT JOIN author a ON d.author_id = a.author_id 
    
}

index base
{
    source          = base  #使用的数据源
    path            = /var/lib/sphinx/base  #索引数据保存位置
    
    #以下配置为中文索引常用配置，项目说明见官方文档
    docinfo         = extern
    morphology = none
    charset_table   = U+FF10..U+FF19->0..9, 0..9, U+FF41..U+FF5A->a..z, U+FF21..U+FF3A->a..z,A..Z->a..z, a..z, U+0149, U+017F, U+0138, U+00DF, U+00FF, U+00C0..U+00D6->U+00E0..U+00F6,U+00E0..U+00F6, U+00D8..U+00DE->U+00F8..U+00FE, U+00F8..U+00FE, U+0100->U+0101, U+0101,U+0102->U+0103, U+0103, U+0104->U+0105, U+0105, U+0106->U+0107, U+0107, U+0108->U+0109,U+0109, U+010A->U+010B, U+010B, U+010C->U+010D, U+010D, U+010E->U+010F, U+010F,U+0110->U+0111, U+0111, U+0112->U+0113, U+0113, U+0114->U+0115, U+0115, U+0116->U+0117,U+0117, U+0118->U+0119, U+0119, U+011A->U+011B, U+011B, U+011C->U+011D, U+011D,U+011E->U+011F, U+011F, U+0130->U+0131, U+0131, U+0132->U+0133, U+0133, U+0134->U+0135,U+0135, U+0136->U+0137, U+0137, U+0139->U+013A, U+013A, U+013B->U+013C, U+013C,U+013D->U+013E, U+013E, U+013F->U+0140, U+0140, U+0141->U+0142, U+0142, U+0143->U+0144,U+0144, U+0145->U+0146, U+0146, U+0147->U+0148, U+0148, U+014A->U+014B, U+014B,U+014C->U+014D, U+014D, U+014E->U+014F, U+014F, U+0150->U+0151, U+0151, U+0152->U+0153,U+0153, U+0154->U+0155, U+0155, U+0156->U+0157, U+0157, U+0158->U+0159, U+0159,U+015A->U+015B, U+015B, U+015C->U+015D, U+015D, U+015E->U+015F, U+015F, U+0160->U+0161,U+0161, U+0162->U+0163, U+0163, U+0164->U+0165, U+0165, U+0166->U+0167, U+0167,U+0168->U+0169, U+0169, U+016A->U+016B, U+016B, U+016C->U+016D, U+016D, U+016E->U+016F,U+016F, U+0170->U+0171, U+0171, U+0172->U+0173, U+0173, U+0174->U+0175, U+0175,U+0176->U+0177, U+0177, U+0178->U+00FF, U+00FF, U+0179->U+017A, U+017A, U+017B->U+017C,U+017C, U+017D->U+017E, U+017E, U+0410..U+042F->U+0430..U+044F, U+0430..U+044F,U+05D0..U+05EA, U+0531..U+0556->U+0561..U+0586, U+0561..U+0587, U+0621..U+063A, U+01B9,U+01BF, U+0640..U+064A, U+0660..U+0669, U+066E, U+066F, U+0671..U+06D3, U+06F0..U+06FF,U+0904..U+0939, U+0958..U+095F, U+0960..U+0963, U+0966..U+096F, U+097B..U+097F,U+0985..U+09B9, U+09CE, U+09DC..U+09E3, U+09E6..U+09EF, U+0A05..U+0A39, U+0A59..U+0A5E,U+0A66..U+0A6F, U+0A85..U+0AB9, U+0AE0..U+0AE3, U+0AE6..U+0AEF, U+0B05..U+0B39,U+0B5C..U+0B61, U+0B66..U+0B6F, U+0B71, U+0B85..U+0BB9, U+0BE6..U+0BF2, U+0C05..U+0C39,U+0C66..U+0C6F, U+0C85..U+0CB9, U+0CDE..U+0CE3, U+0CE6..U+0CEF, U+0D05..U+0D39, U+0D60,U+0D61, U+0D66..U+0D6F, U+0D85..U+0DC6, U+1900..U+1938, U+1946..U+194F, U+A800..U+A805,U+A807..U+A822, U+0386->U+03B1, U+03AC->U+03B1, U+0388->U+03B5, U+03AD->U+03B5,U+0389->U+03B7, U+03AE->U+03B7, U+038A->U+03B9, U+0390->U+03B9, U+03AA->U+03B9,U+03AF->U+03B9, U+03CA->U+03B9, U+038C->U+03BF, U+03CC->U+03BF, U+038E->U+03C5,U+03AB->U+03C5, U+03B0->U+03C5, U+03CB->U+03C5, U+03CD->U+03C5, U+038F->U+03C9,U+03CE->U+03C9, U+03C2->U+03C3, U+0391..U+03A1->U+03B1..U+03C1,U+03A3..U+03A9->U+03C3..U+03C9, U+03B1..U+03C1, U+03C3..U+03C9, U+0E01..U+0E2E,U+0E30..U+0E3A, U+0E40..U+0E45, U+0E47, U+0E50..U+0E59, U+A000..U+A48F, U+4E00..U+9FBF,U+3400..U+4DBF, U+20000..U+2A6DF, U+F900..U+FAFF, U+2F800..U+2FA1F, U+2E80..U+2EFF,U+2F00..U+2FDF, U+3100..U+312F, U+31A0..U+31BF, U+3040..U+309F, U+30A0..U+30FF,U+31F0..U+31FF, U+AC00..U+D7AF, U+1100..U+11FF, U+3130..U+318F, U+A000..U+A48F,U+A490..U+A4CF
    min_infix_len   = 1
    min_prefix_len  = 0;
    min_word_len = 1 #### 索引的词最小长度
    charset_type = utf-8 #####数据编码

    #可以多个配置
    #regexp_filter = (phone|shouji|手机|sj) => 手机
    #单词+数字索引
    #需要re2的正则引擎替代系统内置的正则引擎，安装好后重新编译安装sphinx的源码，在./configure的时候带上参数--with-re2
    #regexp_filter　= (［a-z|A-Z］+)(\d+) =>\1 \2
    #
    ngram_len = 1
    #ngram_chars     = U+4E00..U+9FBF, U+3400..U+4DBF, U+20000..U+2A6DF, U+F900..U+FAFF,U+2F800..U+2FA1F, U+2E80..U+2EFF, U+2F00..U+2FDF, U+3100..U+312F, U+31A0..U+31BF,U+3040..U+309F, U+30A0..U+30FF, U+31F0..U+31FF, U+AC00..U+D7AF, U+1100..U+11FF,U+3130..U+318F, U+A000..U+A48F, U+A490..U+A4CF
    ngram_chars = U+4E00..U+9FBB, U+3400..U+4DB5, U+20000..U+2A6D6, U+FA0E, U+FA0F, U+FA11, U+FA13, U+FA14, U+FA1F, U+FA21, U+FA23, U+FA24, U+FA27, U+FA28, U+FA29, U+3105..U+312C, U+31A0..U+31B7, U+3041, U+3043, U+3045, U+3047, U+3049, U+304B, U+304D, U+304F, U+3051, U+3053, U+3055, U+3057, U+3059, U+305B, U+305D, U+305F, U+3061, U+3063, U+3066, U+3068, U+306A..U+306F, U+3072, U+3075, U+3078, U+307B, U+307E..U+3083, U+3085, U+3087, U+3089..U+308E, U+3090..U+3093, U+30A1, U+30A3, U+30A5, U+30A7, U+30A9, U+30AD, U+30AF, U+30B3, U+30B5, U+30BB, U+30BD, U+30BF, U+30C1, U+30C3, U+30C4, U+30C6, U+30CA, U+30CB, U+30CD, U+30CE, U+30DE, U+30DF, U+30E1, U+30E2, U+30E3, U+30E5, U+30E7, U+30EE, U+30F0..U+30F3, U+30F5, U+30F6, U+31F0, U+31F1, U+31F2, U+31F3, U+31F4, U+31F5, U+31F6, U+31F7, U+31F8, U+31F9, U+31FA, U+31FB, U+31FC, U+31FD, U+31FE, U+31FF, U+AC00..U+D7A3, U+1100..U+1159, U+1161..U+11A2, U+11A8..U+11F9, U+A000..U+A48C, U+A492..U+A4C6
    html_strip      = 1
}


indexer
{
	mem_limit		= 128M
}


searchd
{
    listen                  = 127.0.0.1:9312
	listen			= 9306:mysql41
	log			= /var/log/sphinx/searchd.log
	query_log		= /var/log/sphinx/query.log
	read_timeout		= 5
	max_children		= 30
	pid_file		= /var/log/sphinx/searchd.pid
	seamless_rotate		= 1
	preopen_indexes		= 1
	unlink_old		= 1
	workers			= threads # for RT to work
	binlog_path		= /var/lib/sphinx/
}

```

sphinx配置项可以继承其他的同类型配置项，例如下面的配置： 

```conf
source a
{
}

source b:a
{
}

index a
{
}

index b:a
{
}

```

上面的配置展示了`源配置b`继承`源配置a`,`索引b`继承了`索引a`。需要注意的是，子配置会覆盖掉父配置中的同名配置项目。

#增量索引

sphinx的增量索引是通过主数据源加增量数据源来实现的。主数据源索引全部数据，增量数据源只索引一个时间点以来的数据。在数据源配置上两种配置模式是一样的。只是增量数据源的`sql_query`需要加上一个查询条件。而对于索引配置，两种数据并没有区别。
而为应对搜索，查询语句可以显示地申明从两种索引中查询，也可以增加一个分布式索引来包括这两个索引数据。
分布式索引配置例子如下：

```conf
index all
{
    type        = distributed  #申明本索引类型为分布式索引
    local       = a  # 关联的本地索引a
    local       = b
    agent       = 192.168.1.1000:9312:index_a  #关联一个远程索引
}
```

#实时索引

实时索引需要在数据更新时，实时向sphinx插入更新数据，可以通过调用相关API或者使用SQL语句进行更新相关索引数据

配置如下：
```conf
index rt_index
{
    type = rt    
    path = /var/lib/sphinx/data/rt  #索引文件数据路径
    
    #以下是索引字段，
    rt_field = title
    rt_field = content
    rt_attr_uint = gid
}
```

