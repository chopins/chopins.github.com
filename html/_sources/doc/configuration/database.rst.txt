######################
数据库结构配置
######################

数据库使用配置在主配置文件中进行配置。该配置项中指定了数据库表结果配置文件信息。
表结构 **ini** 配置文件模板如下::

    [user.column]
    uid.type = integer
    uid.length = 11
    uid.unsigned = true
    uid.autoincrement = true
    username.type= string
    username.length = 255

    [user.option]
    comment = 用户表
    ;engine=
    ;collate=

    [user.indexes]
    primary = uid
    login_name.type = unique
    login_name.fields = username

    login_email.type = unique
    login_email.fields = email
 
 表结构 **yml** 配置文件模板如下::
    
    user : 
        column : 
            uid : 
                type :  integer
                length :  11
                unsigned :  true
                autoincrement :  true

            username : 
                type :  string
                length :  255
        option : 
        comment :  用户表
        indexes : 
            primary :  uid
            login_name : 
                type :  unique
                fields : username

            login_email : 
                type :  unique
                fields : email
            
 
 结构说明：
 
 #. 每一个表为一个一级配置项，key 为表名
 #. 表配置项包含以下二级配置项：
 
    -  **字段(column)**     包含了所有表字段定义
    -  **索引(indexes)**    包含了所有表索引定义
    -  **表选项(option)**   包含了所有表属性定义
    -  **注释(comment)**    表注释定义
    
 #. 字段(column)列表，表字段为其子配置项，key 值为字段名字，字段项包括以下定义:
 
    -  **类型(type)**             字段类型，本项为必须项
    -  **长度(length)**           字段长度
    -  **注释(comment)**          字段注释
    -  **符号(unsigned)**         字段是否有符号
    -  **自增长(autoincrement)**  字段是否是自真长
    -  **字符集(collate)**        字段字符集
    -  **默认值(default)**        字段默认值
    -  **固定(fixed)**            字段长度固定
    - 其他数据库可用属性
 
 #. 索引(indexes)列表，索引配置为其子配置项，key 值为索引名,主键名只能为**primary**,值为主键字段名，复合主键用逗号分割。普通索引有以下定义：
 
    -  **type**      索引类型。例如：**index** , **unique**
    -  **feilds**    索引字段，多字段以逗号分割
    -  **comment**   索引注释


