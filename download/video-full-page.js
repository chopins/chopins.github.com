// ==UserScript==
// @name     video-full-page
// @version  1
// @include  *://*
// @grant    unsafeWindow
// @run-at	 document-end
// ==/UserScript==

(function () {
    var doc = unsafeWindow.document.body,
    visVideo = [],
    checkCount = 0,
    checkMax = 3;
    function isVisible(elem) {
        return elem.offsetWidth > 0 || elem.offsetHeight > 0;
    }
    function maxVideo() {
        var max = null;
        for(var i in visVideo) {
            if(max == null) {
                max = visVideo[i];
            } else if(visVideo.offsetHeight > max.offsetHeight) {
                max= visVideo[i];
            }
        }
        return max;
    }
    function hideOther(childs) {
        for(var  i=0;i<childs.length;i++) {
            if(!video.contains(childs[i])) {
                childs[i].style.display = 'none';
            } else {
                hideOther(childs[i].childNodes);
            }
        }
    }
    function fullscreen() {
        var childs = doc.childNodes
        var video = maxVideo();
        if(video == null) {
            return;
        }
        video.style = "position:absolute;width:100%;height:100%;";
        hideOther(childs);
    }

    function checkVideo() {
        var videos = doc.getElementsByTagName('video');
        if (videos.length == 0) {
            return false;
        }

        for (var i = 0; i < videos.length; i++) {
            if (isVisible(videos[i])) {
                visVideo.push(videos[i]);
            }
        }
        if (visVideo.length == 0) {
            return false;
        }
        pop();
        return true;
    }
    function pop() {
        var pop = document.createElement('div');
        var style = 'display:inline-block;width:30px;height:30px;position:fixed;top:150px;z-index:20000;background-color: #fff;opacity: 0.7;cursor: pointer;';
        pop.setAttribute('style', style);
        pop.textContent = 'Fullscreen';
        pop.addEventListener('click', fullscreen);
        doc.appendChild(pop)
    }
    function autoCheck() {
        if(checkVideo()) {
            return;
        }
        if(checkMax < checkCount) {
            return;
        }
        checkCount++;
        setTimeout(autoCheck,2000);    
    }
    setTimeout(autoCheck,2000);

})();
