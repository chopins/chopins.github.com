/*
get the browser fingerprint string

@param text string
@param basetext string
@return string length 30 string or 0
*/
function getfingerprint(text,basetext) {
    try {
        var canvas = document.createElement('canvas');
        var ctx = canvas.getContext('2d');
        var txt = text;
        ctx.textBaseline = "top";
        ctx.font = "14px 'Arial'";
        ctx.textBaseline = basetext;
        ctx.fillStyle = "#f60";
        ctx.fillRect(125,1,62,20);
        ctx.fillStyle = "#069";
        ctx.fillText(txt, 2, 15);
        ctx.fillStyle = "rgba(102, 204, 0, 0.7)";
        ctx.fillText(txt, 4, 17);
        var b64 = canvas.toDataURL().replace("data:image/png;base64,","");
        var bin = atob(b64);
        var crc = encodeURIComponent(bin.slice(-23,-12)).replace(/%/g,'');
        return crc;
    } catch(e) {
        return 0;
    }
}
