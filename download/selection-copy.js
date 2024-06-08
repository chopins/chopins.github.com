// ==UserScript==
// @name         selection-copy
// @namespace    https://page.toknot.com
// @version      2024-06-08
// @description  when click mouse right button, auto copy selection text to clipboard
// @author       Xiao Chopin
// @updateURL    https://toknot.com/download/selection-copy.js
// @downloadURL https://toknot.com/download/selection-copy.js
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
    } else {
        contents = '';
    }
});

})();