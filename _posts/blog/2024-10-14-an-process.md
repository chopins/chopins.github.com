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
   6. 导入多个类时，同名方法将不会被导入。除非通过声明别名的方法来让方法名不相同。
   7. 普通方法访问: `$my.method()`
   8. 静态方法访问：`my.methid()`
   9. 未被导入的普通方法访问:`NS.MixName($my).method()`
   10. 静态方法访问： `NS.MixName(my).method()`
   11. 使用`mix`来声明一个可用组合
   ```php
   mix MixA {
     public mA()
     {

     }
     public mB()
     {
        
     }
   }
   mix MixB {
      use MixA;//没有任何方法被显示导入
      public mA {

      }
      public mB {
      }
   }
   mix MixC {
      use MixB;
      public mA {
        $this.MixAmA(); //MixB::MixAmA
      }
   }
   mix MixD {
      use MixA;
      use MixB;

   }
   ``` 
4. 函数
5. 异常
6. 注释
7. 表达式
   1. `+, -, !, ~, ++`
   2. `+, -, *, /, %, &, &&, |, ||, ??, ^, <<, >>, ==, !=, >, <, >=, <=`
   3. `=`,`&`,`+=, -=, *=, /=, %=, |=, ^=, <<=, >>=`
   4. `is`,`not`,`ref`
8. 命名空间
9.  语句