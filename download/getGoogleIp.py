import os.path
#!/bin/env python

import thread,os
import socket
import struct 
import ssl
 
socket.setdefaulttimeout(2)

cur = os.getcwd()
storedir = "%s/dnsdatapy/" % cur

def checkip(threadName, delay):
    
    pass

def exec_cmd():
    cmd = 'nslookup -q=txt _netblocks.google.com 8.8.8.8'
    try:
        import subprocess
        p = subprocess.Popen(cmd,shell=True,stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        ret = p.stdout.readlines()
        p.wait()
    except ImportError:
        ret = os.popen(cmd).readlines()
    txt=ret[4].split('"')[1]
    return txt

def getnetblocks():
    dnspython = False
    try:
        import dns.resolver
        dnspython = True
    except ImportError:
        answers = exec_cmd()

    if dnspython:
        try:
            answers = dns.resolver.query('_netblocks.google.com','TXT')
            for rd in answers:
                answers = rd.to_text()
        except dns.resolver.NoAnswer:
            answers = exec_cmd()

    block = answers.split()
    block.pop(0)
    block.pop()
    return block

def checkstroedir():
    
    if(os.path.isdir(storedir)):
        pass
    else:
        os.mkdir(storedir)

def write_ip_txt():
    f = "%siplist.txt" % storedir
    fs = open(f, 'w')
    return fs

def zone_default_conf():
    return  '''
$TTL    86400
@       1D IN SOA @ root (
                          42          ; serial (d. adams)
                          3H          ; refresh 
                          15M        ; retry      
                          1W         ; expiry  
                          1D )        ; minimum   
                        1D IN NS         ns1.google.com
                        1D IN NS         ns2.google.com
                        1D IN NS         ns3.google.com
                        1D IN NS         ns4.google.com
'''

def google_ns_ip():
    return '''

ns1 IN A 216.239.32.10
ns2 IN A 216.239.34.10
ns3 IN A 216.239.36.10
ns4 IN A 216.239.38.10

'''
def writer_default(domain):
    f = "%s%s.zone" % (storedir,domain)
    fs = open(f, 'w')
    fs.write(zone_default_conf())
    return fs

def init_write_zone():
    global ipfs,gfs,gsfs,gufs,ybfs,ggfs,cgfs,gafs,apfs,gvfs,ygfs
    ipfs = write_ip_txt()
    gfs = writer_default('google.com')
    gfs.write(google_ns_ip());
    gsfs = writer_default('gstatic.com')
    gufs = writer_default('googleusercontent.com')
    ybfs = writer_default('youtube.com')
    ggfs = writer_default('ggpht.com')
    cgfs = writer_default('clients.google.com')    
    gafs = writer_default('googleapis.com')
    apfs = writer_default('appspot.com')
    gvfs = writer_default('googlevideo.com')
    ygfs = writer_default('ytimg.com')

def ip2long(ipstr):
    return struct.unpack("!I", socket.inet_aton(ipstr))[0]

def long2ip(ip):
    return socket.inet_ntoa(struct.pack("!I", ip))

def getIpRange(iptxt):
    (ip_net, maskbit) = iptxt.split('/');
    start = ip2long(ip_net) + 1
    maskbit = int(maskbit)
    maxip = start | (4294967295 >> maskbit);
    startip = long2ip(start);
    endip = long2ip(maxip - 1);
    return (startip,endip,start,maxip)

def connect_network(start,maxip):
    connip = start
    while connip < maxip:
        host = long2ip(connip)
        try:
            ca = ssl.get_server_certificate((host, 443))
            print ca
        except:
            pass
        connip = connip + 1

def getGoogleIp():
    global ipfs,gfs,gsfs,gufs,ybfs,ggfs,cgfs,gafs,apfs,gvfs,ygfs
    blocklist = getnetblocks()
    checkstroedir()
    init_write_zone()
    
    for iptxt in blocklist:
        iptxt = iptxt.split('ip4:')[1]
        (startip,endip,start,maxip) = getIpRange(iptxt)
        str = "IP BLOCK:{%s} IN { {%s} -- {%s} }" % (iptxt,startip,endip);
        ipfs.write(str)
        try:
            thread.start_new_thread(connect_network,(start,maxip))
        except:
            pass
        

if __name__ ==  "__main__":
    getGoogleIp()
