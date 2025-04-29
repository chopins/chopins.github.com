const PROXY = [
    "github.com"
];


function FindProxyForURL(url, host) {
    for(let domain of PROXY) {
        if(dnsDomainIs(host, domain)) {
            return "PROXY 127.0.0.1:5520";
        }
    }
    return "DIRECT";
}
