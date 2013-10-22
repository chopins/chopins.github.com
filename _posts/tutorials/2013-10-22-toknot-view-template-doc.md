---
layout: blog_contents
title: Toknot 模板语法文档
categories: tutorials
---

###Toknot 模板语法文档

####配置文件, 默认的[Toknot\View\Renderer](http://toknot.com/toknot/class-Toknot.View.Renderer.html)需要如下配置
```ini
;模板文件夹路径，相对路径是相对于应用的根目录
templateFileScanPath = View

;编译后的文件保存文件夹，相对路径是相对于应用的根目录
templateCompileFileSavePath = Data/View/Compile

;模板文件扩展名
templateFileExtensionName = html

;开启HTML静态缓存时文件保存目录，相对路径是相对于应用的根目录
htmlStaticCachePath = Data/View/HTML

;开启数据缓存时数据保存目录，相对路径是相对于应用的根目录
dataCachePath = Data/View/Data

;缓存有效时间，单位秒
defaultPrintCacheThreshold = 2
```

####在控制器的方法中调用模板和申明模板变量如下

```php
class index {
   protected static $FMAI = null;

    //控制器在被调用时会接收到 FMAI 对象实例
   public function __construct(FMAI $FMAI) {
      self::$FMAI = $FMAI;
    }

   public function GET() {

        //定义一个模板变量
      self::$FMAI->D->newVarName = 'newVarValue';
        //或者如下设置一个模板变量
      slef::$FMAI->setViewVar('newVarName','newVarValue');

        //调用 /your_app/View/index.htm 模板文件
      self::$FMAI->display('index');

        //调用 /your_app/View/Index/index.htm 模板文件
      self::$FMAI->display('Index/index');
    }

}
```

####模板表达式标记:
* 起始标记 `{`
* 结束标记 `}`


####Toknot支持下列流程控制结构:

* `foreach`
    * 开始标记`{foreach expression}`, 内部表达式expression与PHP foreach 结构一样
    * 结束标记`{/foreach}`

* `if elseif else`
    * 开始标记`{if expression}`，表达式expression与PHP if结构一样,例如：`{if $a=$b}`,`{if is_nan($a)}`
    * 控制结构`{elseif expression}` 或者 `{else}`
    * 结束标记`{/if}`
* `inc` 包含一个模板，参数与[Toknot\Control\FMAI::display()](http://toknot.com/toknot/source-class-Toknot.Control.FMAI.html#319-329)方法一样,不包含扩展名
    * 单一标记`{inc filename}`, `{inc dir/filename}`

* `func` 调用一个函数，这个函数必须是已经定义了的PHP函数，函数的参数必须是已经定义了的模板变量，函数结果会被输出(echo)
    * 单一标记`{func function($arg)}`

* `set` 定义一个模板变量
    * 单一标记`{set $var='value'}`

####输出一个变量的值，类似下面:

    {$value}

__注意模板中的字符串变量其实是 [Toknot\Di\StringObject](http://toknot.com/toknot/class-Toknot.Di.StringObject.html) 对象实例__

####变量访问数组类型模板变量:
* 字符串类型key: `$array.key`
* 数字类型key: `$array[key]`
* 字符串类型key，key为变量表示:`$array.$key`

__注意模板中的数组变量其实是 [Toknot\Di\ArrayObject](http://toknot.com/toknot/class-Toknot.Di.ArrayObject.html) 对象实例__

####一个完整的模板例子如下:

```html
{inc header}
<ul class="nav-tree-ul" id="left-nav-tree">
{foreach $navList as $key=>$nav}
<li class="nav-tree-ul-li">
    <div class="icon-down-open nav-cat"
        {if $nav.action}action="{$nav.action}"{/if}
        {if !$nav.hassub}hassub="no"{/if}>{$nav.name}</div>
    {if isset($nav.sub)}
    <ul class="sub-nav-tree">
        {foreach $nav.sub as $sub}
        <li class="sub-nav-tree-li" action="{$sub[0]}">{$sub[1]}</li>
        {/foreach}
    </ul>
    {/if}
</li>
{/foreach}
<ul>
```
