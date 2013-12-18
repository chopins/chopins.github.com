---
layout: blog_contents
title: MySQL 按条件分区域排序以及多字段排序
categories: blog
---

1. MySQL 多字段排序SQL为

```sql
ORDER BY field_A AND field_B ASC
```

   必须加 AND，如果用 , 其中一个会被忽略

2. 多区域查询，比如如下需求：
    有 A,B两个字段，分下面三个区域分别排序，
    1. 若字段 A 大与1000， B 大于 1000, 按 A 顺序排序
    2. 若字段 A 大与1000,  B 小于 1000, 按 A 顺序排序 
    3. 如果  A 小于 1000， B 小于 1000, 按 B 降序排序
   对于上述需求首先要找到一个最大数 BIG,然后
   SQL 如下：

```sql
SELECT * FROM tab ORDER BY 
    IF(A>1000 AND B>1000, A, IF(B<1000, A, (B*(-1))+BIG) ASC
```

   这条SQL语句的关键在于 `ORDER BY` 中使用了 IF 来做条件筛选,并且用一个小技巧