// ==UserScript==
// @name         斗鱼 H5 Mini播放器
// @namespace    http://my.mini.cn/
// @version      0.1
// @description  mini
// @author       space
// @match        https://www.douyu.com/*
// @grant        unsafeWindow
// ==/UserScript==

(function (){
    'use strict';
    function $(selector) {
      console.log(selector);
        function query(selector) {
            return document.querySelector(selector);
        };
      	var obj = {};
        obj.hide = function() {
          	var e = query(selector);
            if(e)e.style.display = 'none';
        };
        obj.css =function(s) {
            var e = query(selector);
            if(e)e.setAttribute('style', s);
        };
        return obj;
    }
    function opendouyu() {
      	if(ismini()) {
          	unsafeWindow.open(unsafeWindow.location.href.split('#')[0],'_blank');
          	unsafeWindow.close();
          	return;
        }
        var option = "width=420,height=230,resizable,scrollbars=yes";
        var url = unsafeWindow.location.href +'#ismini';
      	unsafeWindow.open(url,'mini',option);
        unsafeWindow.open('','_self',option);
      	unsafeWindow.close();
    }
    function createBtn() {
        var btn = document.createElement('div');
        var style = "display:inline-block;width:30px;height:30px;position:fixed;top:150px;z-index:20000;background-color: #fff;opacity: 0.7;cursor: pointer;";
        btn.setAttribute('style',style);
        btn.textContent = 'Open';
        btn.addEventListener('click',opendouyu);
        unsafeWindow.document.body.appendChild(btn);
    }
    function pageFull() {
        $('#header').hide();
      	$('#js-room-video').css('padding:0px;position: fixed;display: block;top:0;left:0;');
        $('.diy-live-room-top').hide();
        $('#js-live-room-normal-right').hide();
        $('.anchor-info-con').hide();
        $('#js-stats-and-actions').hide();
        $('.live-room').css('padding:0px;');
      	$('#mainbody').css('width:auto');
    }
    function init() {
        if(ismini()) {
            pageFull();
        }
        createBtn();
    }
    function ismini() {
        return unsafeWindow.location.hash == '#ismini';
    }
    setTimeout(init, 3000);
})();
