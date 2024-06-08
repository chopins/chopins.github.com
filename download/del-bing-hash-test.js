// ==UserScript==
// @name         del-bing-hash-text
// @namespace    https://page.toknot.com
// @version      2024-06-08
// @description  remove bing search top result url hash text
// @match        https://cn.bing.com/search?q=*
// @author       Xiao Chopin
// @updateURL    https://toknot.com/download/del-bing-hash-text.js
// @downloadURL https://toknot.com/download/del-bing-hash-text.js
// @run-at       document-end
// @grant        none
// ==/UserScript==

(function() {
    'use strict';
    setTimeout(() => {
    let topa = document.querySelector('.b_top a');
    topa.href = topa.href.split('#')[0];
    }, 1000);
})();