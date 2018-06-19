// ==UserScript==
// @name     txt2bookpage
// @version  1
// @include    file://*.txt#*
// @include    file://*.txt
// @grant    none
// @run-at	 document-end
// ==/UserScript==

(function () {
    console.log('load');
    var style = "body{background-color: #EDE8D5;padding: 0px 150px;color: #333;} a{color:#333;}"
        + ".pb{font-weight: bold;} .active{background-color:#EFD161;}.active a{color: #D35452;}"
        + "ul{padding-left: 0px;height: 90%;overflow-y: auto;display: inline-block;position:fixed;width:150px;"
        + "top:20px;left: 0px;list-style: none;} ul li{padding:3px;width: 130px;}"
        + "p {text-indent: 40px;font-size: 20px;line-height: 38px;letter-spacing: 2px;}"
    var se = document.createElement('style');
    se.textContent = style;
    unsafeWindow.document.head.appendChild(se);

    var c = unsafeWindow.document.getElementsByTagName('pre')[0];

    var html = c.textContent.replace(/\n/g, '</p><p>');

    c.innerHTML = '<p>' + html + '</p>';

    var result = true, p = null, clist = c = '', i = cn = 1, clist = '', top = 0;
    while (result) {
        result = document.evaluate('/html/body/pre/p[' + i + ']', document.body, null, XPathResult.FIRST_ORDERED_NODE_TYPE);
        if (result === null) {
            break;
        }
        p = result.singleNodeValue;
        if (p === null) {
            break;
        }
        c = p.textContent.replace(/^\s*/, '');
        p.textContent = c;
        res = c.match(/^\s*第[一二三四五六七八九十0-9]+[章节回][\s\n]*/);
        p.setAttribute('id', 'c-p-' + i);

        if (res) {
            p.setAttribute('class', 'pb');
            top = p.offsetTop;
            clist += '<li data-top="' + top + '"><a href="#p-' + i + '">' + c + '</a></li>';
            cn++;
        }
        i++;
    }
    var ul = document.createElement('ul');
    ul.innerHTML = clist;
    unsafeWindow.document.body.appendChild(ul);
    var scriptText = function () {
        window.__gmk_nowActive = null;
        window.__gmk_nowParagraph = 1; window.__gmk_preTop = 1;
        function __gmk_getActive() {
            var top = window.scrollY;
            var lis = document.getElementsByTagName('li');
            var ison = 0;
            for (var k = 0; k < lis.length; k++) {
                if (top >= lis[k].getAttribute('data-top')) {
                    ison = k;
                } else if (top < lis[k].getAttribute('data-top')) {
                    break;
                }
            }
            if (window.__gmk_nowActive) {
                window.__gmk_nowActive.className = '';
            }
            lis[ison].className = 'active';
            window.__gmk_nowActive = lis[ison];
            var pnum = window.__gmk_nowParagraph;
            var result = p = null, ptop = 0;
            while (true) {
                result = document.evaluate('/html/body/pre/p[' + pnum + ']', document.body, null, XPathResult.FIRST_ORDERED_NODE_TYPE);
                if (result === null) {
                    break;
                }
                p = result.singleNodeValue;
                if (p === null) {
                    break;
                }
                ptop = p.offsetTop;
                if (window.__gmk_preTop > top) { if (ptop >= top) { window.__gmk_nowParagraph = pnum; } else if (ptop < top) { break; } pnum--; } else {
                    if (ptop <= top) { window.__gmk_nowParagraph = pnum; } else if (ptop > top) { break; }
                    pnum++;
                }
            }
            if (p) { window.location.hash = p.id.substr(2); } window.__gmk_preTop = top;
        }
        window.document.body.onscroll = __gmk_getActive;
        if (window.scrollY > 0) {
            if (window.location.hash) { window.location.hash = '#c-' + window.location.hash.split('#')[1]; }
            __gmk_getActive();
        }
        document.getElementsByTagName('ul')[0].addEventListener('click',function(e) {
            var ele = e.target;
            if(ele.tagName == 'LI') {
                ele = ele.getElementsByTagName('a')[0];
            }
            if(ele.tagName == 'A') {
                window.location.hash = '#c-'+ ele.href.split('#')[1];
            }
        });
    };
    var script = document.createElement('script');
    script.type = "text/javascript";
    script.innerHTML = '(' + scriptText.toString() + ')();';
    unsafeWindow.document.body.appendChild(script);
})();
