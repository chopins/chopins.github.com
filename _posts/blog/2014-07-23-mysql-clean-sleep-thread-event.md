---
layout: blog_contents
title: 自动清理 MySQL Sleep 线程
categories: blog
---

由于 MySQL Sleep 线程过多，导致 MySQL 链接资源浪费，现在依靠 MySQL 提供的 Event 功能来实现一个自动定期清理 Sleep 线程

首先需要创建一个存储过程, 当存储过程提供了获取 Sleep 线程并杀死其的功能，SQL 如下:

```sql
delimiter // #命令行中需要加上
CREATE DEFINER=`root`@`localhost` PROCEDURE `KillSleepThread`()
    COMMENT '杀死Sleep线程'
BEGIN
DECLARE Done INT DEFAULT 0;
DECLARE Idx INT DEFAULT 0;
DECLARE I INT DEFAULT 1;
DECLARE T INT DEFAULT 0;
DECLARE sql_state_code VARCHAR(255);
DECLARE CONTINUE HANDLER FOR 1094 SET sql_state_code='NoThread';
CREATE TEMPORARY TABLE IF NOT EXISTS processTableTmp  (
 id int unsigned not null AUTO_INCREMENT,
 Tid int unsigned not null,
 PRIMARY KEY (`id`)
) ENGINE = HEAP;
TRUNCATE TABLE processTableTmp;
INSERT INTO processTableTmp (Tid) SELECT Id FROM INFORMATION_SCHEMA.PROCESSLIST WHERE `Time`>10 AND Command='Sleep';
SELECT MAX(id) INTO Idx FROM processTableTmp;
WHILE I <= Idx DO
	SELECT id,Tid INTO Idx,T FROM processTableTmp WHERE id=I;
        SET I=I+1;
        KILL T;
END WHILE; 
DROP TABLE processTableTmp;
END//
delimiter;
```

KillSleepThread 这个存储过程将会杀死 Sleep 时间大于10秒的线程

然后创建一个事件,SQL如下

```sql
CREATE EVENT `CheckSleepThread` ON SCHEDULE EVERY 30 SECOND STARTS NOW() ON COMPLETION NOT PRESERVE ENABLE DO CALL KillSleepThread();
```

上面的 SQL 中 `NOW()` 可以替换成需要开始的日期
