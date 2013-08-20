// ==UserScript==
// @name        AOL reader font color
// @namespace   http://reader.aol.com/
// @description AOL reader font color
// @include     http://reader.aol.com/*
// @version     1
// @grant       none
// ==/UserScript==

var style = document.createElement('style');
style.type = 'text/css';
style.innerHTML = '.article-item-list .title {color:#000000;} h2.article-title{font-size:15px;} .cat-nav-list .count{font-color:#333333;} .icon-right {height: 25px;width: 25px;line-height: 25px;text-align: center;} .cat-nav-list .expand-cat{left: 5px;top: 0px;}';
document.getElementsByTagName('head')[0].appendChild(style);
var app = document.getElementById('app');
app.addEventListener('click',function(e) {
var clickElement = e.target;
var feed = document.getElementById('feed-content');
if(!feed) return;

if(clickElement.compareDocumentPosition(feed) != 10) {
    return;
}
if(clickElement.parentNode) {
var header = clickElement.parentNode;
    if(clickElement.tagName == 'H2' &&header.tagName == 'HEADER' && header.className == 'article-header clearfix') {
        var span = header.getElementsByTagName('div')[0].getElementsByTagName('span');
        span[span.length-1].click();
    }
}
}, false)
