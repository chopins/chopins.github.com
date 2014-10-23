---
layout: blog_contents
title: Toknot PHP 框架实现了一个类对象的伪多重继承
categories: blog
---

这不是真正的多重继承。该伪多重继承是建立在PHP的`__call`魔术方法上。继承的方式参考了Python的语法规则。当实例化一个类时，向类传递一个对象实例，那么你可以在这个实例对象中使用$this来访问你传递的对象。

实现原理位于`Toknot\Di\Object`中。

为了在对象实例化时能处理传入的对象信息，并用于在后期访问时能够调用，Toknot框架在`Toknot\Di\Object`类中定义了`final`的构成函数，这样防止了子对象的覆盖。子对象的构成函数改成`__init`方法。`Toknot\Di\Object`类的构造函数会将所有传入的对象保存到一个`SplObjectStorage`对象中。然后当被调用类访问`__call`魔术方法时，`Toknot\Di\Object`类中定义的标识了`final`的`__call`魔术方法将会完成传入类方法的调用。

下面是演示代码：

```php

class A extends Toknot\Di\Object {
    
}

class B  {
    public $pb = 'testB';
    public function fooB() {
    }
    public function foo_echo() {
        echo 'this B';
    }
}

class C {
    public $pc = 'testC';
    public function fooC() {
    }
    public function foo_echo() {
        echo 'this C';
    }
}

$n = new A(new B, new C);
$n->fooB()
$n->fooC();
echo $n->pb;
echo $n->pc;
$n->foo_echo() // this B，并不会调用C::foo_echo(),只会调用第一个。
```

方法只能调用`public`标识的方法和属性。如例子所示，传入顺序会影响到方法调用，如果具备相同方法时，只会按从左到右的顺序调用最先出现的方法。
继承对象对于被继承对象并不存在从属关系。
