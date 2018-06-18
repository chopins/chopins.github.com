// ==UserScript==
// @name     txt2book-view
// @version  1
// @include    file://*.txt#*
// @include    file://*.txt
// @grant    none
// @run-at	 document-end
// ==/UserScript==

(function() {
  console.log('load');
  var style = "body{background-color: #EDE8D5;padding: 0px 120px;color: #333;} ul{display: inline-block;position: fixed;top: 20px;left: 0px;} p {text-indent: 40px;font-size: 20px;line-height: 38px;letter-spacing: 2px;}"
  var se = document.createElement('style');
  se.textContent = style;
  unsafeWindow.document.head.appendChild(se);
  
  var c = unsafeWindow.document.getElementsByTagName('pre')[0];
  var html = c.textContent.split("\n").join('</p><p>');
  c.innerHTML = '<p>' + html + '</p>';
	var plist = unsafeWindow.document.getElementsByTagName('p');
  var c = '';
  var cn = 1;
  var clist = '';
  for(var i =0;i<plist.length;i++) {
    	c = plist[i].textContent.replace(/^\s*/,'');
      plist[i].textContent = c;
      res = c.match(/^\s*第[一二三四五六七八九十0-9]+[章节回][\s\n]*/);
      if(res) {
        	plist[i].setAttribute('id','c-'+cn);
          clist += '<li><a href="#c-'+cn+'">' +c+'</a></li>';
        	cn++;
      }
  }
  var ul = document.createElement('ul');
  ul.innerHTML = clist;
  unsafeWindow.document.body.appendChild(ul);
})();
