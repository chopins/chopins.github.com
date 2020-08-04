// ==UserScript==
// @name     nlc.pdf.check
// @version  1
// @grant    none
// @include *://mylib.nlc.cn/*
// @run-at document-end
// ==/UserScript==


(function() {
 var s = document.createElement('script');
 s.innerHTML = 'function isAcrobatPluginInstall() { return true;}'
 document.body.appendChild(s);
 console.log('overload')
})();
