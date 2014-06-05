---
layout: blog_contents
title: 用户类的访问控制
categories: tutorials
---

1. 相关类
    
    Toknot\User\ClassAccessControl  类和方法操作控制
    Toknot\User\UserAccessControl   用户操作控制
    Toknot\User\Nobody              未登录用户
    Toknot\User\Root                Root用户
    Toknot\UserClass                其他用户类

2. 类的访问控制  

    如果需要使用框架的访问控制模块来控制控制器的调用权限，需要控制器继承了`Toknot\User\ClassAccessControl`
    在用户访问时，在构造函数中通过 `FMAI::setCurrentUser` 方法来设置当前用户，
    然后需要在控制器构造函数中调用 `FMAI::getAccessStatus`方法来获取当前用户是否可以访问本类
    `FAMI::getAccessStatus`方法会检测类权限属性，该属性值定义在一个与类同名（不区分大小写）的类常量中。值的完整格式如下：
    `M:0770,U:0,G:0,P:r`,逗号分割每个项目，分号分割项目 key 与值，key 与值必须成对出现，并不需包含所以项目
    前面各个项目依次为：类权限，所属用户ID，所属用户组ID，类的操作类型

3. 方法的访问控制  
 
    与类访问控制类似，方法的属性定义在与方法名同名（不区分大小写）的类常量中，值的定义方法与类访问控制相同
    框架路由到方法需要在控制器的 GET,POST 等方法中调用`FMAI::invokeSubAction`来实现

4. ROOT用户  

    ROOT 用户通过`Toknot\User\Root`来实现，ID为`0`,组ID为`0`, 相关配置位于配置文件的`User`块

5. Nobody用户  

    Nobody 用户通过`Toknot\User\Nobody`来实现，ID为`-1`,组ID为`-1`,

6. 普通用户  
    普通用户必须为`Toknot\UserClass`的实例，相关配置位于配置文件的`User`块