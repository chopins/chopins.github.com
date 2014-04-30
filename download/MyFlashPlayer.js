// ==UserScript==
// @name replaceFlashPlayer
// @namespace replace.youku.todou.player
// @description Flash.2.MPlayer
// @version 2
// @match http://*/*
// @match http://www.tudou.com/*
// @match http://douban.fm/
// @match http://pan.baidu.com/*
// @match http:/tv.sohu.com/*
// @grant GM_xmlhttpRequest
// ==/UserScript==

function F() {//!function(){function h(p){console.log("$f.fireEvent",[].slice.call(p))}function l(r){if(!r||typeof r!="object"){return r}var p=new r.constructor();for(var q in r){if(r.hasOwnProperty(q)){p[q]=l(r[q])}}return p}function n(u,r){if(!u){return}var p,q=0,s=u.length;if(s===undefined){for(p in u){if(r.call(u[p],p,u[p])===false){break}}}else{for(var t=u[0];q<s&&r.call(t,q,t)!==false;t=u[++q]){}}return u}function c(p){return document.getElementById(p)}function j(r,q,p){if(typeof q!="object"){return r}if(r&&q){n(q,function(s,t){if(!p||typeof t!="function"){r[s]=t}})}return r}function o(t){var r=t.indexOf(".");if(r!=-1){var q=t.slice(0,r)||"*";var p=t.slice(r+1,t.length);var s=[];n(document.getElementsByTagName(q),function(){if(this.className&&this.className.indexOf(p)!=-1){s.push(this)}});return s}}function g(p){p=p||window.event;if(p.preventDefault){p.stopPropagation();p.preventDefault()}else{p.returnValue=false;p.cancelBubble=true}return false}function k(r,p,q){r[p]=r[p]||[];r[p].push(q)}function e(p){return p.replace(/&amp;/g,"%26").replace(/&/g,"%26").replace(/=/g,"%3D")}function f(){return"_"+(""+Math.random()).slice(2,10)}var i=function(u,s,t){var r=this,q={},v={};r.index=s;if(typeof u=="string"){u={url:u}}j(this,u,true);n(("Begin*,Start,Pause*,Resume*,Seek*,Stop*,Finish*,LastSecond,Update,BufferFull,BufferEmpty,BufferStop").split(","),function(){var w="on"+this;if(w.indexOf("*")!=-1){w=w.slice(0,w.length-1);var x="onBefore"+w.slice(2);r[x]=function(y){k(v,x,y);return r}}r[w]=function(y){k(v,w,y);return r};if(s==-1){if(r[x]){t[x]=r[x]}if(r[w]){t[w]=r[w]}}});j(this,{onCuepoint:function(y,x){if(arguments.length==1){q.embedded=[null,y];return r}if(typeof y=="number"){y=[y]}var w=f();q[w]=[y,x];if(t.isLoaded()){t._api().fp_addCuepoints(y,s,w)}return r},update:function(x){j(r,x);if(t.isLoaded()){t._api().fp_updateClip(x,s)}var w=t.getConfig();var y=(s==-1)?w.clip:w.playlist[s];j(y,x,true)},_fireEvent:function(w,z,x,B){if(w=="onLoad"){n(q,function(C,D){if(D[0]){t._api().fp_addCuepoints(D[0],s,C)}});return false}B=B||r;if(w=="onCuepoint"){var A=q[z];if(A){return A[1].call(t,B,x)}}if(z&&"onBeforeBegin,onMetaData,onMetaDataChange,onStart,onUpdate,onResume".indexOf(w)!=-1){j(B,z);if(z.metaData){if(!B.duration){B.duration=z.metaData.duration}else{B.fullDuration=z.metaData.duration}}}var y=true;n(v[w],function(){y=this.call(t,B,z,x)});return y}});if(u.onCuepoint){var p=u.onCuepoint;r.onCuepoint.apply(r,typeof p=="function"?[p]:p);delete u.onCuepoint}n(u,function(w,x){if(typeof x=="function"){k(v,w,x);delete u[w]}});if(s==-1){t.onCuepoint=this.onCuepoint}};var m=function(q,s,r,u){var p=this,t={},v=false;if(u){j(t,u)}n(s,function(w,x){if(typeof x=="function"){t[w]=x;delete s[w]}});j(this,{animate:function(z,A,y){if(!z){return p}if(typeof A=="function"){y=A;A=500}if(typeof z=="string"){var x=z;z={};z[x]=A;A=500}if(y){var w=f();t[w]=y}if(A===undefined){A=500}s=r._api().fp_animate(q,z,A,w);return p},css:function(x,y){if(y!==undefined){var w={};w[x]=y;x=w}s=r._api().fp_css(q,x);j(p,s);return p},show:function(){this.display="block";r._api().fp_showPlugin(q);return p},hide:function(){this.display="none";r._api().fp_hidePlugin(q);return p},toggle:function(){this.display=r._api().fp_togglePlugin(q);return p},fadeTo:function(z,y,x){if(typeof y=="function"){x=y;y=500}if(x){var w=f();t[w]=x}this.display=r._api().fp_fadeTo(q,z,y,w);this.opacity=z;return p},fadeIn:function(x,w){return p.fadeTo(1,x,w)},fadeOut:function(x,w){return p.fadeTo(0,x,w)},getName:function(){return q},getPlayer:function(){return r},_fireEvent:function(x,w,y){if(x=="onUpdate"){var A=r._api().fp_getPlugin(q);if(!A){return}j(p,A);delete p.methods;if(!v){n(A.methods,function(){var C=""+this;p[C]=function(){var D=[].slice.call(arguments);var E=r._api().fp_invoke(q,C,D);return E==="undefined"||E===undefined?p:E}});v=true}}var B=t[x];if(B){var z=B.apply(p,w);if(x.slice(0,1)=="_"){delete t[x]}return z}return p}})};function b(r,H,u){var x=this,w=null,E=false,v,t,G=[],z={},y={},F,s,q,D,p,B;j(x,{id:function(){return F},isLoaded:function(){return(w!==null&&w.fp_play!==undefined&&!E)},getParent:function(){return r},hide:function(I){if(I){r.style.height="0px"}if(x.isLoaded()){w.style.height="0px"}return x},show:function(){r.style.height=B+"px";if(x.isLoaded()){w.style.height=p+"px"}return x},isHidden:function(){return x.isLoaded()&&parseInt(w.style.height,10)===0},load:function(K){if(!x.isLoaded()&&x._fireEvent("onBeforeLoad")!==false){var I=function(){if(v&&!flashembed.isSupported(H.version)){r.innerHTML=""}if(K){K.cached=true;k(y,"onLoad",K)}flashembed(r,H,{config:u})};var J=0;n(a,function(){this.unload(function(L){if(++J==a.length){I()}})})}return x},unload:function(K){if(v.replace(/\s/g,"")!==""){if(x._fireEvent("onBeforeUnload")===false){if(K){K(false)}return x}E=true;try{if(w){if(w.fp_isFullscreen()){w.fp_toggleFullscreen()}w.fp_close();x._fireEvent("onUnload")}}catch(I){}var J=function(){w=null;r.innerHTML=v;E=false;if(K){K(true)}};if(/WebKit/i.test(navigator.userAgent)&&!/Chrome/i.test(navigator.userAgent)){setTimeout(J,0)}else{J()}}else{if(K){K(false)}}return x},getClip:function(I){if(I===undefined){I=D}return G[I]},getCommonClip:function(){return t},getPlaylist:function(){return G},getPlugin:function(I){var K=z[I];if(!K&&x.isLoaded()){var J=x._api().fp_getPlugin(I);if(J){K=new m(I,J,x);z[I]=K}}return K},getScreen:function(){return x.getPlugin("screen")},getControls:function(){return x.getPlugin("controls")._fireEvent("onUpdate")},getLogo:function(){try{return x.getPlugin("logo")._fireEvent("onUpdate")}catch(I){}},getPlay:function(){return x.getPlugin("play")._fireEvent("onUpdate")},getConfig:function(I){return I?l(u):u},getFlashParams:function(){return H},loadPlugin:function(L,K,N,M){if(typeof N=="function"){M=N;N={}}var J=M?f():"_";x._api().fp_loadPlugin(L,K,N,J);var I={};I[J]=M;var O=new m(L,null,x,I);z[L]=O;return O},getState:function(){return x.isLoaded()?w.fp_getState():-1},play:function(J,I){var K=function(){if(J!==undefined){x._api().fp_play(J,I)}else{x._api().fp_play()}};if(x.isLoaded()){K()}else{if(E){setTimeout(function(){x.play(J,I)},50)}else{x.load(function(){K()})}}return x},getVersion:function(){var J="flowplayer.js @VERSION";if(x.isLoaded()){var I=w.fp_getVersion();I.push(J);return I}return J},_api:function(){if(!x.isLoaded()){throw"Flowplayer "+x.id()+" not loaded when calling an API method"}return w},setClip:function(I){n(I,function(J,K){if(typeof K=="function"){k(y,J,K);delete I[J]}else{if(J=="onCuepoint"){$f(r).getCommonClip().onCuepoint(I[J][0],I[J][1])}}});x.setPlaylist([I]);return x},getIndex:function(){return q},bufferAnimate:function(I){w.fp_bufferAnimate(I===undefined||I);return x},_swfHeight:function(){return w.clientHeight}});n(("Click*,Load*,Unload*,Keypress*,Volume*,Mute*,Unmute*,PlaylistReplace,ClipAdd,Fullscreen*,FullscreenExit,Error,MouseOver,MouseOut").split(","),function(){var I="on"+this;if(I.indexOf("*")!=-1){I=I.slice(0,I.length-1);var J="onBefore"+I.slice(2);x[J]=function(K){k(y,J,K);return x}}x[I]=function(K){k(y,I,K);return x}});n(("pause,resume,mute,unmute,stop,toggle,seek,getStatus,getVolume,setVolume,getTime,isPaused,isPlaying,startBuffering,stopBuffering,isFullscreen,toggleFullscreen,reset,close,setPlaylist,addClip,playFeed,setKeyboardShortcutsEnabled,isKeyboardShortcutsEnabled").split(","),function(){var I=this;x[I]=function(K,J){if(!x.isLoaded()){return x}var L=null;if(K!==undefined&&J!==undefined){L=w["fp_"+I](K,J)}else{L=(K===undefined)?w["fp_"+I]():w["fp_"+I](K)}return L==="undefined"||L===undefined?x:L}});x._fireEvent=function(R){if(typeof R=="string"){R=[R]}var S=R[0],P=R[1],N=R[2],M=R[3],L=0;if(u.debug){h(R)}if(!x.isLoaded()&&S=="onLoad"&&P=="player"){w=w||c(s);p=x._swfHeight();n(G,function(){this._fireEvent("onLoad")});n(z,function(T,U){U._fireEvent("onUpdate")});t._fireEvent("onLoad")}if(S=="onLoad"&&P!="player"){return}if(S=="onError"){if(typeof P=="string"||(typeof P=="number"&&typeof N=="number")){P=N;N=M}}if(S=="onContextMenu"){n(u.contextMenu[P],function(T,U){U.call(x)});return}if(S=="onPluginEvent"||S=="onBeforePluginEvent"){var I=P.name||P;var J=z[I];if(J){J._fireEvent("onUpdate",P);return J._fireEvent(N,R.slice(3))}return}if(S=="onPlaylistReplace"){G=[];var O=0;n(P,function(){G.push(new i(this,O++,x))})}if(S=="onClipAdd"){if(P.isInStream){return}P=new i(P,N,x);G.splice(N,0,P);for(L=N+1;L<G.length;L++){G[L].index++}}var Q=true;if(typeof P=="number"&&P<G.length){D=P;var K=G[P];if(K){Q=K._fireEvent(S,N,M)}if(!K||Q!==false){Q=t._fireEvent(S,N,M,K)}}n(y[S],function(){Q=this.call(x,P,N);if(this.cached){y[S].splice(L,1)}if(Q===false){return false}L++});return Q};function C(){if($f(r)){$f(r).getParent().innerHTML="";q=$f(r).getIndex();a[q]=x}else{a.push(x);q=a.length-1}B=parseInt(r.style.height,10)||r.clientHeight;F=r.id||"fp"+f();s=H.id||F+"_api";H.id=s;v=r.innerHTML;if(typeof u=="string"){u={clip:{url:u}}}u.playerId=F;u.clip=u.clip||{};if(r.getAttribute("href",2)&&!u.clip.url){u.clip.url=r.getAttribute("href",2)}if(u.clip.url){u.clip.url=e(u.clip.url)}t=new i(u.clip,-1,x);u.playlist=u.playlist||[u.clip];var J=0;n(u.playlist,function(){var M=this;if(typeof M=="object"&&M.length){M={url:""+M}}if(M.url){M.url=e(M.url)}n(u.clip,function(N,O){if(O!==undefined&&M[N]===undefined&&typeof O!="function"){M[N]=O}});u.playlist[J]=M;M=new i(M,J,x);G.push(M);J++});n(u,function(M,N){if(typeof N=="function"){if(t[M]){t[M](N)}else{k(y,M,N)}delete u[M]}});n(u.plugins,function(M,N){if(N){z[M]=new m(M,N,x)}});if(!u.plugins||u.plugins.controls===undefined){z.controls=new m("controls",null,x)}z.canvas=new m("canvas",null,x);v=r.innerHTML;function L(M){if(/iPad|iPhone|iPod/i.test(navigator.userAgent)&&!/.flv$/i.test(G[0].url)&&!K()){return true}if(!x.isLoaded()&&x._fireEvent("onBeforeClick")!==false){x.load()}return g(M)}function K(){return x.hasiPadSupport&&x.hasiPadSupport()}function I(){if(v.replace(/\s/g,"")!==""){if(r.addEventListener){r.addEventListener("click",L,false)}else{if(r.attachEvent){r.attachEvent("onclick",L)}}}else{if(r.addEventListener&&!K()){r.addEventListener("click",g,false)}x.load()}}setTimeout(I,0)}if(typeof r=="string"){var A=c(r);if(!A){throw"Flowplayer cannot access element: "+r}r=A;C()}else{C()}}var a=[];function d(p){this.length=p.length;this.each=function(r){n(p,r)};this.size=function(){return p.length};var q=this;for(name in b.prototype){q[name]=function(){var r=arguments;q.each(function(){this[name].apply(this,r)})}}}window.flowplayer=window.$f=function(){var q=null;var p=arguments[0];if(!arguments.length){n(a,function(){if(this.isLoaded()){q=this;return false}});return q||a[0]}if(arguments.length==1){if(typeof p=="number"){return a[p]}else{if(p=="*"){return new d(a)}n(a,function(){if(this.id()==p.id||this.id()==p||this.getParent()==p){q=this;return false}});return q}}if(arguments.length>1){var u=arguments[1],r=(arguments.length==3)?arguments[2]:{};if(typeof u=="string"){u={src:u}}u=j({bgcolor:"#000000",version:[10,1],expressInstall:"http://releases.flowplayer.org/swf/expressinstall.swf",cachebusting:false},u);if(typeof p=="string"){if(p.indexOf(".")!=-1){var t=[];n(o(p),function(){t.push(new b(this,l(u),l(r)))});return new d(t)}else{var s=c(p);return new b(s!==null?s:l(p),l(u),l(r))}}else{if(p){return new b(p,l(u),l(r))}}}return null};j(window.$f,{fireEvent:function(){var q=[].slice.call(arguments);var r=$f(q[0]);return r?r._fireEvent(q.slice(1)):null},addPlugin:function(p,q){b.prototype[p]=q;return $f},each:n,extend:j});if(typeof jQuery=="function"){jQuery.fn.flowplayer=function(r,q){if(!arguments.length||typeof arguments[0]=="number"){var p=[];this.each(function(){var s=$f(this);if(s){p.push(s)}});return arguments.length?p[arguments[0]]:new d(p)}return this.each(function(){$f(this,l(r),q?l(q):{})})}}}();!function(){var h=document.all,j="http://get.adobe.com/flashplayer",c=typeof jQuery=="function",e=/(\d+)[^\d]+(\d+)[^\d]*(\d*)/,b={width:"100%",height:"100%",id:"_"+(""+Math.random()).slice(9),allowfullscreen:true,allowscriptaccess:"always",quality:"high",version:[3,0],onFail:null,expressInstall:null,w3c:false,cachebusting:false};if(window.attachEvent){window.attachEvent("onbeforeunload",function(){__flash_unloadHandler=function(){};__flash_savedUnloadHandler=function(){}})}function i(m,l){if(l){for(var f in l){if(l.hasOwnProperty(f)){m[f]=l[f]}}}return m}function a(f,n){var m=[];for(var l in f){if(f.hasOwnProperty(l)){m[l]=n(f[l])}}return m}window.flashembed=function(f,m,l){if(typeof f=="string"){f=document.getElementById(f.replace("#",""))}if(!f){return}if(typeof m=="string"){m={src:m}}return new d(f,i(i({},b),m),l)};var g=i(window.flashembed,{conf:b,getVersion:function(){var m,f,o;try{o=navigator.plugins["Shockwave Flash"];if(o[0].enabledPlugin!=null){f=o.description.slice(16)}}catch(p){try{m=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");f=m&&m.GetVariable("$version")}catch(n){try{m=new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");f=m&&m.GetVariable("$version")}catch(l){}}}f=e.exec(f);return f?[1*f[1],1*f[(f[1]*1>9?2:3)]*1]:[0,0]},asString:function(l){if(l===null||l===undefined){return null}var f=typeof l;if(f=="object"&&l.push){f="array"}switch(f){case"string":l=l.replace(new RegExp('(["\\\\])',"g"),"\\$1");l=l.replace(/^\s?(\d+\.?\d*)%/,"$1pct");return'"'+l+'"';case"array":return"["+a(l,function(o){return g.asString(o)}).join(",")+"]";case"function":return'"function()"';case"object":var m=[];for(var n in l){if(l.hasOwnProperty(n)){m.push('"'+n+'":'+g.asString(l[n]))}}return"{"+m.join(",")+"}"}return String(l).replace(/\s/g," ").replace(/\'/g,'"')},getHTML:function(o,l){o=i({},o);var n='<object width="'+o.width+'" height="'+o.height+'" id="'+o.id+'" name="'+o.id+'"';if(o.cachebusting){o.src+=((o.src.indexOf("?")!=-1?"&":"?")+Math.random())}if(o.w3c||!h){n+=' data="'+o.src+'" type="application/x-shockwave-flash"'}else{n+=' classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"'}n+=">";if(o.w3c||h){n+='<param name="movie" value="'+o.src+'" />'}o.width=o.height=o.id=o.w3c=o.src=null;o.onFail=o.version=o.expressInstall=null;for(var m in o){if(o[m]){n+='<param name="'+m+'" value="'+o[m]+'" />'}}var p="";if(l){for(var f in l){if(l[f]){var q=l[f];p+=f+"="+(/function|object/.test(typeof q)?g.asString(q):q)+"&"}}p=p.slice(0,-1);n+='<param name="flashvars" value=\''+p+"' />"}n+="</object>";return n},isSupported:function(f){return k[0]>f[0]||k[0]==f[0]&&k[1]>=f[1]}});var k=g.getVersion();function d(f,n,m){if(g.isSupported(n.version)){f.innerHTML=g.getHTML(n,m)}else{if(n.expressInstall&&g.isSupported([6,65])){f.innerHTML=g.getHTML(i(n,{src:n.expressInstall}),{MMredirectURL:encodeURIComponent(location.href),MMplayerType:"PlugIn",MMdoctitle:document.title})}else{if(!f.innerHTML.replace(/\s/g,"")){f.innerHTML="<h2>Flash version "+n.version+" or greater is required</h2><h3>"+(k[0]>0?"Your version is "+k:"You have no flash plugin installed")+"</h3>"+(f.tagName=="A"?"<p>Click here to download latest version</p>":"<p>Download latest version from <a href='"+j+"'>here</a></p>");if(f.tagName=="A"||f.tagName=="DIV"){f.onclick=function(){location.href=j}}}if(n.onFail){var l=n.onFail.call(this);if(typeof l=="string"){f.innerHTML=l}}}}if(h){window[n.id]=document.getElementById(n.id)}i(this,{getRoot:function(){return f},getOptions:function(){return n},getConf:function(){return m},getApi:function(){return f.firstChild}})}if(c){jQuery.tools=jQuery.tools||{version:"@VERSION"};jQuery.tools.flashembed={conf:b};jQuery.fn.flashembed=function(l,f){return this.each(function(){$(this).data("flashembed",flashembed(this,l,f))})}}}();
}

(function(global) {
	function createFlowPlayer(id, allList, duration) {
		var playerUrl = 'http://page.toknot.com/flowplayer/flowplayer-3.2.18.swf';
		//var playerUrl = 'http://127.0.0.1:8086/flowplayer/flowplayer-3.2.18.swf';
		function farmatTime(sec) {
			sec = parseInt(sec);
			var s = sec % 60;
			s = s > 9 ? s : '0' + s;
			var m = Math.round(sec / 60);
			m = m > 9 ? m : '0' + m;
			return m + ':' + s;
		}
		;
		function getSeqsTime(seq) {
			var t = 0;
			for (var i = 0; i < seq; i++) {
				t += playerList[i].t;
			}
			return t;
		}
		function getFileType(obj) {
			var keys = Object.keys(obj);
			if (keys.indexOf('hd2') >= 0) {
				return 'hd2';
			} else if (keys.indexOf('mp4') >= 0) {
				return 'mp4';
			} else if (keys.indexOf('flv') >= 0) {
				return 'flv';
			}
		}
		;
		
		document.getElementById('player').innerHTML = '';
		var defaultType = getFileType(allList);
		var playerList = allList[defaultType];
		flowplayer(id, playerUrl, {
			clip: {
				autoPlay: true,
				autoBuffering: true
				//duration: duration
			},
			playlist: playerList,
			plugins: {
				controls: {
					playlist: true,
					scrubber: true
				}}
		});
		var playerDiv = document.getElementById(id).parentNode;
		var info = document.createElement('div');
		info.id = 'videoData';
		var allType = Object.keys(allList);
		var typeList = document.createElement('select');
		for (var i = 0; i < allType.length; i++) {
			var op = document.createElement('option');
			op.innerHTML = allType[i];
			op.value = allType[i];
			op.id = 'type_' + allType[i];
			if(allType[i] == defaultType) {
				op.setAttribute('selected', true);
			}
			typeList.appendChild(op);
		}
		
		info.appendChild(typeList);
		
		var seqList = document.createElement('select');
		for (var i = 0; i < playerList.length; i++) {
			var op = document.createElement('option');
			op.innerHTML = i + 1;
			op.value = i;
			op.id = 'seq_' + i;
			seqList.appendChild(op);
		}
		
		seqList.onchange = function(e) {
			$f('player').play(parseInt(seqList.value));
		};
		info.appendChild(seqList);
		var currPlayerTime = document.createElement('span');
		currPlayerTime.innerHTML = '00:00';
		info.appendChild(currPlayerTime);
		var sumDuration = document.createElement('span');
		sumDuration.innerHTML = '/'+farmatTime(duration);
		info.appendChild(sumDuration);

		playerDiv.appendChild(info);
		var currIndex = 0;
		var playerTime = 0;
		var opList = seqList.getElementsByTagName('option');
		
		typeList.onchange = function(e) {
			var pList = allList[typeList.value];
			playerList = pList;
			$f('player').setPlaylist(pList);
			currIndex =0;
			seqList.innerHTML = '';
			var timeSum = 0;
			for (var i = 0; i < pList.length; i++) {
				var op = document.createElement('option');
				op.innerHTML = i + 1;
				op.value = i;
				op.id = 'seq_' + i;
				seqList.appendChild(op);
				if(timeSum < playerTime && (timeSum + pList[i].t) > playerTime) {
					currIndex = i;
					op.selected = true;
				}
				timeSum += pList[i].t;
			}
			$f('player').play(currIndex);
		};

		var prePlayTime = 0;
		var cnt = 0;
		
		function updateTime() {
			var clip = $f('player').getClip();
			
			if (clip) {
				var index = clip.index;
				if (index != currIndex) {
					for (var i = 0; i < opList.length; i++) {
						if (opList[i].value == index) {
							opList[i].setAttribute('selected', true);
						}
						if (opList[i].value == currIndex) {
							opList[i].setAttribute('selected', false);
						}
					}
					currIndex = index;
					playerTime = getSeqsTime(currIndex);

				}
				var curTime = $f('player').getTime();
				if(curTime == prePlayTime) {
					if(cnt > 3) {
						$f('player').play();
						$f('player').seek(prePlayTime);
					}
					cnt = cnt+1;
				} else {
					cnt =0;
				}
				prePlayTime = curTime;
				var htmlTime = playerTime + curTime;
				currPlayerTime.innerHTML = farmatTime(htmlTime);
			}
			setTimeout(updateTime, 500);
		}
		updateTime();
	}
	function createPlayerJs() {
		var script = document.createElement('script');
		script.setAttribute('type', 'text/javascript');
		var c = F.toString().replace('function F() {//', '');
		script.textContent = c.substr(0, c.length - 1);
		script.id = 'flowplayer';
		document.getElementsByTagName('head')[0].appendChild(script);
	}
	function getCookie(cn) {
		var i, x, y, a = document.cookie.split(";");
		for (i = 0; i < a.length; i++) {
			x = a[i].substr(0, a[i].indexOf("="));
			y = a[i].substr(a[i].indexOf("=") + 1);
			x = x.replace(/^\s+|\s+$/g, "");
			if (x == cn)
				return unescape(y);
		}
		return null;
	}
	;
	function setCookie(cn, v, ex) {
		var e = new Date(), n = e.getTime();
		ex = n + ex * 1000;
		e.setTime(ex);
		var cv = escape(v) + "; exs=" + e.toUTCString();
		document.cookie = cn + "=" + cv;
	}
	;
	function setVideoId() {
		if (typeof videoId2 !== 'undefined' && videoId2 != '') {
			var videoId = videoId2;
		} else if (typeof vcode !== 'undefined' && vcode != '') {
			var videoId = window.vcode;
		} else if (typeof iid !== 'undefined') {
			var videoId = window.iid;
			document.getElementById('__flash2mplayer').setAttribute('tudouiid', 1);
			document.getElementById('__flash2mplayer').setAttribute('segs', itemData.segs.toString());
		} else if (typeof itemData != 'undefined' && typeof itemData.iid != 'undefined') {
			var videoId = itemData.iid;
			document.getElementById('__flash2mplayer').setAttribute('segs', itemData.segs.toString());
		} else if (typeof pageConfig != 'undefined' && typeof pageConfig.iid != 'undefined') {
			var videoId = pageConfig.iid;
			document.getElementById('__flash2mplayer').setAttribute('tudouiid', 1);
			document.getElementById('__flash2mplayer').setAttribute('segs', pageConfig.segs);
		} else {
			console.log('No Video Id');
			if (typeof videoId == 'undefined') {
				return;
			}
		}
		window.vcode = videoId;
		document.getElementById('__flash2mplayer').setAttribute('vcode', videoId);
	}

	function randomString(len) {
		len = parseInt(len);
		len = len || 32;
		var $chars = 'ABCDEFGHJKMNPQRSTWXYZabcdefhijkmnprstwxyz2345678';
		var maxPos = $chars.length;
		var pwd = '';
		for (i = 0; i < len; i++) {
			pwd += $chars.charAt(Math.floor(Math.random() * maxPos));
		}
		return pwd;
	}

	
	function F2MgetYoukuURL(spec) {
		function getFileIDMixString(seed) {
			var mixed = [
			];
			var source = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/\\:._-1234567890'.split('');
			var index,
					len = source.length;
			for (var i = 0; i < len; i++) {
				seed = (seed * 211 + 30031) % 65536;
				index = Math.floor(seed / 65536 * source.length);
				mixed.push(source[index]);
				source.splice(index, 1);
			}
			return mixed;
		}
		;
		function getFileID(fileid, seed) {
			var mixed = getFileIDMixString(seed);
			var ids = fileid.split('*');
			var realId = [
			];
			var idx;
			for (var i = 0; i < ids.length; i++) {
				idx = parseInt(ids[i], 10);
				realId.push(mixed[idx]);
			}
			return realId.join('');
		}
		;
		
		function toHex(number) {
			var str = number.toString(16);
			return ((str.length < 2) ? '0' + str : str).toUpperCase();
		}
		;
		try {
			var playerId = 'player';
			var data = spec.data[0], d = new Date(); /*fileType = getFileType(data['streamfileids'])*/
			var allUrl = {};
			for(var fileType in data['streamfileids']) {
				var fileid = getFileID(data['streamfileids'][fileType], data['seed']);
				var rand1 = 1000 + parseInt(Math.random() * 999);
				var rand2 = 1000 + parseInt(Math.random() * 9000);
				var sid = d.getTime() + '' + rand1 + '' + rand2;
				var first = '';
				var pathType = fileType == 'mp4' ? 'mp4' : 'flv';
				var playList = [];
				var videoSeconds = 0;
				for (var i = 0, len = (data['segs'][fileType]).length; i < len; i++) {
					var k = data['segs'][fileType][i]['k'],
							url = 'http://f.youku.com/player/getFlvPath/sid/' +
							sid + '_' + toHex(i) + '/st/' + pathType + '/fileid/' +
							fileid.substr(0, 8) + toHex(i) + fileid.substr(10, fileid.length - 2) + '?start=0&K=' + k + '&hd=2&myp=0&ts=185&ypp=0';
					videoSeconds += parseInt(data['segs'][fileType][i]['seconds']);
					var seq = {};
					seq['t'] = parseInt(data['segs'][fileType][i]['seconds']);
					seq['url'] = url;
					seq['autoBuffering'] = true;
					playList.push(seq);

				}
				allUrl[fileType] = playList;
			}
			var script = document.createElement('script');
			script.textContent = '(' + createFlowPlayer.toString() + ')("player",' + JSON.stringify(allUrl) + ',' + videoSeconds + ');';
			document.body.appendChild(script);
		} catch (e) {
			console.log(e);
		}
	}
	;
	function getYouku(vcode) {
		var url = 'http://v.youku.com/player/getPlayList/VideoIDS/' + vcode + '/timezone/+08/version/5/source/out/Sc/2?password=&ran=9777&n=3';
		GM_xmlhttpRequest({
			method: 'GET',
			url: url,
			synchronous: true,
			onload: function(response) {
				try {
					var re = JSON.parse(response.responseText);
					F2MgetYoukuURL(re);
				} catch (e) {
					console.log(e);
				}
			},
			onerror: function(e) {
				console.log(e);
			},
			ontimeout: function(e) {
				console.log(e);
			},
			onreadystatechange: function(e) {

			}
		});
	}
	function getTudou(vcode) {
		return;
		var videosegs = JSON.parse(document.getElementById('__flash2mplayer').getAttribute('segs'));
		if (typeof videosegs['5'] !== 'undefined') {
			var seg = videosegs['5'];
		} else if (typeof videosegs['3'] !== 'undefined') {
			var seg = videosegs['3'];
		} else {
			var seg = videosegs['2'];
		}

		var playlist = [];
		var len = seg.length;
		var count = 0;
		var seconds = 0;

		for (var i in seg) {
			var url = 'http://v2.tudou.com/f?sender=pepper&v=4.2.2&sj=1&id=' + seg[i]['k'] + '&sid=11000&hd=5&r=0';

			seconds = seconds + seg[i]['seconds'];
			request = GM_xmlhttpRequest({
				idx: i,
				method: 'GET',
				seconds: seconds,
				url: url,
				onload: function(response) {
					var tmp = document.createElement('span');
					var re = response.responseText.split('>') [1].split('<') [0];
					tmp.innerHTML = re;
					var index = seg[this.idx]['no'];
					playlist[index] = {};
					playlist[index]['url'] = tmp.textContent;
					playlist[index]['t'] = Math.round(this.seconds / 1000);
					count++;
					if (count == len) {
						var script = document.createElement('script');
						script.textContent = 'createFlowPlayer("player",' + JSON.stringify(playlist) + ',' + Math.round(seconds / 1000) + ');';
						document.body.appendChild(script);
					}
					delete tmp;
				}
			});
		}
	}
	function requestVideoUrl() {
		var vcode = document.getElementById('__flash2mplayer').getAttribute('vcode');
		if (!vcode) {
			if (count > max)
				return;
			count++;
			setTimeout(requestVideoUrl, 1000);
			return;
		}
		var tudoduiid = document.getElementById('__flash2mplayer').getAttribute('tudouiid');
		if (tudoduiid || (vcode && document.domain == 'tudou.com')) {
			getTudou(vcode);
		} else {
			getYouku(vcode);
		}
	}
	function createSetVideoScript() {
		var script = document.createElement('script');
		script.id = '__flash2mplayer';
		script.textContent = '(' + setVideoId.toString() + ')();';
		document.body.appendChild(script);
	}
	function run() {
		if (window.top != window)
			return true;
		createPlayerJs();
		createSetVideoScript();
		requestVideoUrl();
	}
	try {
		run();
	} catch (e) {
		console.log(e);
	}
})(window);
