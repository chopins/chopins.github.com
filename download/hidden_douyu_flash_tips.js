// ==UserScript==
// @name     Douyu
// @version  1
// @grant    none
// @include  *://*.douyu.com/*
// @run-at document-end
// ==/UserScript==

function hiddenFlashTips() {
  setTimeout(function() {
    var tips = document.querySelectorAll('.flash-version-tips');
    if(tips.length > 0) {
      tips[0].style.display = 'none';
    } else {
        hiddenFlashTips();
    }
  },4000);
}
hiddenFlashTips();
