######################
配置文件说明
######################

Toknot 配置文件保存在应用目录下面的 *config/* 文件夹内，通过应用初始化向导会默认创建 **主配置文件** ， **路由配置文件** 。
目前的框架版本默认支持 **ini** 与简化版本的 **yml** 格式配置文件。如果需要支持其他格式类型的配置文件，需要在网站入口文件（通常是index.php）文件中包含解析该配置文件的PHP类文件，然后传入 ``main()`` 函数。

入口文件例子如下::

    include 'yor_config_type_path/SelfConfigParse.php'
    main('your_app_path','my','SelfConfigParse');


.. note:: PHP默认的 **ini** 文件只支持两个维度。

.. note:: Toknot 框架的 **ini** 支持任意维度配置，在配置时可以使用 key 中的点来划分不同维度。

.. note:: Toknot 框架的 **yml** 只支持简单格式，不支持数组等复杂格式，

**********************
ini 配置文件
**********************

Toknot 框架可以解析出 **ini** 配置文件中按点来划分出的多维配置项目，例如如下配置::

    [block.subblock]
    item.subitem.sub = 1
    
将会解析出数组::
    
    array(
        block => array(
            subblock => array(
                item => array(
                    subitem => array(
                        sub => 1
                    )
                )
            )
        );

数据类型与 PHP 默认支持类型相同

**********************
yml
**********************

Toknot 支持的 *yml* 格式并不完全。配置项必须有严格的缩进，不能解析出用括号包含的块区域。
目前可解析的类型有：*string*，*boolean*, *null*, *int*, *float* 类型。
以下字符串值会被特殊处理：

- **yes** , **true**  转换成布尔 true
- **no** , **false**  转换成布尔 false
- **~**             转换成 null

********************** 
主要配置文件说明：
**********************

.. toctree::
	:titlesonly:
	
	main
	router
	database
	
	

