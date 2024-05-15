// ==UserScript==
// @name         get-selection-range
// @namespace    http://tampermonkey.net/
// @version      2024-05-07
// @description  try to take over the world!
// @author       You
// @match        http*://*/*
// @run-at       document-end
// @grant        none
// ==/UserScript==


/**
* {
   -moz-user-select:text;
   user-select:text!important;
}
 */
(function() {
    'use strict';
 let contents = '';
document.addEventListener("mousedown", (event) => {
    if(event.button != 2) {
        return;
    }
    if(contents.length > 0 && window.navigator.clipboard) {
        window.navigator.clipboard.writeText(contents).then(()=>{});
    }
});
document.addEventListener("selectionchange", (event) => {
    var sel = document.getSelection();
    if(sel.rangeCount > 0) {
     contents = sel.getRangeAt(0).cloneContents().textContent;
    }
});

})();
