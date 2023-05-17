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
import math

class GlobalValue:
    cur = os.getcwd()
    storedir = "%s/dnsdatapy/" % cur
    useOpenSSL = False
    check_num = 0
    count_ip_num = 0
    complete = 0
    mutex = threading.Lock()
    fs_ip = None
    fs_g = None
    fs_gs = None
    fs_gu = None
    fs_yb = None
    fs_gg = None
    fs_cg = None
    fs_ga = None
    fs_ap = None
    fs_gv = None
    fs_yg = None
    fs_ban = None
    fs_banip_list = []
    def __init__(self):
        pass

if sys.version_info.major == 2 and sys.version_info.micro < 9:
    try:
        import OpenSSL
        GlobalValue.useOpenSSL = True
        if OpenSSL.__version__.split('.')[1] < 12:
            print('Your pyOpenSSL version less than 0.12, update to higher version')
            sys.exit()
    except ImportError:
        print('Your python version less than 2.7.9,need OpenSSL or upgrade your python')
        sys.exit()
else:
    print('use python ssl')

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
    if(os.path.isdir(GlobalValue.storedir)):
        pass
    else:
        os.mkdir(storedir)

def write_ip_txt():
    f = "%siplist.txt" % GlobalValue.storedir
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
    f = "%s%s.zone" % (GlobalValue.storedir,domain)
    fs = open(f, 'w')
    fs.write(zone_default_conf())
    fs.flush()
    return fs

def write_google_default():
    f = "%sgoogle.com.zone" % GlobalValue.storedir
    fs = open(f, 'w')
    fs.write(google_ns_ip())
    fs.flush()
    return fs

def init_write_zone():
    GlobalValue.fs_ip = write_ip_txt()
    GlobalValue.fs_g = write_google_default()
    GlobalValue.fs_gs = writer_default('gstatic.com')
    GlobalValue.fs_gu = writer_default('googleusercontent.com')
    GlobalValue.fs_yb = writer_default('youtube.com')
    GlobalValue.fs_gg = writer_default('ggpht.com')
    GlobalValue.fs_cg = writer_default('clients.google.com')    
    GlobalValue.fs_ga = writer_default('googleapis.com')
    GlobalValue.fs_ap = writer_default('appspot.com')
    GlobalValue.fs_gv = writer_default('googlevideo.com')
    GlobalValue.fs_yg = writer_default('ytimg.com')
    f = "%sip_ban.list" % GlobalValue.storedir
    GlobalValue.fs_ban = open(f,'a+')
    for line in GlobalValue.fs_ban:
        GlobalValue.fs_banip_list.append(line.strip())

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
    
    notAfter = time.mktime(time.strptime(cainfo['notAfter'],'%b %d %H:%M:%S %Y GMT'))
    if time.time() > notAfter:
        return
    notBefore = time.mktime(time.strptime(cainfo['notBefore'],'%b %d %H:%M:%S %Y GMT'))
    if(time.time() < notBefore):
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
    
    serial_number = str(int(cainfo['serialNumber'],16))
    return (serial_number,subjectAltName)
    
def openssl_check(hostip):
    try:
        cert = ssl.get_server_certificate((hostip,443), ssl_version=ssl.PROTOCOL_TLSv1, ca_certs="/etc/ssl/certs/ca-bundle.trust.crt")
        x509 = OpenSSL.crypto.load_certificate(OpenSSL.crypto.FILETYPE_PEM,cert)
        
        notAfter = time.mktime(time.strptime(x509.get_notAfter(),'%Y%m%d%H%M%SZ'))
        if time.time() > notAfter:
            return
        notBefore = time.mktime(time.strptime(x509.get_notBefore(),'%Y%m%d%H%M%SZ'))
        if time.time() < notBefore:
            return
        
        serial_number = str(x509.get_serial_number())
        subject = x509.get_subject()
        if not check_organization(subject):
            return
        extcount = x509.get_extension_count()
        subjectAltName = None
        for i in range(0,extcount):
            x509ext = x509.get_extension(i)
            if x509ext.get_short_name() == 'subjectAltName':
                subjectAltName = str(x509ext)

        if not subjectAltName:
            return
        
        subjectAltName = map((lambda x:x.strip().rstrip(',')),subjectAltName.split('DNS:')[1:])
        return (serial_number,subjectAltName)
        
    except :
        pass

class GenerateRecord:
    def __init__(self, cert_info, hostip):
        self.serial_number = cert_info[0]
        self.subjectAltName = cert_info[1]
        self.hostip = hostip
        self.skip = False

    def writer_a_reocrd(self, fp, domain, record='*', serial_number = None):
        if self.skip:
            self.skip = False
            return
        
        if not serial_number and domain in self.subjectAltName:
                return fp.write("%s IN A %s\n" % (record,self.hostip))
            
        if domain in self.subjectAltName and self.serial_number == serial_number:
            fp.write("%s IN A %s\n" % (record, self.hostip))
  
    def checkip(self,domain):
        conn = ssl.wrap_socket(socket.socket(socket.AF_INET),
                        ssl_version= ssl.PROTOCOL_TLSv1,
                        cert_reqs=ssl.CERT_REQUIRED,
                        ca_certs="/etc/ssl/certs/ca-bundle.trust.crt")
        try:
            conn.connect((self.hostip, 443))
        except:
            self.skip = True
            return
        
        conn.sendall("GET / HTTP/1.1\r\nHost: %s\r\nUser-Agent: Mozilla/5.0 (Gecko Firefox)\r\nConnection: close\r\n\r\n" % domain)
        try:
            ret = conn.recv(12)
        except:
            self.skip = True
            return
 
        if ret.split()[1] in ('200','301','302'):
            self.skip = False
        else:
            self.skip = True
            

        conn.close()
    
    def check_ip_domain(self, fp, check_domain, domain = None, serial_number = None):
        if not domain:
            domain = check_domain

        if domain not in self.subjectAltName:
            return

        domain_part = check_domain.split('.')
        if len(domain_part) == 2:
            record = '@'
        else:
            record = domain_part[0]

        self.checkip(check_domain)
        self.writer_a_reocrd(fp, domain, record, serial_number)
    
    def check_ip_list(self, fp, pre, start, end, check_suffix, domain, key = None):
        for i in range(start,end):
            check_domain = "%s%d%s" % (pre, i, check_suffix)
            self.check_ip_domain(fp, check_domain, domain, key)

def checkCert(hostip):
    try:
        GlobalValue.mutex.acquire()
        GlobalValue.check_num = GlobalValue.check_num + 1
        GlobalValue.mutex.release()
        if(GlobalValue.count_ip_num > 0):
            curr = math.floor(float(GlobalValue.check_num) / float(GlobalValue.count_ip_num) * 100)
            if curr > GlobalValue.complete:
                GlobalValue.complete = curr
                print('Complete:%d%%' % GlobalValue.complete)

        socket.setdefaulttimeout(3)
        if GlobalValue.useOpenSSL:
            cert_info = openssl_check(hostip)
        else:
            cert_info = sslconnect(hostip)

        if not cert_info:
            GlobalValue.fs_ban.write("%s\n" % hostip)
            GlobalValue.fs_ban.flush()
            return

        domain_str = str(cert_info[1])

        GlobalValue.fs_ip.write("IP %s Valid Domain: %s\n" % (hostip, domain_str))
        GlobalValue.fs_ip.flush()
        gr = GenerateRecord(cert_info, hostip)

        gcomm_sn = '8924155373108256736'

        gr.writer_a_reocrd(GlobalValue.fs_g, 'google.com', '@',gcomm_sn)
        gr.check_ip_domain(GlobalValue.fs_g, 'www.google.com', None,'3560456681580786076')
        gmsn = '7810596706032786260'
        dmlist = ('mail.google.com','inbox.google.com')
        for d in dmlist:
            gr.check_ip_domain(GlobalValue.fs_g,d, None, gmsn)

        gr.check_ip_domain(GlobalValue.fs_g, 'accounts.google.com', None, '3059601525857341613')
        gr.check_ip_domain(GlobalValue.fs_g, 'm.google.com', None, '7422046862831836265')
        gr.check_ip_domain(GlobalValue.fs_g, 'checkout.google.com', None, '2412261313296579961')
        comm_domain_list = ('talk.google.com','plus.google.com','play.google.com',
                            'id.google.com','groups.google.com','images.google.com',
                            'code.google.com','map.google.com','maps.google.com',
                            'news.google.com','upload.google.com','drive.google.com',
                            'encrypted.google.com','translate.google.com')
        for d in comm_domain_list:
            gr.check_ip_domain(GlobalValue.fs_g, d, '*.google.com', gcomm_sn)

        gr.check_ip_list(GlobalValue.fs_g,'encrypted-tbn', 0, 3, '.google.com', '*.google.com',gcomm_sn)
        gr.check_ip_list(GlobalValue.fs_g,'drive', 0, 9, '.google.com', '*.google.com',gcomm_sn)
        gr.writer_a_reocrd(GlobalValue.fs_g, '*.google.com', '*', gcomm_sn)
        gfs.flush()
        gr.writer_a_reocrd(GlobalValue.fs_cg, '*.clients.google.com', '*')

        gssn = '4629298540228774186'
        gr.writer_a_reocrd(GlobalValue.fs_gs, 'gstatic.com', '@', gssn)
        gs_domain_list = ('fonts.gstatic.com','csi.gstatic.com','maps.gstatic.com','www.gstatic.com','ssl.gstatic.com')
        for gd in gs_domain_list:
            gr.check_ip_domain(GlobalValue.fs_gs, gd, '*.gstatic.com', gssn)

        gr.check_ip_list(GlobalValue.fs_gs, 'g', 0, 3, '.gstatic.com', '*.gstatic.com', gssn)
        gr.check_ip_list(GlobalValue.fs_gs, 'mt', 0, 7, '.gstatic.com', '*.gstatic.com', gssn)
        gr.check_ip_list(GlobalValue.fs_gs, 't', 0, 3, '.gstatic.com', '*.gstatic.com', gssn)
        gr.check_ip_list(GlobalValue.fs_gs, 'encrypted-tbn', 0, 3, '.gstatic.com', '*.gstatic.com', gssn)
        gr.writer_a_reocrd(GlobalValue.fs_gs, '*.gstatic.com', '*', gssn)
        gsfs.flush()
        gr.writer_a_reocrd(GlobalValue.fs_gu, 'googleusercontent.com')
        gr.writer_a_reocrd(GlobalValue.fs_gu, '*.googleusercontent.com', '*', '1423458468341525840')
        gufs.flush()
        gr.writer_a_reocrd(GlobalValue.fs_yb, 'youtube.com')
        ybsn = '8924155373108256736'
        yb_domain_list = ('www.youtube.com','accounts.youtube.com','help.youtube.com','m.youtube.com','insight.youtube.com')
        for ybd in yb_domain_list:
            gr.check_ip_domain(GlobalValue.fs_yb, ybd, '*.youtube.comm', ybsn)
        gr.writer_a_reocrd(GlobalValue.fs_yb, '*.youtube.com', '*', ybsn)
        ybfs.flush()
        ggsn = '1423458468341525840'
        gr.writer_a_reocrd(GlobalValue.fs_gg, 'ggpht.com', '@')
        gr.check_ip_list(GlobalValue.fs_gg, 'lh', 3, 6, '.ggpht.com', '*.ggpht.com', ggsn)
        gr.check_ip_list(GlobalValue.fs_gg, 'gm', 1, 4, '.ggpht.com', '*.ggpht.com', ggsn)
        gr.check_ip_list(GlobalValue.fs_gg, 'geo', 1, 3, '.ggpht.com', '*.ggpht.com', ggsn)
        gr.writer_a_reocrd(GlobalValue.fs_gg, '*.ggpht.com', '*', gssn)
        ggfs.flush()
        gapi_sn = '9025913279998864902'
        gapi_domain = ('ajax.googleapis.com','fonts.googleapis.com','chart.googleapis.com',
                        'maps.googleapis.com','wwww.googleapis.com','play.googleapis.com',
                        'translate.googleapis.com','youtube.googleapis.com','content.googleapis.com',
                        'bigcache.googleapis.com','storage.googleapis.com','android.googleapis.com',
                        'redirector-bigcache.googleapis.com','commondatastorage.googleapis.com','bigcache.googleapis.com')
        for gapid in gapi_domain:
            gr.check_ip_domain(GlobalValue.fs_ga, gapid, '*.googleapis.com', gapi_sn)

        gr.check_ip_list(GlobalValue.fs_ga, 'mt', 0, 3, '.googleapis.com', '*.googleapis.com', gapi_sn)
        gafs.flush()
        gr.writer_a_reocrd(GlobalValue.fs_ga, '*.googleapis.com')
        gr.writer_a_reocrd(GlobalValue.fs_ap, '*.appspot.com')
        gr.writer_a_reocrd(GlobalValue.fs_gv, '*.googlevideo.com')
        gvfs.flush()
        gr.writer_a_reocrd(GlobalValue.fs_yg, 'ytimg.com')
        gr.check_ip_domain(GlobalValue.fs_yg, 'i.ytimg.com', '*.ytimg.com', );
        gr.check_ip_list(GlobalValue.fs_yg, 'i', 1, 4, '.ytimg.com', '*.ytimg.com', gcomm_sn);
        gr.check_ip_domain(GlobalValue.fs_yg, 's.ytimg.com', '*.ytimg.com', gcomm_sn);
        gr.writer_a_reocrd(GlobalValue.fs_yg, '*.ytimg.com', '*', gcomm_sn);
        ygfs.flush()
    except:
        print(sys.exc_info())

def check_organization(subject):
    if useOpenSSL and subject.organizationName == 'Google Inc':
        return True
    else:
        for item  in subject:
            for i in item:
                if i[0] == 'organizationName' and i[1] == 'Google Inc':
                    return True

def in_ban_ip(ip):
    if ip in GlobalValue.fs_banip_list:
        return True
    return False

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
        pool = ThreadPool()
        while connip < self.maxip:
            host = long2ip(connip)
            if not in_ban_ip(host):
                iplist.append(host)
                if len(iplist) >=100:
                    pool.map(checkCert,iplist)
                    pool.close()
                    pool.join()
                    iplist = []
                    pool = ThreadPool()

            connip = connip + 1

def closefs():
    GlobalValue.fs_ip.close()
    GlobalValue.fs_g.close()
    GlobalValue.fs_gs.close()
    GlobalValue.fs_gu.close()
    GlobalValue.fs_yb.close()
    GlobalValue.fs_gg.close()
    GlobalValue.fs_cg.close()
    GlobalValue.fs_ga.close()
    GlobalValue.fs_ap.close()
    GlobalValue.fs_gv.close()
    GlobalValue.fs_yg.close()

def getGoogleIp():
    blocklist = getnetblocks()
    
    checkstroedir()
    init_write_zone()
    tlen = 0
    thread_count_ip_num = 0;
    for iptxt in blocklist:
        iptxt = iptxt.lstrip('ip4:')
        (startip,endip,start,maxip) = getIpRange(iptxt)
        str = "IP BLOCK:{%s} IN { {%s} -- {%s} }\n" % (iptxt,startip,endip);
        GlobalValue.fs_ip.write(str)
        GlobalValue.fs_ip.flush()
        thread_count_ip_num = thread_count_ip_num + (maxip - start)
        while(tlen >50):
            time.sleep(1)
            tlen = threading.active_count()

        connetThread(start,maxip)

    GlobalValue.count_ip_num = thread_count_ip_num
    print('Count Ip:%s' % GlobalValue.count_ip_num)
    tlen = threading.active_count()
    signal.signal(signal.SIGINT, process_term)  
    while(tlen >0):
        time.sleep(1)
        tlen = threading.active_count()
    
    closefs()

if __name__ ==  "__main__":
    getGoogleIp()
