---
layout: blog_contents
title: FMAI框架访问接口类文档
categories: tutorials
---

FMAI类为Toknot框架快速访问接口，提供了模块及常用功能的快速访问方法。这些方法按照配置文件帮助应用做了很多初始化工作,以及提供了常用功能快速调用工作。  
由于Toknot`Object`类提供了伪多重继承功能，所以在应用Controller类中可以通过`$this`来直接访问`FMAI`类的公共方法。  
__下面是常用方法说明:__  

1. `static import($className, $aliases)` 
    导入一个类，如果你的类按规则定义（规则是指各级命名空间与目录名一致,并且位于应用目录下，同一个顶级命名空间下），不需要手动导入，  
    `$className`  类全名  
    `$aliases`    类别名

2. `loadConfigure($ini)`   
    导入一个配置文件，导入的配置文件如果`key`相同，将会覆盖原有值  
    `$ini` 文件名  

3. `getCFG()`   
    获取当前配置信息，返回一个数组对象  

4. `enableHTMLCache($CFG)`  
    默认模板引擎状态，激活缓存页面输出内容
    `$CFG` 为模板配置信息

5. `newTemplateView($CFG)`  
    实例化一个模板引擎实例，返回`Renderer`对象实例，此方法将按配置信息初始化模板配置，因此如果使用默认模板解析引擎，需要调用本方法。
    `$CFG` 模板配置信息

7. `display($tplName)`  
    渲染并显示一个模板，
    `$tplName`为模板名，这个名字不包括文件后缀，通常路径相对于应用下的View目录

6. `getActiveRecord()`  
    返回一个`ActiveRecord`对象实例，本对象为数据操作映射。因此需要使用框架默认数据库操作时，需要先获取一个`ActiveRecord`对象实例。 

7. `getParam($index, $filter = true)`  
    获取一个排除路由器路径后的参数值，
    `$index`  按斜线分割，排除路由路径后的位置，从0开始
    `$filter` 是否对引号做过滤处理

8. `getGET($name)`  
    获取一个GET值，默认或处理引号

9. `getPOST($name)`  
    获取一个POST值，默认或处理引号  

10. `getCOOKIE($name)`  
    获取一个COOKIE值，默认或处理引号  

11. `throwForbidden()`  
    抛出拒绝访问异常  

12. `redirectController($class, $queryString = '')`  
    重定向到一个控制器   
    `$class` 控制器不包括`Controller`命名空间以上的信息  
    `$queryString` URL查询参数  

13. `convertClassToUri($class)`  
    转换控制器类为URL,
    `$class` 控制器不包括`Controller`命名空间以上的信息 

14. `setCurrentUser($user)`  
    设置当前用户对象  
    `$user` 当前用户对象  

15. `checkAccess($clsObj)`  
    检查并且设置当前用户对象访问本控制器的状态  
    `$clsObj` 当前控制器对象  

16. `throwNoPermission($message = '')`  
    抛出无权访问异常  
    `$message`  附加异常信息  

17. `getSubAction()`  
    排除路由路径后的第一个路径名  

18. `invokeSubAction()`  
    调用子路径，本方法会做用户访问权限检查  

19. `startSession($name = null)`  
    开始session，返回`Session`对象
    `$name` session名

20. `getCurrentExecTime()`
    获取应用到本方法被调用时的执行时间  

21. $D   本属性保存了模板变量，在应用内部使用`$this->D->NewValue`来设置$NewValue模板变量值
 
    