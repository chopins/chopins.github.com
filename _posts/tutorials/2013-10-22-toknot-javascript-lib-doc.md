---
layout: blog_contents
title: Toknot javascript 库文档
categories: tutorials
---

##Toknot javascript 库文档
本文档为 Toknot/Admin/Static/js/toknot.js 的类参考

###1.扩展的 String 对象的函数

* `String.isEmail()`         是否是email,是email返回true,否则false
* `String.isCNMoblie()`   是否是中国手机号码,是返回true,否则false
* `String.trim()`            除去字符串首尾空格
* `String.strpos(needle, offset)` 查找字符串中指定字符串,并且可以设置开始处
* `String.ucword()`        将单词首字符转换为大写
* `String.isword()`        字符串是否由字母数字组成
* `String.ucfirst()`        将字符串首字转为大写

###2.扩展的兼容对象

* Node
* console
* navigator.IE    是否是IE, 是为true, 否则false

###3.基类 TK 包含下列属性

* `TK.doc`  等于 `window.document`
* `TK.bodyNode` 等于 `window.document.body`
* `TK.ugent` 用户代理信息
* `TK.FIREFOX`  如果用户代理是 Firefox 为 true, 否则 false
* `TK.WEBKIT`   如果用户代理是webkit 为true
* `TK.IEV`    IE版本,大约判断
* `TK.Ajax`     Ajax 相关对象 * `TK.version`  TK 版本
* `TK.inputType`  input 类型对象,该对象有如下属性:
    * INPUT_TEXT: 1, 普通文本框
    * INPUT_PASSWORD: 2, 密码输入框
    * INPUT_CHECKBOX: 3, 多选框
    * INPUT_RADIO: 4, 单选框
    * INPUT_TEXTAREA: 5, 大块文本输入框
    * INPUT_BUTTON: 6, 普通按钮
    * INPUT_SUBMIT: 7, 提交表单按钮
    * INPUT_IMAGE: 8, 图片按钮
    * INPUT_SELECT: 9, 列表选择框

###4.基类 TK 包含事件相关的方法

* `TK.keyDown()` 键盘按下事件,该方法将返回一个对象,包含下面的方法, 参数function为事件触发的函数名

    * `TK.keyDown().esc(function)`   ESC键
    * `TK.keyDown().tab(function)`     TAB
    * `TK.keyDown().enter(function)`    Enter
    * `TK.keyDown().space(function)`    Space键
    * `TK.keyDown().backspace(function)` Backspace
    * `TK.keyDown().up(function)`        方向键 上
    * `TK.keyDown().down(function)`        方向键 下
    * `TK.keyDown().left(function)`        方向键 左
    * `TK.keyDown().right(function)`    方向键 右
    * `TK.keyDown().any(function)`     任意键盘都会触发


* `TK.keyUp()`     键盘弹起事件,与`TK.keyDown()`类似返回类似对象,包含的方法名相同

* `TK.delKeyListener(key,type)` 删除键盘事件, key为按键对应的code, 使用any时使用any 字符串

* `TK.mouseout(function, elementObject)`  鼠标移出 elementObject 触发 function,多次添加不会被覆盖,会返回该事件函数索引ID

* `TK.mouseover(function, elementObject)` 鼠标移入 elementObject 触发 function,多次添加不会被覆盖,会返回该事件函数索引ID

* `TK.mousedown()`    鼠标按下事件,该方法返回一个对象,该对象包含下面的方法,参数function为事件触发的函数名,多次添加不会被覆盖

    * `TK.mousedown().left(function)`   鼠标左键,会返回该事件函数索引ID
    * `TK.mousedown().right(function)`     鼠标右键,会返回该事件函数索引ID
    * `TK.mousedown().middle(function)`    鼠标中键,会返回该事件函数索引ID
    * `TK.mousedown().any(function)`    鼠标任何按键,会返回该事件函数索引ID


* `TK.mouseup()` 鼠标弹起事件,与`TK.mousedown()`类似返回类似对象,包含的方法名相同,多次添加不会被覆盖,会返回该事件函数索引ID

* `TK.delMouseEventFunction(type,idx,button)` 删除注册的时间函数,
    * `type` 为事件类型名:`mouseout,mouseover,mousedown,mouseup`,
    * `idx` 为注册函数索引ID,
    * `button` 为当使用按键事件时的按键名字:`left,right,middle,any`


* `TK.getEventNode(e)` 获取当前触发事件所在元素对象

* `TK.getFocusNode()` 获取当前焦点元素对象

* `TK.delDefultEvent(e)` 删除事件默认行为

* `TK.mousePos(e)` 获取当前鼠标事件时，鼠标所在坐标, 返回类似 `{x : 0, y: 0}`


###5.基本类 TK 包含的UI类方法
* `TK.createNode(tagname)` 创建一个节点, 返回一个扩展后的 Element 对象
* `TK.jsPath(idx)`    返回指定顺序的JS文件路径, 如果没有给定idx,将返回最后一个JS文件路径
* `TK.require(file,bodyEnd)` 与`TK.loadJSFile(file,bodyEnd)` 加载一个JS文件
* `TK.ready(function)` 页面加载完成后运行 function, function不会覆盖已经存在的
* `TK.getURIHash()`  返回页面URL中的hash值
* `TK.scrollOffset()` 返回当前滚动条坐标对象,对象类似 `{x:0,y:0}`
* `TK.carousel(data, obj, type,eff, cls, waitTime)` 创建一个幻灯片控件,返回控件元素对象
    * `data`    JSON    轮换元素数据,类似如下

                {'label' : string  该轮换项标签
                 'link'  : string  链接URL
                 'img'   : string  轮换项图片地址
                }

    * `obj` 本控件摆放位置元素
    * `type` 1为数字列表点击切换,2前进后退切换,3,文字切换,4缩略图列表点击切换
    * `eff` 轮换效果, 1为渐变轮换,2滑动切换
    * `cls` 控件内元素样式名前缀, 实际元素会加上以下名字:
        * CarouselMainBox : 控件样式
        * CarouselListDiv : 展示元素清单样式
        * CarouselPreDiv  : 上一个按钮样式
        * CarouselNextDiv : 下一个按钮样式
        * CarouselCurrentSelect : 当前选中元素指示样式

    * `waitTime` 滚动间隔时间
* `TK.msgBox(msg, cls, zIndex, waitTime)` 创建一个简单的具有时效性的信息控件,返回控件所在DIV对象
    * `msg` 信息内容
    * `cls` 信息提示控件样式
    * `zIndex` 信息提示控件 z-index 值
    * `waitTime` 默认3000ms,信息提示控件自动超时隐藏毫秒时间
* `TK.alertBox(tit, msg, func, cls, cover, zIndex)` 创建一个拥有确定按钮的信息提示控件
    * `tit` 控件标题信息
    * `msg` 控件提示信息
    * `func` 确定按钮后执行的操作

             回调函数原型样式:
             callbackFunciton(event, button);
                event  : EventObject   点击事件
                button : boolean       等于true

    * `cls` 控件内元素样式名前缀,内部实际会跟随以下名字:
        * TitleDiv  : 标题栏样式
        * MainDiv   : 控件中间主题部分样式
        * ButtonDiv : 按钮所在元素样式

    * `cover` 是否显示cover层,默认不显示，true为显示
    * `zIndex` 控件 z-index 值,如果没有设置将为当前页面最上面

* `TK.confirmBox(tit, msg, func, cls, cover, zIndex)`  创建一个拥有确定与取消按钮的信息提示控件
    * 参数与`TK.alertBox()`相同, 只是func 的接收参数中button值在点击取消时为false,确定为true
* `TK.inputBox(tit, msg, inputList, buttonList, cls, cover, zIndex)`创建一个具有表单功能的控件
    * `tit` 控件标题信息
    * `msg`  默认提示信息
    * `inputList` 表单内input元素清单,select元素将会使用selectDiv控件替代,值为一个数组,下面为其中一个input元素:

                    {'label' : string   input元素标签
                     'type'  : string   input元素类型
                     'name'  : string   input元素name值
                     'value' : string   input元素默认值
                     'cls'   : string   input元素直接使用样式,
                                          内部实际会跟随以下名字：
                                        ItemDiv   : input元素的上一级Div样式
                                        ItemLabel : input元素的label元素样式
                    }

    * `buttonList` 按钮清单，这里的按钮不是button类型input标签,值为数组,下面为其中一个元素组成:

                 {'label' : string   按钮显示名字,innerHTML值
                    'value' : string   按钮值,attributes属性
                    'cls'   : string   按钮样式
                    'call'  : string/function   按钮点击回调函数名
                    'url'   : string   表单提交URL
                 }

    * `cls`  控件内元素样式名前缀,内部实际会跟随以下名字:
        * TitleDiv  : 标题栏样式
        * MainDiv   : 控件中间主题部分样式
        * ButtonDiv : 按钮所在元素样式
        * MsgDiv    : 提示信息样式名
        * CloseDiv  : 关闭按钮样式名
    * `cover`  是否显示cover层，true为显示，默认不显示
    * `zIndex`  控件z-index 值，默认在页面最上面
    * 返回一个扩展后元素对象,用下列方法:
        * `box.iHide()`       销毁控件
        * `box.msg(msg, cls, visibility)`     显示提示信息
            * msg: string   提示信息内容
            * cls: string   提示信息样式名
            * visibility : boolean  隐藏后是否保留提示信息位置
        * `box.submitInput(url, func, validFunc)` 提交表单
            * url   : string    提交表单URL

            * func  : function  表单提交返回回调函数,回调函数原型样式：

                    callbackFunciton(returnData)
                    returnData : JSON  Ajax返回数据

            * validFunc : function  表单数据检测回调函数

                    回调函数原型样式：
                    callbackFunciton(formData, box)
                    returnData : JSON 表单数据
                                           KEY值为input name值
                    box        : ELEMENT_NODE 控件对象
                    return boolean 返回false将阻止表单提交,true提交表单


* `TK.selectDiv(optionList, name, func, def, cls)` 创建一个下来列表控件
    * `optionList` 列表数据,其中一个元素类似下面:

            单个选项所需要的数据:
            {'label'    : string  选项显示名
             'value'    : mixed   选项值
            'disabled' : boolean true时该项不可选，默认可选
             }

    * `name`  控件在表单内的name值
    * `func`  更换选择项后回调函数,可选,回调函数原型样式：callbackFunciton(value),value为选择的值
    * `def`   默认项数据, 数据样式与optionList单项一样,可选
    * `cls`  控件内元素样式名前缀,内部实际会跟随以下名字:
         * DefDiv    : 当前显示项外层样式
         * DefOption : 当前显示项样式
         * SelectOptionDiv : 下拉列表层样式
         * Selected  : 下拉列表中选中项样式
         * OptionDisable : 不可选项样式
         * OptionMouseOver : 鼠标移动到选项上时样式

* `TK.pageShowSize()` 获取当前浏览器可视区域尺寸, 返回类似 `{h: 0, y: 0}`

* `TK.getOpacityStr(num)` 获取兼容性透明度设置样式

* `TK.showPageCover()`  显示一个遮照层

* `TK.hiddenPageCover()`  隐藏遮照层

* `TK.drawRect(x, y, w, h, color)`  画一个矩形

* `TK.drawLineTrends(style, initData, padding)` 创建基于canvas 元素的趋势图表

###6.其他
* `TK.setCookie(cn, v, ex)` 设置cookie值

* `TK.getCookie(cn)`    获取一个cookie 值

* `TK.getFormInputData(frm, disable_no_name)` 获取指定元素对象所包含的所有input或相关表单数据

* `TK.submitForm(ele, callFunc, validFunc)` 自动提交表单,本方法只能提交form标签表单

* `TK.time()` 获取当前时间戳,1970-1-1 00:00:00 到当前的秒数

* `TK.repeat(str, n)` 重复一个字符串

* `TK.preZero(num, bit)` 添加前导零

* `TK.date(time, cache)`  获取 YYYY-mm-dd HH:ii:ss 格式时间, 如果设置time,将获取该时间的日期,如果cache为true,每次调用将只更新一秒的时间

* `TK.rand()` 获取一个10位数的随机数

* `TK.localDate()` 获取 YYYY-mm-dd HH:ii:ss 格式本地时间

* `TK.$(element)` 扩展Element对象方法, 如果全局范围内没有定义 windows.$, 将会创建 window.$ 并指向本方法,详细见 [第7大类](#7-tk-方法)

###7. TK.$(element) 和 $(element) 方法
   element 参数形式如下:

* 以`.`开头是样式名
* 以`@`开头是标签名
* 以`%`开头是标签name属性名
* 不以上面的开头的字符串是元素ID名
* 也可以是Element对象
* 如果是数组,数组的每一个元素必须是Element对象

本方法将返回一个扩展后的Element对象,该对象除拥有DOM默认定义的Element对象外,还拥有下面的属性或方法:

* 属性
    * `TK.$(element).inputType`    如果元素是input标签,将有此属性,值根据input标签type属性决定,等于`TK.inputType`属性定义
    * `TK.$(element).tag` 小写的标签名

* 方法
    * `TK.$(element).getIframeBody()` 获取iframe标签内嵌HTML页面的Body对象,element为iframe标签的相关值
    * `TK.$(element).getPos()` 获取元素的坐标信息,返回一个对象,如下

            {
            x:0,  //横向坐标
            x:0,    //纵向坐标
            w:0,  //宽度
            h:0,  //高度
            }

    * `TK.$(element).copyNode(deep)` 复制一个标签, deep 为是否递归复制

    * `TK.$(element).getNodeByCls(cls)` 根据样式名找子元素,返回一个数组

    * `TK.$(element).getChildNodeByAttr(attr,value)` 根据指定属性及属性值找子元素,返回一个数组

    * `TK.$(element).getParentNodeByAttr(attr,value)`根据指定属性及属性值找上级元素,最多查找到body
    * `TK.$(element).getFirstNode()` 获取第一个 ELEMENT_NODE 子元素,忽略文本或注释等节点

    * `TK.$(element).getLastNode()`    获取最后一个 ELEMENT_NODE 子元素

    * `TK.$(element).isNodeChild(parentNode)` 检测当前元素是否是参数指定元素的子元素,是返回true,否则false

    * `TK.$(element).unshiftChild(new_node)` 在第一个子元素前插入一个新节点

    * `TK.$(element).getParentNodeByTag(tagName)` 根据标签名查找上级元素,最多查找到body

    * `TK.$(element).getSubNodeByTag(tagName)` 根据标签名查找子元素,返回一个数组

    * `TK.$(element).hasClass(cls)` 检查是否有指定样式名

    * `TK.$(element).removeClass(cls)` 移除指定样式名

    * `TK.$(element).replaceClass(oldCls, newCls)` 替换指定样式为新样式

    * `TK.$(element).addClass(cls)` 添加一个样式名

    * `TK.$(element).setClass(cls)` 设置样式名，会替换原有所有的样式

    * `TK.$(element).setCss(value)` 设置style属性值，会替换原有所有style值

    * `TK.$(element).getStyle(ns)` 获取元素style属性中指定名字的样式值

    * `TK.$(element).convStyleName(ns)` 将style中的样式名转换为JS style 对象的属性名格式

    * `TK.$(element).setStyle(ns, value)` 设置一个style样式值

    * `TK.$(element).setOnTop()` 绝对定位时，让元素位于顶部

    * `TK.$(element).setZIndex(idx)` 设置元素z-index值

    * `TK.$(element).nextNode()` 元素下一个 ELEMENT_NODE 元素节点

    * `TK.$(element).previousNode()` 元素上一个 ELEMENT_NODE 元素节点

    * `TK.$(element).delListener(e, call_action)` 删除指定事件

    * `TK.$(element).addListener(e, call_action)` 添加事件,本方法在JS原生相关函数上增加了以下事件支持:
        * scroll    页面滚动事件
        * resize    页面尺寸改变时间
        * load        页面加载事件
        * 对于注册的匿名函数将会拥有`eventId`属性与`clear()`方法，通过在匿名函数内部调用`this.clear()`来清除本次注册事件

    * `TK.$(element).getChilds()` 获取所有子节点,只会获取 ELEMENT_NODE 节点,并且递归获取所有子节点的子节点

    * `TK.$(element).submitForm(func, enter)` 让节点具备提交 form 表单的功能, 参数 func 为表单 Ajax 提交后的回调函数,具体见`TK.submitForm()`, enter参赛为是否激活 Enter 按键提交

    * `TK.$(element).toCenter(eff, spec)` 让元素对象居中,eff为1时平滑移动,否则瞬间移动到指定位置,spec为true标识是否在页面滚动时居中

    * `TK.$(element).mousePop(e)` 元素跟随鼠标,此方法需要用作鼠标事件触发时

    * `TK.$(element).byNodePop(byObj, direct)` 元素跟随指定对象, direct可以为以下值:
        * 1 位于下侧靠左
        * 2 位于下侧靠右
        * 3 左侧居上
        * 4 右侧居内
        * 默认位于右侧居上

    * `TK.$(element).maxImg(cls, bsrc,altShow, altClose)` 放大图片,点击本元素后会方法指定图片,点击放大后的图片会关闭放大效果,参数说明:
        * cls 为图片放大后的图片标签的样式
        * bsrc 为需要方法的图标标签对象,如果为false,将为当前元素
        * altShow 未当前元素的 title 提示信息,比如为点击放大
        * altClose 未放大后的图片 title 提示信息,点击关闭

    * `TK.$(element).toPos(x, y)` 将元素移动到指定坐标

    * `TK.$(element).move(down, spec)` 元素可移动，down为鼠标按下该元素时可移动,spec为只能在该元素范围内移动, 当调用本函数后,即激活了元素的可移动属性

    * `TK.$(element).maxsize(spec, part, type)` 双击时放大对象，spec为只能放大到该元素范围，part为点击对象,type为true时为单击，否则为双击

    * `TK.$(element).resize(sens, spec)` 使元素可修改尺寸,spec为只能在该元素范围内，sens为鼠标灵敏度

    * `TK.$(element).close(spec)` 隐藏元素，spec为点击该元素隐藏

    * `TK.$(element).hide(visibility)` 隐藏元素，visibility为隐藏后是否保留位置

    * `TK.$(element).show(visibility)` 显示元素,visibility为隐藏后是否保留位置

    * `TK.$(element).destroy()` 销毁本元素

    * `TK.$(element).getCursorOffset()` 获取当前输入区，光标偏移量

    * `TK.$(element).setCursorOffset(offset, start)` 设置光标偏移量


###8.TK.Ajax 对象
* 属性
    * `TK.Ajax.dataType` 设置返回的数据类型,值可能为text, json, xml
    * `TK.Ajax.charset`     设置返回的数据编码
    * `TK.Ajax.waitTime` 超时时间,单位:毫秒
    * `TK.Ajax.messageNode` 为Ajax显示状态信息的节点
    * `TK.Ajax.messageList` 为Ajax各个状态时的信息, 格式如下

            {
            start: '', //开始时
            complete: '', //完成时
            still: '', //持续进行时
            current: ''
            }

* 方法
    * `TK.Ajax.get(url, callFunc)` GET方法请求
    * `TK.Ajax.post(url, data, callFunc)` POST方法请求
    * `TK.Ajax.head(url,callFunc)` HEAD方法请求
    * `TK.Ajax.put(url, data, callFunc)` PUT 方法请求
    * `TK.Ajax.options(url ,callFunc)`OPTIONS 方法请求
    * `TK.Ajax.del(url,callFunc)`  DELETE方法请求
    * `TK.Ajax.trace(url,callFunc)` TRACE方法请求
    * `TK.Ajax.file(formObj, callFunc)` 上传文件,formObj 为 ELEMENT_NODE 上传文件表单
    * `TK.Ajax.jsonp(url,callFunc)`  JSONP请求, 服务器端只需要输出调用`TK.Ajax.callback(reData)` 的 js 即可