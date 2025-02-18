---
layout: blog_contents
title: 一个解释器语法
categories: blog
---

1. 类型
   1. `int` 64位有符号整数
   2. `uint` 64 位无符号整数
   3. `string`  字符串，最长2GB
   4. `float`   64位双精度浮点数,IEC 60559标准
   5. `bool`    布尔数，`false`, `true`
   6. `null`
   7. `array`   任意可混合类型数组,Hash表
        ``` php
        $a = [1,'ss','k' => 'val'];
        $b = [1 => '2'];
        ```
   8. `object`  对象
   9.  `void`    无返回值类型
   10. `any`    任意类型
   11. `list`    单一类型数组，索引为自然整数,第一个元素确定类型
        ```php
        $a = (1,2,4,6);
        $b = ('ss', 'b', 'cass');
        $c = (1.23, 23.93,  45.6);
        $d = (false, true, false);
        ```
   12. `type`    标量类型定义关键字, 定义的类型长度必须为2的指数倍数
        ```php
        type LIKE_TYPE(TYPE_SIZE) NEW_TYPE_NAME
        type int(4) int32;
        type int(2) int16;
        type uint(16) uint128;
        type float(32) sfloat;
        ```
2. 变量
   1. 变量作用域
      1. 函数
      2. 组合方法
      3. 语句块内
      4. 顶级域，有且只有一个顶级域，其他域必须位于一个域中
      5. 使用`parent`关键字来导入上一级作用域中的变量
   2. 全局变量
      1. 全局变量在所有作用域中均可方法
      2. 使用`global`来声明
         ```php
         global $c = 2;
         function a() {
             global $b = 1;
             echo $c;
         }
         ```
      3. 全局优先级最高，不可声明成块变量
   3. 块变量
      1. 仅块代码中可访问
      2. 使用`var`来声明
            ```php
            for() {
               var $a = 1;
               var $b =2;
            }
            if() {
               var $a = 3;
            }
            ```
   4. 共享内存变量
      1. 共享多线程，多进程变量
      2. 共享内存赋值（写入）具备原子性
      3. `memory` 关键字
          ```php
          memory[share] = 1;//write,
          $a = memory[share];//read
          ```
3. 类组合
   1. 即将不同类的方法组合到一个新类中
   2. 类对象实例指针`$my`
   3. 类调用的引用关键字`my`
   4. 组合方法时，当前类的方法优先级最高，同名方法必须重命名才可访问
   5. 被导入类的方法的名字为被导入类当前可用名字，即，被导入类导入中存在别名方法时，当前导入类需要使用该别名
   6. 导入多个类时，同名方法需添加别名或通过类名访问。未添加别名时，与当前类同名的方法，无法直接使用方法名访问
   7. 普通方法访问: `$my.method()`
   8. 静态方法访问：`my.methid()`
   9. 同名的普通方法访问:`$my.NS.MixName.method()`
   10. 同名静态方法访问： `my.NS.MixName.method()`
   11. 使用`class`来声明一个可用组合
         ```php
         namespace NSA {
            class MixA {
               public mA()
               {

               }
               public mB()
               {

               }
               public static msD()
               {

               }
            }
         }
         namespace NSB {
            class MixB {
               use NSA.MixA;//MixA类名在MixB类中可见

               public mA {

               }
               public mB {
                  $my.mA(); //调用 MixB::mA() 方法
                  $my.NSA.MixA.mA(); //调用NSA.MixA::mA() 的方法
                  $my.MixA.mA(); //调用NSA.MixA::mA() 的方法
                  my.MSA.MixA.msD();
               }
            }
            class MixC {
               use MixB : mA as MixAmA;
               public mA {
                  $my.MixAmA(); //MixB::MixAmA
               }
            }
            class MixD {
               use NSA.MixA;
               use MixB;
            }
         }
         $a = new NSB.MixD;
         $a.NSA.MixA.mA();
         class NSB.MixD.NSA.MixA {

         }
         NSB.MixD.my.NSA.MixA.msD();//待定1
         MSB.MixD::NSA.MixA.msD();//待定2
         ```
4. 函数
   1. `func`关键字定义普通函数
   2. `()->`定义简单表达式函数
   3. `$args` 为函数内预定义变量，默认保存了当前传入参数
   4. `...` 为可选参数列表
   5. `func`定义的无名字函数为匿名函数
      ```php
      func A() : void {
         echo count($args);
      }
      $a = 1;
      $b = 2;
      $f = func($d, ...$c) use($b) : void {
         echo count($args);
         echo count($c);
         echo $b;
      }

      $f2 = ($c)-> $a + $b + $c;
      echo $f2(3); // echo 6
      A(1, 2,3,4); // echo 4
      $f(1, 2, 3, 4, 5); // echo 5; echo 4; echo 2;
      ```
5. 异常
   1. `try {} catch() {} finally {}`
   2. `raise`
   3. `Report` 所有异常的基类
      1. `Report` 原型
         ```php
         class Report {
               protected string $message = "";
               private string $string = "";
               protected int $code;
               protected string $file = "";
               protected int $line;
               private array $trace = [];
               private ?Report $previous = null;
               protected bool $throw = true;
               private static ?Report $lastException = null;
               /* 方法 */
               public __construct(string $message = "", int $code = 0, ?Report $previous = null)
               final public getMessage(): string
               final public getPrevious(): ?Report
               final public getCode(): int
               final public getFile(): string
               final public getLine(): int
               final public getTrace(): array
               final public getTraceAsString(): string
               final public getLastException() : ?Report
               final public static setThrow(string $exceptionClass, bool $isthrow): bool
         }
         ```
      2. `Report::$throw` 属性表示是否中断执行，为`true`时将中断执行，异常对象可被捕获，为`false`时将继续执行后续代码
          ```php
          class MyAException
          {
            use Report;
            protected bool $throw = false;
            public __init($msg)
            {
               $my.Report.__init($msg);
               echo $my->getMessage();
            }
            @string {
               return $this->getMessage();
            }
          }
          try {
            raise MyAException('message'); //等效 echo 'message';
            echo 'continue';  //将输出 continue
          } catch(Report $e) {
            echo 'catch exception'; //不会被捕获
          } finally {
            echo Report::getLastException();//echo 'message'
          }
          class MyBException
          {
            use Report;
            public __init($msg)
            {
               $my->break = true;
               $my.Report.__init($msg);
            }
          }
          try {
            raise MyAException('message'); //中断
            echo 'continue';  //不会执行
          } catch(Report $e) {
            echo 'catch exception'; //输出 catch exception
          } finally {
            echo Report::getLastException();//echo 'message'
          }
          ```
6. 注释
   1. `//`行注释
   2. `/* */` 块注释
7. 表达式
   1. `$my`,`my` 为对象与类引用
   2. `.`为类与对象方法和属性访问操作符，`new`类实例化操作符号
   3. `+, -, !, ~, ++, ?:`
   4. `+, -, *, /, %, &, &&, |, ||, ??, ^, <<, >>, ==, !=, >, <, >=, <=`
   5. `=`,`&`,`+=, -=, *=, /=, %=, |=, ^=, <<=, >>=`
   6. `===`带类型的值是否相同的比较,`!==`带类型的值是否不相同的比较
   7. `is` 是否是类实例，或子类
8. 命名空间
   1. `namespace`关键字开始定义命名空间
   2. `use` 为导入命名空间的类. `use C_NAME as C_A_N` 语法可以给类添加别名，没有别名时使用类名
   3. 同一个命名空间内，不能存在相同类名
   4. 类中使用`use`，在类范围类遵循相同规则。在类定义外无效。
   5. 命名空间名字以`.`分割，以点开头的命名空间为相对于当前命名空间
      ```php
      namespace NSTOP.NSA;
      use .NSC.ClassA; //访问的是 NSTOP.NSA.NSC.ClassA;
      use NSTOP2.NSB.ClassC;//访问的是 NSTOP2.NSB.ClassC;
      ```
9.  语句
    1. `for()` 循环
       1. `for(expr1;expr2;expr3) {}` 复杂条件循环
       2. `for($arr as $k : $v) {}` 迭代数组
       3. `for(expr) {}` 简单循环
       4. `do {} for(true)`
       5. `continue`
    2. `if(expr)  else`
    3. `case(expr) {}`, 分支执行，`=>`分支为松散比较，`==>`分支为严格比较
       ```php
       $a=  1;
       case ($a) {
          ==> '1' :
            echo '不会执行';
          ==> 1 :
            echo '会执行';
          default: echo '默认执行';
       }
       case ($a) {
         => '1':
            echo '会执行';
            break;
         => 1 :
            echo '不会执行';
         default: '';
       }
       ```
    4. `goto`
    5. `return`
    6. `break`
    7. `use`， 后面为字符串时表示包含的文件名，为标识符时为类名或函数名，标识符号导入仅可在顶级代码域和类成员声明级别中使用
         ```php
         use 'index.ap' //包含必须文件
         use ?'include.ap' //包含非必须文件
         use NS.ClassA; //导入类
         ```
 10. 修饰声明
     1. 声明前使用`@`开头修饰声明
     2. 函数、类、类方法、类属性
     3. 块修饰声明相当于定义一个修饰函数
     4. 仅名称，则表示指向一个已定义类或函数
     5. 预定义修饰注解
        1. `@deprecated(version)` 类、类方法、类函数被标记为弃用，它们被调用时出现`Notice`信息，参数为被标记版本，参数不是必须的
            ```php
            @deprecated('1.1') //1.1版本被标记为弃用
            @deprecated('libc-1.1') //libc-1.1 版本被标记为弃用
            @deprecated //已被标记为弃用
            ```
        2. `@final` 仅类和类方法可用，类不可被`use`到其他类中，`use`类不允许出现同名方法
        3. `@interface` 类公共方法在编译后可生成接口头文件
        4. `@abstract`类不可被实例化，类方法被`use`到其他类中时，`use`类必须定义该方法
        5. `@readonly`类属性仅在类初始化时可被修改
        6. `@set {}` 设置属性,参数`$name`、`$value`
        7. `@get {}` 获取属性，参数`$name`
        8. `@type {}` 类型转换规则，参数`$name`,必须返回`$name`值类型
        9. `@string {}` 转换成字符串，无参数, 必须返回字符串
        10. `@int {}`转换成整数，无参数
        11. `@array {}` 转换成数组，无参数
        12. `@bool {}`转换成布尔，无参数
        13. `@float {}`转换成浮点，无参数
        14. `@serialize{}`序列化，无参数
        15. `@free {}`释放对象资源数据，对象被删除时被调用
        16. `@clone{}`复制对象，无参数
        17. `@dump{}`debug打印对象时被调用
        18. `@call{}`类方法不可访问时调用，参数`$name`，`$value`
        19. `@static{}`静态方法不可访问时调用，参数`$name`，`$value`
        20. `@isset{}`不可访问类属性是否存在，参数`$name`
        21. `@unset{}`删除不可访问类属性，参数`$name`
        22. `@unserialize{}`反序列化为对象时被调用
        23. `@invoke{}`对象被当成函数调用时触发,参数`$args`
        24. `@export{}`导出类时调用,参数`$value`