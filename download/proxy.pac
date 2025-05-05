const SOCKS = [
    "addons.mozilla.org"
];


function FindProxyForURL(url, host) {
    for(let domain of SOCKS) {
        if(dnsDomainIs(host, domain)) {
            return "SOCKS 127.0.0.1:5520";
        }
    }
    return "DIRECT";
}
