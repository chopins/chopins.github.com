// ==UserScript==
// @name     Google Translate
// @version  1
// @grant    GM.xmlHttpRequest
// @include *://*/*
// @run-at document-end
// ==/UserScript==
//

(function(){
  
  console.log('start translate');
var toLang = "zh-CN";

function setStyle(parentDoc,style) {
    for (var k in style) {
        parentDoc._google_translate_pop_info.style[k] = style[k];
    }
}
function query(parentDoc,word, pos) {
    var qurl = 'https://translate.google.cn/translate_a/single?'
            + 'client=gtx&sl=auto&tl=' + toLang + '&hl=' + toLang
            + '&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&otf=2&ssel=0&tsel=0&kc=5&'
            + 'tk=' + Math.random() * 1000000 + '&q=' + encodeURIComponent(word);
		console.log("query " + word);
    GM.xmlHttpRequest({method: "GET",
        url: qurl,
        overrideMimeType: "application/json; charset=UTF-8",
        onload: function (response) {
            var res = JSON.parse(response.responseText);
            var zh = res[0][0][0];
            var style = {display: "block", left: pos.x + 'px', top : pos.y + 'px'};
            setStyle(parentDoc,style);
	
            parentDoc._google_translate_pop_info.innerHTML = zh;

            setTimeout(function () {
                setStyle(parentDoc, {display: "none"});
            }, 10000);
        }
    });
}
function selectContent(parentDoc,e) {
    var sl = window.getSelection();
    var selectTxt = sl.toLocaleString()
    text = selectTxt.trim(selectTxt);
    if (parentDoc._google_translate_pop_info.style.display === 'block') {
        setStyle(parentDoc, {display: "none"});
    }
    if (text === '') {
        return;
    }

    if (/[\u4e00-\u9fa5]+/i.test(text)) {
        return;
    }
    var pos = {x: e.clientX, y: e.clientY + window.scrollY};

    query(parentDoc,text, pos);

}
function addEvent(parentDoc) {
  	console.log(parentDoc);
    parentDoc.ondbclick = function(e) {
      selectContent(parentDoc,e);
    };
    parentDoc.onclick = function(e) {
      selectContent(parentDoc,e);
    };

    parentDoc._google_translate_pop_info = document.createElement('div');
    var style = {borderRadius: "5px", position: "absolute", display: "none",
        padding: "10px", margin: "10px", background: "#000000", color: "#FFFFFF", opacity: "0.6"};
    setStyle(parentDoc,style);

    parentDoc.body.appendChild(parentDoc._google_translate_pop_info);
}


function load(parentNode) {
		var bodyTag = parentNode.body.tagName;

    if(bodyTag === 'FRAMESET') { 
        var frames = parentNode.getElementsByTagName('frame');
        for(var f in frames) {
          if(isNaN(f)) {
            return;
          }
          
          frames[f].onload = function () {
            load(this.contentWindow.document); 
          }
        }

    } else if( bodyTag === 'BODY') {
      	console.log('add');
      console.log(parentNode);
      	addEvent(parentNode);
    }
  	var iframes = parentNode.getElementsByTagName('iframe');
  	if(ifr) {
  		for(var ifr in iframes) {
        	if(isNaN(ifr)) {
            return;
          }
      	  load(iframes[ifr].contentWindow.document);
    	}
    }
}
load(document);
})();

