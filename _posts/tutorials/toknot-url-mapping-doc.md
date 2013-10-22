---
layout: blog_contents
title: Toknot URL映射文档
categories: tutorials
---

###Toknot URL映射文档


PATH模式是指类似下列的URL的请求:

	http://localhost/User/Login

QUERY模式类似下面：

	http://localhost/index.php?c=User.Login

如果应用程序目录为：`/home/MyApp`,那么上面的两种URL将会被映射到`/home/MyApp/Controller/User/Login.php`文件定义的控制器`\MyApp\Controller\User\Login`类，这个类各个部分说明如下：

* `MyApp` 是应用的根命名空间
* `Controller`是控制器的根命名空间
* `User`是一个控制器的分类命名空间
* `Login`是该控制器的类名

对于HTTP请求有各种模式（方法），比如通常使用的GET和POST模式，Toknot的路由器将会把不同请求模式映射到控制器的与请求模式同名类方法上，例如：

	GET http://localhost/User/Login
	GET http://localhost/index.php?c=User.Login

上面的请求将会被映射到控制器`\MyApp\Controller\User\Login::GET()`上
同理：

	POST http://localhost/User/Login
	POST http://localhost/index.php?c=User.Login

将会被映射到控制器`\MyApp\Controller\User\Login::POST()`上
特殊情况的是当在名命令行下运行一个控制器时执行的是`CLI()`方法，命令行下运行通常可以直接如下面所示:

	cd /home/MyApp/WebRoot
	php index.php /User/Login

上面是默认的形式，就是当PHP的`$_SERVER`超全局变量存在`argv`和`argc`时可用，否则需要修改`/home/MyApp/WebRoot/index.php`文件，将
```php
$app = new Application;
```
变成
```php
$app = new Application($argv,$argc);
```
也就是在实例化[Toknot\Control\Application](http://toknot.com/toknot/class-Toknot.Control.Application.html)是传递`$argv`和`$argc`参数

目前，对于URL映射是 __不区分大小写__ 的, 也就是说下的几种URL将会访问相同的控制器

	http://localhost/User/Login
	http://localhost/USER/loGiN
	http://localhost/user/login

URL映射模式又Toknot默认路由器控制，需要在入口文件`/home/MyApp/WebRoot/index.php`中设置,[Toknot\Control\Application::setRouterArgs()](http://toknot.com/toknot/class-Toknot.Control.Application.html)提供了设置接口, 具体参数取决于路由器的[Toknot\Control\RouterInterface::runtimeArgs()](http://toknot.com/toknot/class-Toknot.Control.RouterInterface.html)实现

Toknot默认路由器参数如下：

1. `$mode` 路由模式，可选值为 [Toknot\Control\Router::ROUTER_PATH](http://toknot.com/toknot/class-Toknot.Control.Router.html)和[Toknot\Control\Router::ROUTER_GET_QUERY](http://toknot.com/toknot/class-Toknot.Control.Router.html)

2. `$routeDepth` URL映射控制器的最大层数，这个值如果为0，映射模式将变成从顶级开始，最先找到模式，例如：控制器文件夹下存在一个`/home/MyApp/Controller/User/Login.php`文件，并且同时存在`/home/MyApp/Controller/User.php`，那么在最先找到模式下，`http://localhost/User/Login`将只会被映射到`/home/MyApp/Controller/User.php`，后面的`Login`将会被忽略，而当作参数所以**不要在相同文件夹创建同名的文件夹和PHP文件**

3. `$notFound` 定义一个Not Found控制器，这个控制器类似WEB Server的404文件，此控制器当URL指向控制器不存在时被调用，注意 __开发模式下（唯一常量`DEVELOPMENT`为`true`），不会调用此控制器__ ,这个参数只应当是相对于应用的根命名空间，例如值为`Block\NotFoundController`时被调用的将会是`\MyApp\Block\NotFoundController`

4. `$methodNotAllowed` 类似于`$notFound`，这个参数是设置405状态是调用的类，就是当控制器不存在一个HTTP请求方法对应的方法时，这个类的同名方法会被调用， __开发模式下（唯一常量`DEVELOPMENT`为`true`），不会调用此控制器__

上面提到了HTTP请求的URL路径中，没有被用的到的部分会被忽略，但是在应用内还是能够被访问到，只不过是按`/`分割成了参数，你可以通过[Toknot\Control\FMAI::getParam($index)](http://toknot.com/toknot/class-Toknot.Control.FMAI.html)来获取，该方法的参数只有一个：$index 参数在分割URL后的索引位置，当然是不包括已经作为映射控制器的部分，例如：

	http://localhost/User/Home/oneuser/note

假设`$routeDepth`被设置为0或者被设置为2,那么URL中的参数在`/home/MyApp/Controller/User/Home.php`将使用如下方式来获取

```php
$FMAI = FMAI::getInstance();

echo $FMAI->getParam(0);//将会输出oneuser
echo $FMAI->getParam(1);//将会输出note
```
而对于GET或者POST参数，仍然按PHP原来的方式访问，Toknot 对其没有作任何处理
对于使用GET_QUERY模式，上面的参数访问方法仍然有效
