// ==UserScript==
// @name     nlc.pdf.check
// @version  1
// @grant    none
// @include *://mylib.nlc.cn/*
// @run-at document-start
// ==/UserScript==


setTimeout(function() {
 var s = document.createElement('script');
 s.innerHTML = 'function isAcrobatPluginInstall() { return true;}'
 document.getElementsByTagName('head')[0].appendChild(s);
 console.log('overload')
},3000);
