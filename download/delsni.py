from netfilterqueue import NetfilterQueue,Packet
from scapy.all import hexdump,IP,TCP,load_layer
from scapy.layers.inet import in4_chksum
import os
import secrets
import string


load_layer("tls")


def iptable():
    ip_list = ['110.242.68.66', '39.156.66.10', '20.205.243.166', '127.0.0.2']
    os.system('iptables -F OUTPUT')
    for ip in ip_list:
        os.system('iptables -I OUTPUT -d %s -p tcp --dport 443 -j NFQUEUE --queue-num 1' % ip)

def randstr(len):
    lowercase_letters = string.ascii_lowercase
    return (''.join(secrets.choice(lowercase_letters) for _ in range(len - 3)) + '.cn').encode('ascii')

def packet_modify(packet, idx, hostlen):
    print("==== Find SNI ======\n")
    newstr = randstr(hostlen)
    payload = packet.get_payload()

    # oldpkg = IP(payload)
    # payload = payload[0:0x24] + b"\x00\x00" + payload[0x26:]
    # chksum = in4_chksum(6, oldpkg, payload)
    # print(chksum)
    payload = payload[0:0x24] + b"\x00\x00" + payload[0x26:idx-8] + b'\x00' + payload[idx-7:idx] + newstr + payload[idx+hostlen:]
    print('***************************************')
    ippkg = IP(payload)
    chksum = in4_chksum(6, ippkg, payload).to_bytes(2, byteorder='big')
    payload = payload[0:0x24] + chksum + payload[0x26:]
    hexdump(payload)
    packet.set_payload(payload)

def packet_callback(packet:Packet):
    payload = packet.get_payload()
    if payload[0x21] == 0x10:
        print('ACK')
    if payload[0x21] == 0x18:
        print('ACK-PUSH')
    ack_no = payload[0x1C:0x1F]
    seq_no = payload[0x18:0x1B]

    filter = ['baidu.com', 'github.com', 'api.x.taoshouyou.com']
    for host in filter:
        findstr = host.encode('ascii');
        idx = payload.find(findstr)
        if  idx != -1:
            hostlen = len(host)
            packet_modify(packet, idx, hostlen)
    packet.accept()

iptable()

nfqueue = NetfilterQueue()
nfqueue.bind(1, packet_callback)
try:
    print("Start Queue bind")
    nfqueue.run()
except KeyboardInterrupt:
     print("Stopping packet processing...")
finally:
    nfqueue.unbind()  # 解绑队列
    print("Queue unbound.")

#sudo iptables -I OUTPUT -d 110.242.68.66 -p tcp --dport 443 -j NFQUEUE --queue-num 1
