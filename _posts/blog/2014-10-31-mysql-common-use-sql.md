---
layout: blog_contents
title: MySQL 常用SQL语句
categories: blog
---

1. 修改用户密码  
    能够登录进入MySQL时，可以使用直接更新mysql用户表的办法来修改用户密码，下面是例子：  

    `$ mysql -uroot -p` 
 
    ```sql
    mysql> USE mysql;   
    mysql> UPDATE user SER Password=PASSWORD('new_password') WHERE User='user_name';   
    mysql> FLUSH PRIVILEGES;   
    ```

    或者,使用`SET PASSWORD`进行设置，可以在MySQL终端中使用`help set password`查询使用信息，下面是其中一个例子

    ```sql
    mysql> SET PASSWORD FOR 'root'@'localhost' = PASSWORD('newpass');
    ```

    另外就是使用`mysqladmin`来修改用户密码，下面是例子    
    当你没有设置过`root`用户密码时：   
    `$ mysqladmin -u root password NEWPASSWORD`，  
    `NEWPASSWORD`是你要设置的新密码  
    当你已经更新过`root`用户密码时：
    `$ mysqladmin -u root -p password NEWPASSWORD`,   
    `NEWPASSWORD`是你要设置的新密码，命令会要求你输入旧密码  
    如果忘记`root`用户密码时，可以在启动MySQL时跳过权限表，然后无需密码即可登入MySQL进行修改，方法如下：  
    `$ mysqld_safe --skip-grant-tables&`  
    密码修改方法与前面的相同。

2. 创建从复制配置    
    对于主服务器的配置，主要是设置一个唯一的`server-id`以及开启binlog日志，增加一个具备`REPLICATION SLAVE`权限的用户。  
    对于从服务器的相关配置官方建议通过SQL语句来进行配置，而不是将配置信息写入MySQL配置文件中。  
    配置文件中主要配置`server-id`，并增加`skip-slave-start`配置项，以及需要复制的库，或需要忽略的库。   
    完成后我们登入MySQL,执行下面的`CHANGE MASTER TO` SQL语句,该语句帮助信息可以通过`help change master to`获取：  

    ```sql
    mysql> CHANGE MASTER TO MASTER_HOST='192.168.0.3',MASTER_USER='replication',MASTER_PASSWORD='password',MASTER_PORT=3306, MASTER_CONNECT_RETRY=10;   
    mysql> START SLAVE;   
    ```

    上面是一个简单的配置，当主从服务器都没数据时，这样配置即可。但是如果主服务器上已经有数据就不能这样了。   
    对于`MYISAM`引擎，需要先执行`FLUSH TABLES WITH READ LOCK`来进行写锁定并获取二进制日志名和偏移量值（使用`SHOW MASTER STATUS;`来获取相关信息），然后进行数据备份。完毕后执行`UNLOCK TABLES;`，然后在从服务器上导入数据，并设置好获取到的二进制日志名和偏移量。

    对于`InnoDB`引擎，我们可以使用`mysqldump`的`--master-data` 和`--single-transaction`配合使用进行数据备份。

3. 获取自定义格式时间   
    使用`DATE_FORMAT()`函数，例如获取当期日期如`2014-11-11`格式，使用   
    `SELECT DATE_FORMAT(NOW(),'%Y-%m-%d');`  

4. EVENT相关操作  
    创建EVENT:  
 
    ```SQL
    CREATE EVENT `event_name` ON SCHEDULE EVERY 10 SECOND STARTS '2014-07-18 15:01:40' ON COMPLETION NOT PRESERVE ENABLE DO CALL KillSleepThread()
    ```   

    更新使用:`ALTER EVENT`   
    显示状态使用:`SHOW EVENTS;`    
    显示创建信息:`SHOW CREATE EVENT event_name;`   
    查看当前是否开启了event scheduler三种方法:   

    ```sql
    mysql> SHOW VARIABLES LIKE 'event_scheduler';  
    mysql> SELECT @@event_scheduler;  
    mysql> SHOW PROCESSLIST;//(是否有State为：Waiting for next activation的进程，User为event_scheduler)  
    ```

5. 存储过程操作:   
    创建使用PROCEDURE：  

    ```sql
    CREATE DEFINER=`root`@`localhost` PROCEDURE `KillSleepThread`( )`  
    BEGIN  
    /*SQL*/   
    END   
    ```  

    显示创建信息:`SHOW CREATE PROCEDURE procedure_name;`   
    更新存储过程需要先删除，然后重新穿件，`DROP PROCEDURE procedure_name`   
    显示所有存储过程:`SHOW PROCEDURE STATUS;`  
 
6. 表信息查询   
    显示表结构:`SHOW COLUMNS FROM table_name;` 或者他的快捷方式`DESCRIBE table_name;``DESC table_name;`, 查询`information_schema`获取：`SELECT * FROM COLUMNS WHERE TABLE_NAME='表名' AND TABLE_SCHEMA='数据库名';  
    显示表信息：`SHOW TABLE STATUS LIKE 'table_name'`    
    显示表索引信息:`SHOW INDEX FROM table_name;`    
    