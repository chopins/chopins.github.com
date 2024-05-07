// ==UserScript==
// @name         get-selection-range
// @namespace    http://tampermonkey.net/
// @version      2024-05-07
// @description  try to take over the world!
// @author       You
// @match        http*://*/*
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
 let contents = null;
document.addEventListener("mouseup", (event) => {
    if(contents.length > 0) {
        window.navigator.clipboard.writeText(contents).then(()=>{});
    }
});
document.addEventListener("selectionchange", (event) => {
    contents = document.getSelection().getRangeAt(0).cloneContents().textContent;
});

})();