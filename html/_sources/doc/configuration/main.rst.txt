######################
主配置文件参考
######################

主配置文件保存了应用主要的配置数据，默认使用 **ini** 格式文件，也可以使用 **yml** 格式。主配置文件类型需要在入口文件调用 ``main()`` 函数时传入。
主配置文件为应用目录下的 *config/config.ini* 或 *config/config.yml*。
主配置文件主要包括以下几个区块：

#. **app** 应用配置区块,必须配置的区块
#. **vendor** 引入的其他组件，必须配置的区块
#. **wrapper** 处理请求的协议，必须配置的区块
#. **database** 数据库配置区块

****************************
应用配置区块: *app*
****************************

**ini** 格式参考例子::

    [app]
    display_trace = true
    timezone = UTC
    charet = utf8
    app_ns=Model
    ctl_ns=Controller
    db_table_ns=Model\DB
    middleware_ns=Middleware
    service_ns=
    view_ns=View
    router = Toknot\Share\Router
    short_except_path = true
    model_dir = runtime/model
    default_layout = Model\View\Layout\DefaultLayout
    route_conf_type = ini
    ;session config see http://php.net/session.configuration
    session.enable = true
    session.table = session
    session.name = sid
    session.cookie_httponly = 1


**yml** 格式参考例子::

    app :
        display_trace : true
        timezone : UTC
        charet : utf8
        app_ns : Tool
        ctl_ns : Controller
        model_ns : Model
        middleware_ns : Middleware
        service_ns:
        db_table_ns : Model
        route_conf_type : yml
        view_ns : View
        default_call : rt
        short_except_path : true
        model_dir : runtime/model
        default_layout : Model\View\Layout\DefaultLayout
        log :
            enable : false
            logger : runtime/logs/trace
        session :
            enable : true
            table : session
            name : sid
            cookie_httponly : 1


**app** 区块参数解释：
----------------------
 
==========================  =========  =====  ===============================================================================
配置名                      类型        必须    描述
==========================  =========  =====  ===============================================================================
**display_trace**           boolean    否     是否显示异常堆栈信息，如果未设置该项，框架将不显示堆栈信息
**timezone**                string     否     应用的默认时区，并不影响框架的时区
**charet**                  string     否     应用的默认字符集
**app_ns**                  string     是     应用的命名空间
**ctl_ns**                  string     是     应用的控制器命名空间，该值不包括应用的命名空间
**model_ns**                string     否     应用的模型命名空间，该值不包括应用的命名空间
**middleware_ns**           string     是     中间件命名空间，该值不包括应用的命名空间
**db_table_ns**             string     否     数据库表映射到PHP类的命名空间，这些映射类在使用数据库时将会自动生成
**route_conf_type**         string     是     路由配置文件类型
**view_ns**                 string     否     应用视图命名空间，命名空间名字不包括应用的命名空间
**default_call**            string     是     默认调用wrapper配置块项目key
**short_except_path**       boolean    否     在异常堆栈信息中，是否改为显示文件相对路径，而非绝对路径
**model_dir**               string     否     数据库表映射类文件保存目录，该值应当是相对于应用目录的相对路径
**default_layout**          string     否     应用视图的默认布局
**log**                     array      否     日志相关配置项目，子项如下：
                                                 
                                              - **enable**   是否保存日志
                                              - **logger**   日志操作方式，如果是路径，将使用默认方式保存到本地日志文件，
                                                            如果该值实现``Toknot\Boot\Logger``类，将会调用该类处理日志信息
 
**session**                 array      否      session相关配置项目，子项如下：
                                                 
                                               - **enable**    是否启用 session
                                               - **table**     session 保存的表
                                               - **name**      session 名字
                                               - **cookie_httponly**  session 不能通过 javascript 获取
                                         
                                               .. note:: 目前框架提供的存储 session 的方式是保存到数据库，暂未提供其他方法
                                                 
==========================  =========  =====  ===============================================================================


*************************************
引用的其他项目配置区块: *vendor*
*************************************

本区块每增加一个配置项目，将项目导入一个相关库。项目模板类似：``key = vendor/Vendor_namespace``。
例子::

    [vendor]
    dbal = doctrine/Doctrine
    routing = symfony/Symfony
    phpdoc = zend/Zend

*************************************
处理请求的协议配置区块: *wrapper*
*************************************

本区块配置处理请求的方式，Toknot 框架提供了``Toknot\Share\Route\Router``路由器来处理请求，配置模板如下面的例子。
例子::

    [wrapper]
    rt = Toknot\Share\Route\Router
    ts = Toknot\Share\Service\Wrapper

*************************************
数据库配置区块: *database*
*************************************

本区块为数据库相关配置，框架提供的数据操作组件将会使用本区块配置。
**ini** 格式配置例子::

    [database]
    default =db1
    ext_type = tinyint
    ;primary database
    db1.host = 127.0.0.1
    db1.config_type = ini
    db1.port = 3306
    db1.user = root
    db1.password = 
    db1.dbname = process
    db1.charset = utf8
    db1.type = mysql
    db1.table_config = database   ;tables info config file
    db1.table_default.engine = innodb
    db1.table_default.collate = utf8_general_ci
    db1.column_default.unsigned = true
    db1.column_default.collate = utf8_general_ci
    db1.config_type = ini

    db2.host = 127.0.0.1
    db2.port = 3306
    db2.user = root
    db2.password = 
    db2.dbname = word
    db2.charset = utf8
    db2.type = mysql
    db2.table_config = word   ;tables info config file
    db2.table_default.engine = innodb
    db2.table_default.collate = utf8_general_ci
    db2.column_default.unsigned = false
    db2.column_default.collate = utf8_general_ci

**yml** 格式配置例子，下面配置使用 *yml* 文件的锚点引用功能::

    database :
        ext_type : tinyint
        default : db2
        dbdefault: &dbdefault
            host : 127.0.0.1
            port : 3306
            user : root
            password : 
            charset : utf8
            type : mysql
            table_default:
                engine : innodb
                collate : utf8_general_ci
            column_default:
                unsigned : true
                collate : utf8_general_ci
        db1:
            dbname : process
            table_config : database
            config_type : yml
            << : *dbdefault
        db2:
            dbname : event
            table_config : process
            config_type : yml
            << : *dbdefault

数据库参数：
--------------------

==========================  =========  ====  ====================================================================================
配置名                      类型       必须   描述
==========================  =========  ====  ====================================================================================
**ext_type**                string     否        表配置文件中的数据库类型扩展，需要提供相应的类型操作类，顶级配置
**default**                 string     是        默认使用的数据库配置项，顶级配置
**host**                    string     是        数据库服务器地址，数据库配置项配置
**port**                    string     否        数据库服务器端口，数据库配置项配置
**user**                    string     是        数据库用户名，数据库配置项配置
**password**                string     否        数据库密码，数据库配置项配置
**charset**                 string     是        数据库编码，数据库配置项配置
**type**                    string     是        数据库类型，数据库配置项配置，可支持如下类型:

                                                 - **mysql**        MySQL
                                                 
                                                 .. note:: MySQL驱动使用顺序依次是：PDO,mysqli,mysql
                                                 
                                                 - **mssql**        MySQL
                                                 - **mysql2**       MySQL,Amazon RDS
                                                 - **db2**          IBM DB2
                                                 - **postgres**     PostgreSQL
                                                 - **postgresql**   PostgreSQL
                                                 - **pgsql**        PostgreSQL
                                                 - **sqlite3**      SQLite
                                                 - **sqlite**       SQLite

**dbname**                  string     是        数据库名，数据库配置项配置
**table_config**            string     是        数据库表配置文件名，需要路径，文件位于 *config* 目录下，数据库配置项配置
**config_type**             string     是        数据库表配置文件类型，*yml* 、 *ini* 或自定义类型，数据库配置项配置
**table_default**           string     否        数据库表默认属性，可配置默认数据引擎或字符集等属性，数据库配置项配置
**column_default**          string     否        数据库表字段默认属性，数据库配置项配置
==========================  =========  ====  ====================================================================================
