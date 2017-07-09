######################
路由配置
######################

路由配置文件位于应用目录下的 *config/router.ini*

**yml** 格式配置文件模板::

    test-rooter:
        prefix :
            path : '/p'
            controller : UserAuth@checkLogin
        path : /foo-{id}
        controller : MyController@test
        method : GET
        require:
            id : '0-9 :{9-12}'
            subdomain : www
        options : 
        schemes : 
        host : {subdomain}.host.com

**ini** 格式配置文件模板::

    [test-rooter]
    prefix.path =
    prefix.controller = UserAuth@checkLogin
    path = /test-{id}
    controller = MyController@test
    method = GET
    require.id = [0-9]{9-12}
    require.subdomain = www
    options = 
    schemes = 
    host = {subdomain}.host.com
    
参数解释
----------------
 
==========================  =========  ========  ====================================================================================
 配置名                      类型       必须       描述
==========================  =========  ========  ====================================================================================
**prefix**                  array      否        前缀，分组,字段与路由配置相同
**path**                    string     是        URL路径
**controller**              string     是        控制器PHP类名字
**method**                  string     否        如未设置，将使用**GET**，除 HTTP 的 method 外还支持 **CLI**
**require**                 array      否        参数列表，key 为参数名，值为匹配表达式, 这里的参数使用在 **path** 与 **host** 中

                                                 .. note:: 模式匹配表达式可能需要使用引号包括
                                                 
**schemes**                 string     否        请求协议
**host**                    string     否        请求的主机名
**before**                  string     否        控制器运行前调用本类
**after**                   string     否        控制器执行完调用本类
==========================  =========  ========  ====================================================================================

路由配置中类名字规则：
----------------------------

#. 所有PHP类名字不包括应用的名字空间和本组件类型名字空间
#. **controller** 定义的类的名字空间由主配置文件 **app.ctl_ns** 控制，默认为 ``Controller``
#. **before**,**after** ,**prefix.controller** 定义的类名字空间由主配置文件 **app.middleware_ns** 控制，默认为``Middleware``
#. 调用方法模板: *子名字空间.类名@方法名*
#. 调用静态方法模板: *子名字空间.类名:方法名*
#. 调用函数模板: *@子名字空间.函数名*，函数调用不支持命名空间,注意函数无法自动加载

类名转换规则表：
----------------------------

===============  ==========================  ============================================================
配置名            值                           实际调用
===============  ==========================  ============================================================
**controller**   ``MyController``            ``new AppNS\Controller\MyController``
**controller**   ``MyController@foo``        ``(new AppNS\Controller\MyController)->foo()``
**controller**   ``MyController:foo``        ``AppNS\Controller\MyController::foo()``
**controller**   ``@foo``                    ``AppNS\Controller\foo()``
**controller**   ``User.MyController@foo``   ``(new AppNS\Controller\User\MyController)->foo()``
**controller**   ``@User.foo``               ``AppNS\Controller\User\foo()``
**before**       ``CheckAuth@check``         ``(new AppNS\Middleware\CheckAuth)->check()``
===============  ==========================  ============================================================

.. note:: 上表类命名空间取了默认值

.. note:: **before**,**after** ,**prefix.controller** 除名字空间外，其他于 **controller** 相同

.. note:: **prefix.controller** 是指该路由配置的 *prefix* 的子项 *controller*


















