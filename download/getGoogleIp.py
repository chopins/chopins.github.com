import os.path
#!/bin/env python

import threading,os
import socket
import struct 
import ssl
import pprint
import sys
import time
import signal
from multiprocessing.dummy import Pool as ThreadPool 


cur = os.getcwd()
storedir = "%s/dnsdatapy/" % cur
useOpenSSL = False
if sys.version_info.major == 2 and sys.version_info.micro < 9:
    try:
        import OpenSSL
        useOpenSSL = True
    except ImportError:
        print('Your python version less than 2.7.9,need OpenSSL or upgrade your python')
        sys.exit()
    

def process_term(a,b):
    print('exit')
    sys.exit()

def exec_cmd():
    cmd = 'nslookup -q=txt _netblocks.google.com 8.8.8.8'
    try:
        import subprocess
        p = subprocess.Popen(cmd,shell=True,stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
        ret = p.stdout.readlines()
        p.wait()
    except ImportError:
        ret = os.popen(cmd).readlines()
    txt=ret[4].decode();
    txt = txt.split('"')
    return txt[1]
  
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
        except:
            answers = exec_cmd()

    block = answers.split()
    block.pop(0)
    block.pop()
    return block

def checkstroedir():
    global storedir
    if(os.path.isdir(storedir)):
        pass
    else:
        os.mkdir(storedir)

def write_ip_txt():
    f = "%siplist.txt" % storedir
    fs = open(f, 'w')
    return fs

def zone_default_conf():
    date = time.strftime('%Y%m%d%H')
    return  '''$TTL    86400
@ 1D IN SOA @ root ( %s  7200 36000 604800  86400 )
@ 1D IN NS         ns1.google.com
@ 1D IN NS         ns2.google.com
@ 1D IN NS         ns3.google.com
@ 1D IN NS         ns4.google.com
''' % date

def google_ns_ip():
    date = time.strftime('%Y%m%d%H')
    return '''$TTL    86400
@ 1D IN SOA @ root ( %s  7200 36000 604800  86400 )
@ 1D IN NS         ns1
@ 1D IN NS         ns2
@ 1D IN NS         ns3
@ 1D IN NS         ns4

ns1 IN A 216.239.32.10
ns2 IN A 216.239.34.10
ns3 IN A 216.239.36.10
ns4 IN A 216.239.38.10

''' % date

def writer_default(domain):
    f = "%s%s.zone" % (storedir,domain)
    fs = open(f, 'w')
    fs.write(zone_default_conf())
    return fs

def write_google_default():
    f = "%sgoogle.com.zone" % storedir
    fs = open(f, 'w')
    fs.write(google_ns_ip())
    return fs

def init_write_zone():
    global ipfs,gfs,gsfs,gufs,ybfs,ggfs,cgfs,gafs,apfs,gvfs,ygfs
    ipfs = write_ip_txt()
    gfs = write_google_default()
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
    ip = socket.inet_ntoa(struct.pack("!L", ip))
    return ip

def getIpRange(iptxt):
    (ip_net, maskbit) = iptxt.split('/');
    start = ip2long(ip_net) + 1
    maskbit = int(maskbit)
    maxip = start | (4294967295 >> maskbit);
    startip = long2ip(start);
    endip = long2ip(maxip - 1);
    return (startip,endip,start,maxip)

def sslconnect(hostip):
    conn = ssl.wrap_socket(socket.socket(socket.AF_INET),
                            ssl_version= ssl.PROTOCOL_TLSv1,
                            cert_reqs=ssl.CERT_REQUIRED,
                            ca_certs="/etc/ssl/certs/ca-bundle.trust.crt")
    try:                        
        conn.connect((hostip, 443))
    except:
        return
    
    cainfo = conn.getpeercert();
    if not cainfo:
        return
    pprint.pprint(cainfo)
    notAfter = time.mktime(time.strptime(cainfo['notAfter'],'%b %d %H:%M:%S %Y GMT'))
    if time.time() > notAfter:
        return
    if 'subject' not in cainfo.keys():
        return
    if 'subjectAltName' not in cainfo.keys():
        return
    if not check_organization(cainfo['subject']):
        return
    subjectAltName = []
    for dns in cainfo['subjectAltName']:
        subjectAltName.append(dns[1])
    
    
    print(subjectAltName)
    
def openssl_check(hostip):
    try:
        cert = ssl.get_server_certificate((hostip,443), ssl_version=ssl.PROTOCOL_TLSv1, ca_certs="/etc/ssl/certs/ca-bundle.trust.crt")
        x509 = OpenSSL.crypto.load_certificate(OpenSSL.crypto.FILETYPE_PEM,cert)
        print(x509.get_serial_number())
        print(x509.get_extension('subjectAltName'))
        print(x509.get_notAfter())
    except :
        pass
   

def checkCert(hostip):
    global useOpenSSL
    socket.setdefaulttimeout(3)
    if useOpenSSL:
        openssl_check(hostip)
    else:
        sslconnect(hostip)
        
    

def check_organization(subject):
    for item  in subject:
        for i in item:
            if i[0] == 'organizationName' and i[1] == 'Google Inc':
                return True
 
class connetThread(threading.Thread):
    def __init__(self,startip,maxip):
        threading.Thread.__init__(self) 
        self.startip = startip
        self.maxip = maxip
        self.setDaemon(True)
        self.start()

    def run(self):
        connip = self.startip
        iplist = []
        pool = ThreadPool(10)
        while connip < self.maxip:
            host = long2ip(connip)
            
            iplist.append(host)
            if len(iplist) >=10:
                pool.map(checkCert,iplist)
                pool.close()
                pool.join()
                pool = ThreadPool(10)
            
            connip = connip + 1

def closefs():
    global ipfs,gfs,gsfs,gufs,ybfs,ggfs,cgfs,gafs,apfs,gvfs,ygfs
    ipfs.close()
    gfs.close()
    gsfs.close()
    gufs.close()
    ybfs.close()
    ggfs.close()
    cgfs.close()
    gafs.close()
    apfs.close()
    gvfs.close()
    ygfs.close()

def getGoogleIp():
    global ipfs,gfs,gsfs,gufs,ybfs,ggfs,cgfs,gafs,apfs,gvfs,ygfs
   
    blocklist = getnetblocks()
    
    checkstroedir()
    init_write_zone()
    tlen = 0
    for iptxt in blocklist:
        iptxt = iptxt.split('ip4:')[1]
        (startip,endip,start,maxip) = getIpRange(iptxt)
        str = "IP BLOCK:{%s} IN { {%s} -- {%s} }" % (iptxt,startip,endip);
        ipfs.write(str)
        
        while(tlen >50):
            time.sleep(1)
            tlen = threading.active_count()
            print(tlen)

        connetThread(start,maxip)

    tlen = threading.active_count()
    signal.signal(signal.SIGINT, process_term)  
    while(tlen >0):
        time.sleep(1)
        tlen = threading.active_count()
    
    closefs()

if __name__ ==  "__main__":
    getGoogleIp()
