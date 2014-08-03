---
layout: blog_contents
title: MySQL排除结果集中存在于另一个表中的结果行
categories: blog
---

我们得到结果集 AR, 现在需要排除 AR 中的一些结果行，这些行是指那些存在于表 B 中的记录行，当不能通过主键或唯一索引来判断时，
可以如下操作:

```sql
SELECT tab1.f1, tab2.f2, tab3.f3 FROM tab1 
       LEFT JOIN tab2 ON tab1.id1=tab2.id1 
       LEFT JOIN tab3 ON tab2.id2=tab3.id2
       WHERE (SELCT COUNT(*) FROM tab4 WHERE tab4.f1=tab1.f1 AND tab4.f2=tab2.f2) =0
```

上面的是查询了 tab1, tab2, tab3 后得到一个结果集， 然后排除当 tab1.f1与tab2.f2存在于 tab4的行
这样查询速度上得到了保证