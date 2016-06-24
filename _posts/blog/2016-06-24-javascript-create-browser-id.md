---
layout: blog_contents
title: javascript 创建浏览器唯一ID
categories: blog
---

代码见以下javascript,其中需要引用 md5 的javascript 实现函数

```javascript

function getcrc(str) {
    try {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var txt = str;
        ctx.textBaseline = "top";
        ctx.font = "14px 'Arial'";
        ctx.fillStyle = "#f60";
        ctx.fillRect(125,1,62,20);
        ctx.fillStyle = "#069";
        ctx.fillText(txt, 2, 15);
        ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
        ctx.fillText(txt, 4, 17);
        var b64 = canvas.toDataURL().replace("data:image/png;base64,","");
        var bin = atob(b64);
        var crc = encodeURIComponent(bin.slice(-16,-12)).replace(/%/g,'');
        return crc;
    } catch(e) {
        log(e.message);
        return '';
    }         
}
function getplug() {
    var p = window.navigator.plugins;
    var s = '';
    if(p && p.length > 0) {
        var pl = p.length;
        for(var i=0;i<pl;i++) {
            if(p[i].description) {
                s += p[i].description;
            }
            if(p[i].version) {
                s += p[i].version;
            }
            if(p[i].name) {
                s += p[i].name;
            }
            if(p[i].filename) {
                s += p[i].filename;
            }
        }
    }
    return s;
}
function log(s) {
    var str = document.getElementById('log').innerHTML;
    document.getElementById('log').innerHTML = str + '<br />' + s;
}
var h = window.screen.height+'px';
var w = window.screen.width+'px';
var ah = window.screen.availHeight+'px';
var aw = window.screen.availWidth+'px';
var base = h+w+ah+aw+window.navigator.userAgent;
var crc = getcrc(base);
var plug = getplug();
log('MD5:'+md5(base+crc+plug));
```
