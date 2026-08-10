const SOCKS = [
    "addons.mozilla.org",
    "godaddy.com",
    "google.com",
    "google.com.sg",
    "gstatic.com",
    "github.com",
    "githubassets.com",
    "githubusercontent.com",
    "elastic.co",
    "softpedia.com",
    "steampowered.com",
    "wikipedia.org",
    "wikimedia.org",
];


function FindProxyForURL(url, host) {
    for(let domain of SOCKS) {
        if(dnsDomainIs(host, domain)) {
            return "SOCKS 127.0.0.1:5520; DIRECT";
        }
    }
    return "DIRECT";
}
