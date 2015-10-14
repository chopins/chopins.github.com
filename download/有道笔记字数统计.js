// ==UserScript==
// @name        youdao.note.show.char.size
// @namespace   youdao.note.show.char.size
// @include     http://note.youdao.com/web/*
// @version     1
// @grant       none
// ==/UserScript==


//需要点击当前字数来更新统计
function loadsize() {
  var nobr = document.createElement('nobr');
  document.getElementsByClassName('note-meta') [0].appendChild(nobr);
  function showsize() {
    var ifr = document.getElementsByClassName('ke-edit-iframe') [0];
    var size = ifr.contentDocument.getElementsByClassName('ke-content editor-body') [0].textContent.replace(/\s+/g, '').length
    nobr.textContent = '字符数：' + size;
  }
  nobr.onclick = showsize
  showsize();
}
document.body.onload = function () {
  function checkiframe() {
    var l = document.getElementsByClassName('ke-edit-iframe').length;
    var l2 = document.getElementsByClassName('note-meta').length;
    if (l > 0 && l2 >0) {
      loadsize();
      return;
    }
    setTimeout(checkiframe, 1000);
  }
  checkiframe();
}
