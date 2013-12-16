// ==UserScript==
// @name           Flash.VLC.Player
// @namespace      youku.todou.vlc.player
// @description      Flash.VLC.Player
// @version         1
// @match          http://v.youku.com/v_show/*
// @match          http://www.tudou.com/*
// @grant           GM_xmlhttpRequest
// ==/UserScript==

(function() {
	function Flash2Mplayer(global) {
		replacePlayer();
		global.playIndex= 0;
		global.playlist = [];
		global.videoSeconds = 0;
		global.seqsPlayTime = 0;
		function replacePlayer() {
			if (typeof videoId2 !== 'undefined') {
				var videoId = videoId2;
			} else if (typeof vcode !== 'undefined') {
				var videoId = window.vcode;
			} else if (typeof iid !== 'undefined') {
				var videoId = window.iid;
				document.getElementById('__flash2vlc').setAttribute('tudouiid', 1);
                document.getElementById('__flash2vlc').setAttribute('segs', pageConfig.segs.toString());

			} else if (typeof pageConfig !== 'undefined' && typeof pageConfig.iid !== 'undefined') {
				var videoId = pageConfig.iid;
				document.getElementById('__flash2vlc').setAttribute('tudouiid', 1);
                document.getElementById('__flash2vlc').setAttribute('segs', pageConfig.segs);
			} else {
				//console.log('No Video Id');
				return;
			}
			document.getElementById('__flash2vlc').setAttribute('vcode', videoId);
			//getNode('player').innerHTML = 'Loading Mplayer...';
		};
		global.farmatTime = function farmatTime(sec) {
		    var s = sec %60;
			s = s >9 ? s : '0'+s;
			var m = Math.round(sec/60);
			m = m >9 ? m : '0'+m;
			return m+':'+ s;
		};
		global.getNode = function getNode(id) {
			return document.getElementById(id);
		};
		global.F2McallbackGetData = function F2McallbackGetData(re, seconds) {
			if (typeof vcode !== 'undefined' || document.domain == 'youku.com') {	
				return F2MgetYoukuURL(re);
			}
			global.playlist = re;
			global.videoSeconds = seconds;
			createPlayer(re[0]);
		};
		global.createPlayer = function createPlayer(url) {
			var w = getNode('player').offsetWidth;
			var t = farmatTime(global.videoSeconds);
			getNode('player').setAttribute('style','background-color:#EEE;');
			getNode('player').innerHTML = '<embed type="application/x-mplayer2" id="mplayer"'
					+ 'name="video2" width="'+w+'" height="500" src="'+url+'"'
					+ 'onMediaComplete="playComplete();" showlogo="true"/>'
					+ '<style type="text/css">'
					+ '#videoInfo span {display:inline-block;margin-left:10px;font-weight:bold;color:#000;}'
					+ '</style>'
					+ '<div id="videoInfo"><span id="curIdx">1/'+global.playlist.length+'</span>'
					+ '<span id="videoTime">00:00/'+t+'</span>'
					+ '</div><div id="playerPlaceholder"></div>';
			global.playIndex = 0;
			
			global.player = getNode('mplayer');	
			global.timeUpdate = function timeUpdate(){
				var nt = Math.round(global.seqsPlayTime+global.player.getTime());
				getNode('videoTime').innerHTML = farmatTime(nt)+'/'+t;
				setTimeout(timeUpdate,1000);
			};
			timeUpdate();
			return global.player;
		};
		global.playComplete = function playComplete(e) {
			var nextIndex = global.playIndex + 1;
			if( nextIndex < global.playlist.length) {
				global.playIndex = nextIndex;
				nextIndex++;
				global.seqsPlayTime += global.player.getTime();
				getNode('curIdx').innerHTML = nextIndex+'/'+global.playlist.length;
				getNode('mplayer').setAttribute('src',global.playlist[nextIndex]);
				getNode('mplayer').Play();
				return;
			}
		};
		global.F2MgetYoukuURL = function F2MgetYoukuURL(spec) {
			var data = spec.data[0],d = new Date(),fileType = getFileType(data['streamfileids']);
			var fileid = getFileID(data['streamfileids'][fileType], data['seed']);
			var rand1 = 1000 + parseInt(Math.random() * 999);
			var rand2 = 1000 + parseInt(Math.random() * 9000);
			var sid = d.getTime() + '' + rand1 + '' + rand2;
			var first = '';
			for (var i = 0, len = (data['segs'][fileType]).length; i < len; i++) {
				var k = data['segs'][fileType][i]['k'],
						url = 'http://f.youku.com/player/getFlvPath/sid/' +
						sid + '_' + toHex(i) + '/st/flv/fileid/' +
						fileid.substr(0, 8) + toHex(i) + fileid.substr(10, fileid.length - 2) + '?start=0&K=' + k + '&hd=2&myp=0&ts=185&ypp=0';

				global.videoSeconds += parseInt(data['segs'][fileType][i]['seconds']);
				global.playlist.push(url);
				if(i == 0) {
					first = url
				}
			}
			createPlayer(first);
		};
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
		};
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
		};
		function getFileType(obj) {
			var keys = Object.keys(obj);
			var type = [
				'hd2',
				'mp4',
				'flv'
			].filter(function(item) {
				return !!(keys.indexOf(item) + 1);
			});
			return type[0] || 'flv';
		};
		function toHex(number) {
			var str = number.toString(16);
			return (str.length < 2) ? '0' + str : str;
		};
	};
	
	function run(callback) {
	    if(window.top != window) return;
		var script = document.createElement('script');
		script.id = '__flash2vlc';
		script.textContent = '(' + callback.toString() + ')(window);';
		document.body.appendChild(script);
		
		var conunt = 0,max = 6;
		function request() {
			var vcode = document.getElementById('__flash2vlc').getAttribute('vcode');
			
			if (!vcode) {
				if (count > max)
					return;
				count++;
				setTimeout(request, 1000);
				return;
			}
			var tudoduiid = document.getElementById('__flash2vlc').getAttribute('tudouiid');

			if (tudoduiid) {
                var videosegs = JSON.parse(document.getElementById('__flash2vlc').getAttribute('segs'));
				if(typeof videosegs['5'] !== 'undefined') {
					var seg = videosegs['5'];
				} else if(typeof videosegs['3'] !== 'undefined') {
					var seg = videosegs['3'];
				} else {
					var seg = videosegs['2'];
				}
				
				var playlist = [];
				var len = seg.length;
				var count = 0;
				var seconds = 0;
				for(var i in seg) {
					var url ='http://v2.tudou.com/f?sender=pepper&v=4.2.2&sj=1&id=' + seg[i]['k'] + '&sid=11000&hd=5&r=0';
					seconds = seconds+ seg[i]['seconds'];
					request = GM_xmlhttpRequest({
						idx : i,
						method: 'GET',
						url: url,
						onload: function(response) {
							var tmp = document.createElement('span');
							var re = response.responseText.split('>') [1].split('<') [0];
							tmp.innerHTML = re;
							var index = seg[this.idx]['no'];
							playlist[index] = tmp.textContent;
							count++;
							if(count== len) {
								var script = document.createElement('script');
								script.textContent = 'F2McallbackGetData(' + JSON.stringify(playlist) + ','+Math.round(seconds/1000)+');';
								document.body.appendChild(script);
							}
							delete tmp;
						}
					});
				}
				
				return;
			} else {
				var url = 'http://v.youku.com/player/getPlayList/VideoIDS/' + vcode + '/timezone/+08/version/5/source/out/Sc/2?password=&ran=9777&n=3';
			}
			GM_xmlhttpRequest({
				method: 'GET',
				url: url,
				onload: function(response) {
					var re = response.responseText;
					var script = document.createElement('script');
					script.textContent = 'F2McallbackGetData(' + re + ',0);';
					document.body.appendChild(script);
				}
			});
		}
		setTimeout(request, 1000);
		
	}
	;
	run(Flash2Mplayer);
})();
