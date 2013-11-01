---
layout: blog_contents
title: MySQL使用SELECT结果集更新其他表
categories: blog
---

假如有表A,有如下字段:
   
    uid         用户ID
    post_count  用户发贴数
    is_del      用户是否已经被删除,0是否

表B有如下字段 
  
    uid         作者ID
    post_id     帖子ID
    is_del      帖子是否被删除,0是否
 
`post_count` 的值是 `post_id` 根据`uid` 的`COUNT`值,现在需要重新校正`post_count`的值，使用如下SQL：

```sql
UPDATE A INNER JOIN (
SELECT count( B.post_id ) AS cnt, B.uid AS id
FROM A
LEFT JOIN B ON A.uid = B.uid
WHERE A.is_del =0 AND B.is_del = 0
GROUP BY B.id
) AS C ON A.uid = C.id
SET A.post_count = C.cnt
```
