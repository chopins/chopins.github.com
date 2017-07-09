命令行下参数访问
========================

Toknot 框架内核提供了命令行参数访问方法。支持长参数，短参数，以及数字索引。内核提供了以下方法:

#. ``Kernel::getArg()`` 获取指定参数,参数不存在返回 ``null``
#. ``Kernel::hasArg()`` 检查参数是否存在，返回 ``boolean`` 值，存在返回 ``true``, 否在 ``false``

长参数用法,对于命令： ``php index.php --with-foo=thevalue1  --with-foo2=thevalue2 --with-foo3``

::

    $kernel = Kernel::single();
    $kernel->getArg('--with-foo'); // thevalue1
    $kernel->getArg('--with-foo2'); // thevalue2
    $kernel->getArg('--with-foo3'); // 空字符串
    $kernel->getArg('--with-foo4'); // null
    
短参数用法,对于命令： ``php index.php -a 1 -b 2 -c``

::

    $kernel = Kernel::single();
    $kernel->getArg('-a'); // 1
    $kernel->getArg('-b'); // 2
    $kernel->getArg('-c'); // 空字符串
    $kernel->getArg('-d'); // null
    

数字索引用法,对于命令 ``php index.php a -a 2 -b --with-fo=value1 foo``

::
    
    $kernel = Kernel::single();
    $kernel->getArg('-a'); // 2
    $kernel->getArg('-b'); // 空字符串
    $kernel->getArg(1); // a
    $kernel->getArg('--with-fo'); //value1
    $kernel->getArg(6); //foo

以上也是混合获取参数的方法。
上面的例子可知道以下规则：

#. 长参数以 **双横线** ( **--** )开头，值于参数名用 **等号** ( **=**) 连接
#. 短参数以 **单横线** ( **-** )开头，参数名只能一个字（支持汉字），如果多字符将无法获取，值与参数名用至少一个空格（空格数无限制）连接。
#. 索引访只能用于无参数名的访问，但是参数索引位置是该参数默认位置。就是 PHP ``$argv`` 数组变量中的默认位置

命令行的其他操作方法主要由 ``Toknot\Share\CommandLine`` 类提供，文档见 :doc:`命令行类<../libraries/command>`
