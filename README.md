Toknot.com网站页面

systemd 修改默认启动级别
 * 修改为3运行级别
  `ln -sf /lib/systemd/system/multi-user.target /etc/systemd/system/default.target`
 * 修改为5运行级别
  `ln -sf /lib/systemd/system/graphical.target /etc/systemd/system/default.target`

Linux 先切换用户时，需要执行`xhost + ` 命令来允许切换后的用户使用当前用的显示服务（Xorg服务或Xwayland服务），可解决 `can't open display` 错误

Google Translate Greasemonkey Script for Firefox (API is https://translate.google.cn) 划词翻译脚本
[Greasemonkey Script](http://toknot.com/download/google_translate_for_firefox.js)

AOL reader 字体及收卷脚本
[aol-reader-font-color.js](http://toknot.com/download/aol-reader-font-color.js)

PHP 扩展自动编译脚本
[phpcim](http://toknot.com/download/phpicm)

PHP Shell 脚本
[phpsh](http://toknot.com/download/phpsh)

SVN 查询脚本, 根据用户名查询指定日期或版本号以后该用户更新日志
[svn-ext.sh](http://toknot.com/download/svn-ext.sh)

PHP HTML简单爬虫
[php-fetchContent.php](http://toknot.com/download/fetchPage.php)

firefox nightly 版本在浏览器选项中去掉了“禁用或替换上下文菜单”修改功能，
只能在about:config中修改dom.event.contextmenu.enabled项来实现

PHP 中文匹配正则,UTF-8编码 `/[\x{4e00}-\x{9fa5}]+/u`

终端提示字符：
```bash
PS1="\[\e[1m\]\$?\[\e[0m\] \[\e[44m\] \h \[\e[0m\]\[\e[45m\] \A \[\e[m\]\[\e[42m\] \u \[\e[0m\]\[\e[46m\] \w \[\e[0m\] \[\e[1m\]\\$\[\e[0m\] "
```

根据文件innode删除文件:
```bash
ls -il  #查看目录下文件的innode号
find ./ -inum 1234 -delete  #删除innode号为1234的文件
```


firefox 禁止页面 “屏蔽鼠标选择与鼠标导航” 脚本
```javascript
// ==UserScript==
// @name        PageProhibitSelect
// @namespace   disable.page.prohibit.user.select
// @include     http://*/*
// @version     1
// @grant       none
// ==/UserScript==

document.body.onselectstart = true;
var st = document.createElement('style');
st.type = 'text/css';
st.innerHTML = " * {-moz-user-select: text !important;}";
document.getElementsByTagName('HEAD').item(0).appendChild(st);
```

元素属性变更事件注册
```javascript
function addAttributesChangeEvent(node, callback) {
    var config = {attributes: true};
    if(typeof MutationObserver != 'undefined') {
        var mobs = MutationObserver;
    } else if(typeof WebKitMutationObserver != 'undefined') {
        var mobs = WebKitMutationObserver;
    } else if(window.ActiveXObject) {
        return node.attachEvent('onpropertychange', callback);
    } else {
        return undefined;
    }
    var observer = new mobs(function(mutationsList, observer) {
        for(var mutation of mutationsList) {
                if (mutation.type == 'attributes' && node === mutation.target) {
                    callback(mutation.target);
                }
            }
        });
    observer.observe(node, config);
    node.observer = observer;
}

function removeAttributesChangeEvent(node, callback) {
    if(window.ActiveXObject) {
        return node.attachEvent('onpropertychange', callback);
    }
    node.observer.disconnect();
}
```

SourceForge CDN
https://liquidtelecom.dl.sourceforge.net/project/

Github No SNI
nohup /usr/bin/chromium-browser --host-rules="MAP *github.com github.io, MAP *githubusercontent.com ghu.com" --host-resolver-rules="MAP github.io 20.205.243.166, MAP ghu.com 185.199.108.133" --ignore-certificate-errors > /dev/null 2>&1 &


CSS 规则
```css
wfuaa,ujltbq { display:none !important;}
body>div[style*=fixed][style*=opacity]:empty{display:none !important;}
```

### waydroid
通过 `waydroid_script` 安装 `libndk` `libhoudini` 以支持 ARM APK，
对于ARM APK 使用`adb install --abi arm64-v8a [APP-PATH.APK]` 进行安装，以便运行时能兼任arm架构的app
在文件`/usr/lib/waydroid/tools/services/user_manager.py`的第25行添加 `showApp = False`可以禁止`waydroid`在`~/.local/share/applications`下创建应用的桌面快捷方式，修改后代码如下：
```python
showApp = False
    for cat in appInfo["categories"]:
        if cat.strip() == "android.intent.category.LAUNCHER":
            showApp = True
    showApp = False #添加的行
    if not showApp:
        return -1
```