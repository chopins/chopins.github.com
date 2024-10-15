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
   7. `array`   任意可混合类型数组
   8. `object`  对象
   9. `void`    无返回值类型
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
   11. 使用`mix`来声明一个可用组合
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
      NSB.MixD.my.NSA.MixA.msD();//待定

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
6. 注释
7. 表达式
   1. `$my`,`my` 为对象与类引用
   2. `.`为类与对象方法和属性访问操作符，`new`类实例化操作符号
   3. `+, -, !, ~, ++`
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