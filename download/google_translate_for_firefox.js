// ==UserScript==
// @name     Google Translate
// @version  1
// @grant    GM.xmlHttpRequest
// @include *://*/*
// @run-at document-end
// ==/UserScript==
//

(function(){

var toLang = "zh-CN";

function setStyle(parentDoc,style) {
    for (var k in style) {
        parentDoc._google_translate_pop_info.style[k] = style[k];
    }
}
function query(parentDoc,word, pos) {
    var len = word.length;
    var enword = encodeURIComponent(word);
    var qurl = 'https://translate.google.cn/translate_a/single?'
            + 'client=gtx&sl=auto&tl=' + toLang + '&hl=' + toLang
            + '&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&otf=2&ssel=0&tsel=0&kc=5&'
            + '&q=' + enword;
		var audio = 'https://translate.google.cn/translate_tts?ie=UTF-8&q='+enword
    				+'&tl=en&total=1&idx=0&textlen='+len+'&client=gt&prev=input';
    GM.xmlHttpRequest({method: "GET",
        url: qurl,
        overrideMimeType: "application/json; charset=UTF-8",
        onload: function (response) {
            var res = JSON.parse(response.responseText);

            var zh = res[0][0][0];
            var ph = res[0][1][3] ? '<span onclick="console.log(this.parentNode.previousElementSibling);this.parentNode.previousElementSibling.play();">[ '+ res[0][1][3] +' ]</span>  ' : '';
            var more = res[1] ? res[1][0][1].join('; ') : '';

            var style = {display: "block", left: pos.x + 'px', top : pos.y + 'px'};
            setStyle(parentDoc,style);

					  parentDoc._google_translate_pop_info.firstElementChild.innerHTML = '<source src="'+audio+'"></source>';
            parentDoc._google_translate_pop_info.lastElementChild.innerHTML = ph +zh+ '<br />' + more;

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
  	if (/^[~!@#$%^&*()_+\[\]\{\}\-=\\|`:;"'<>,\.\/?]+$/i.test(text)) {
      	return;
    }
    if (/^[0-9]+$/i.test(text)) {
        return;
    }
    var pos = {x: e.clientX, y: e.clientY + window.scrollY};

    query(parentDoc,text, pos);

}
function addEvent(parentDoc) {
  	document.addEventListener('dbclick',function(e) {
      selectContent(parentDoc,e);
    });
  	document.addEventListener('click',function(e) {
      selectContent(parentDoc,e);
    });
    
    parentDoc._google_translate_pop_info = document.createElement('div');
    var style = {borderRadius: "5px", position: "absolute", display: "none",
        padding: "10px", margin: "10px", background: "#000000", color: "#FFFFFF", opacity: "0.6"};
    setStyle(parentDoc,style);
    var video = document.createElement('video');
    video.autoplay = "false";
    video.style.display = 'none';
 
		parentDoc._google_translate_pop_info.appendChild(video);
    var sub = document.createElement('div');
   	parentDoc._google_translate_pop_info.appendChild(sub);
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


