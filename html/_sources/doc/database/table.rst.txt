数据库表操作
=========================================

Toknot 框架会自动创建数据库表类映射。
首先获取表映射实例
::

    $user = DBA::table('user');

手动创建映射实例：
::
    
    use AppNS\Model\Db1\User;
    $db = DBA::single('db1');
    $user = new User($db);

#. 根据主键值查询
::

    $userId = 1;
    $user->findKeyRow($userId);

#. 根据主键删除
::
    
    $userId = 1;
    $user->deleteKeyRow($userId);

#. 创建过滤器实例，过滤器将会创建查询条件。使用方法见 ``Toknot\Share\DB\QueryWhere``
::

    $filter = $user->filter();
    
#. 创建表字段映射实例，该实例将会创建字段条件。使用方法见 ``Toknot\Share\DB\QueryColumn``
::

    $cols = $user->cols('user_id');
    $cols = $user->userId;

#. 表映射实例的所有接受查询条件的参数的方法，均可接受过滤器实例与字段映射实例。

#. 获取表所有记录
::

    $filter = 1;
    $user->getAllList($filter);
    
#. 获取从第2行开始的前10行记录
::

    $filter = 1;
    $user->getList($filter, 10,2);

#. 插入数据
::

    $data = ['field'=> 1];
    $user->insert($data);
    
#. 更新数据
::

    $filter = $user->cols('field2')->eq(2);
    $data = ['field'=>1];
    $user->update($data,$filter,1,1);
    //update user set field = 1 where field2 = 2 limit 1,1
    

