// ==UserScript==
// @name     txt2bookpage
// @version  1
// @include    file://*.txt#*
// @include    file://*.txt
// @grant    unsafeWindow
// @run-at	 document-end
// ==/UserScript==

(function () {
    console.log('load txt book');

    var style = "html{background-color:#DCCB9C;}body{background-color: #EDE8D5;margin:0px 250px;padding: 0 30px;color: #666;} a{color:#666;}"
        + ".pb{font-weight: bold;} .active{background-color:#ECECDF;}.active a{color: #D35452;}"
        + "ul{padding-left: 10px;height: 90%;overflow-y: auto;display: inline-block;position:fixed;width:250px;"
        + "top:5px;left: 0px;list-style: none;} ul li{padding:3px;width: 100%;}"
        + "ul div span {display: inline-block;width:20px;height:20px;border:1px solid #ccc;margin-right: 5px;}"
        + "p {text-indent: 40px;font-size: 20px;line-height: 38px;letter-spacing: 2px;}"
    var se = document.createElement('style');
    se.textContent = style;
    unsafeWindow.document.head.appendChild(se);

    var c = unsafeWindow.document.getElementsByTagName('pre')[0];
    var clist = '<div id="bgcolor"><span style="background-color:#f6f4ec;" data-pbg="#EBE5D8">'
        + '</span><span style="background-color:#F6ECCB;" data-pbg="#DCCB9C"></span>'
        + '<span style="background-color:#E5F1E5;" data-pbg="#CFE1CF"></span>'
        + '<span style="background-color:#161819;" data-pbg="#0E0F11"></span>'
        + '<span style="background-color:#DEDEDE;" data-pbg="#CFCFCF"></span></div>'
    var idx = 1;
    var reg = new RegExp(/^\s*第[一二三四五六七八九十百千十〇0-9]+[章节回集][\s\n]*/);
    var cnum = 0;
    clist += '<li><a href="#p-' + idx + '" data-cn="'+cnum+'">书首</a></li>';
    var html = c.textContent.replace(/([^\n]*)\n/g, function (ms,sub) {
        var s ='<p id="c-p-'+idx+'"';
        if (reg.test(sub)) {
            s += ' class="pb"';
            cnum++;
            clist += '<li><a href="#p-' + idx + '" data-cn="'+cnum+'">' + sub + '</a></li>';
        }
        s += ' data-cn="'+cnum+'">';
        idx++;
        return s + sub + '</p>';
     });
    c.innerHTML =  html;

    var ul = document.createElement('ul');
    ul.innerHTML = clist;
    unsafeWindow.document.body.appendChild(ul);
    var scriptText = function () {
        /*function hashCode(str) {
            var hash = 0, i, chr;
            if (str.length === 0) return hash;
            for (i = 0; i < str.length; i++) {
                chr = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0; // Convert to 32bit integer
            }
            return hash;
        };
        var fileHash = hashCode(window.location.pathname);*/
        document.getElementById('bgcolor').addEventListener('click', function (e) {
            var ele = e.target;
            if (ele.tagName == 'SPAN') {
                document.body.style.backgroundColor = ele.style.backgroundColor;
                document.getElementsByTagName('html')[0].style.backgroundColor = ele.getAttribute('data-pbg');
            }
        });;
        var activeLink = null;
        var st = 0;
        var activeP = document.getElementById('c-p-1');
        function __gmk_getActive() {
            var activeId = window.location.hash.split('#')[1];
            if(activeId.indexOf('c-') != 0) {
                activeId = 'c-' + activeId;
            }
            activeP = document.getElementById(activeId);
            var idx = activeP.getAttribute('data-cn');
            if(activeLink != null) {
                activeLink.removeAttribute('class');
            }
            activeLink = document.getElementsByTagName('li')[idx];
            activeLink.className = 'active';
        }
        window.onhashchange = __gmk_getActive;
        //window.document.body.onscroll = __gmk_getActive;
        /*var pageIndex = window.localStorage.getItem(fileHash);
        if (pageIndex) {
            window.location.hash = '#c-' + pageIndex;
        }*/
        if (window.location.hash != '' && window.location.hash != '#') {
            __gmk_getActive();
        }
        document.getElementsByTagName('ul')[0].addEventListener('click', function (e) {
            var ele = e.target;
            if (ele.tagName == 'LI') {
                ele = ele.getElementsByTagName('a')[0];
            }
            if (ele.tagName == 'A') {
                window.location.hash = '#c-' + ele.href.split('#')[1];
            }
        });
        function findOffset(offset, lastNode, next) {
            var find = null;
            while(true) {
                if(next) {
                    find = lastNode.nextSibling;
                } else {
                    find = lastNode.previousSibling
                }
                if(find == null) {
                    return;
                } else if(find.nodeType == 1) {
                    nodeOffset = find.offsetTop;
                    nodeBottomOffset = nodeOffset + find.offsetHeight;
                    if(offset >= nodeOffset && nodeBottomOffset > offset) {
                        window.location.hash = '#' + find.id;
                        return;
                    }
                }
                lastNode = find;
            }
        }
        window.onscroll = function() {
            if(st) {
                clearTimeout(st);
            }
            st = setTimeout(function() {
            var scorllTop = window.scrollY;
            cOffset = 0;
            if(activeP != null) {
                cOffset = activeP.offsetTop + activeP.offsetHeight;
            }
            if(cOffset > scorllTop) {
                findOffset(scorllTop, activeP, false);
            } else {
                findOffset(scorllTop, activeP, true);
            }}, 1000);
        }
        /*window.onresize = function() {
            window.__gmk_resizeStatus = true;
            var lis = document.getElementsByTagName('ul')[0].getElementsByTagName('li');
            var hash = window.location.href.split('#')[1];
            var i = pos = 0;
            for(i=0; i<lis.length;i++) {
                if(lis[i].nodeType == 1) {
                    pos = lis[i].getElementsByTagName('a')[0].href.split('#')[1];
                    lis[i].setAttribute('data-top',document.getElementById('c-'+pos).offsetTop);
                }
            }
            window.scrollTo(0,document.getElementById('c-'+hash).offsetTop);
        };*/
    };
    var script = document.createElement('script');
    script.type = "text/javascript";
    script.innerHTML = '(' + scriptText.toString() + ')();';
    unsafeWindow.document.body.appendChild(script);
})();
