// ==UserScript==
// @name     Google Translate
// @version  1
// @grant    GM.xmlHttpRequest
// @include *://*/*
// @run-at document-end
// ==/UserScript==
//

//This is firefox addons greasemonkey script
var toLang = "zh-CN"
function query(word,pos) {
	var qurl = 'https://translate.google.cn/translate_a/single?'
  +'client=gtx&sl=auto&tl='+toLang+'&hl='+toLang
  +'&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&otf=2&ssel=0&tsel=0&kc=5&'
	+'tk='+Math.random()*1000000+'&q='+ encodeURIComponent(word);

  GM.xmlHttpRequest({ method: "GET",
  url: qurl,
  overrideMimeType : "application/json; charset=UTF-8",
  onload: function(response) {
   var res  = JSON.parse(response.responseText);
   var zh = res[0][0][0];

   window._google_translate_pop_info.style.display = 'block';
   window._google_translate_pop_info.innerHTML = zh;
   window._google_translate_pop_info.style.left = pos.x+'px';
   window._google_translate_pop_info.style.top = pos.y + 'px';
   setTimeout(function() {window._google_translate_pop_info.style.display = 'none';},10000);
  }});
}
function selectContent(e) {
  var	sl = window.getSelection();
  var selectTxt = sl.toLocaleString()
  text = selectTxt.trim(selectTxt);
  var pos = {x:e.clientX,y:e.clientY + window.scrollY};
  if(window._google_translate_pop_info.style.display == 'block') {
    window._google_translate_pop_info.style.display = 'none';
  }
  if(text !== '') {
    query(text,pos);
  }
}
document.body.onload = function() {
  document.ondbclick = selectContent;
  document.onclick = selectContent;
 
  window._google_translate_pop_info = document.createElement('div');
  var style = {borderRadius:"5px",position : "absolute", 
               padding : "10px",margin:"10px",background :"#000000",color:"#FFFFFF",opacity:"0.6"};
  for(var k in style) {
  	window._google_translate_pop_info.style[k] = style[k];  
  }
  
  document.body.appendChild(window._google_translate_pop_info);
}


