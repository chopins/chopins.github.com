svn查看指定用户更新记录 

`svn log -rhead:{2013-05-20} -v|sed -n '/username/,/-----$/ p'`

`svn log -rhead:12344 -v|sed -n '/username/,/-----$/ p'`

==Fedora 18创建 AP 模式热点方法==
1. 安装hostapd, yum安装即可
2. 配置hostapd,编辑/etc/hostapd/hostapd.conf,如下
```
ctrl_interface=/var/run/hostapd
ctrl_interface_group=wheel

# Some usable default settings...
macaddr_acl=0
auth_algs=1
ignore_broadcast_ssid=0

# Uncomment these for base WPA & WPA2 support with a pre-shared key
wpa=3
wpa_key_mgmt=WPA-PSK
wpa_pairwise=CCMP
rsn_pairwise=CCMP

# DO NOT FORGET TO SET A WPA PASSPHRASE!!
wpa_passphrase=12345678

# Most modern wireless drivers in the kernel need driver=nl80211
driver=nl80211

# Customize these for your local configuration...
interface=wlan0
hw_mode=g
channel=7
ssid=MyAP
```
3. 取消NetworkManager对WiFi的托管，否则hostapd无法启动网卡，取消方法见https://wiki.archlinux.org/index.php/Software_Access_Point#NetworkManager_is_interfering
4. 打开防火墙配置，命令为firewall-config， 开启 伪装(Masquerading)
5. 将无线网卡ip配置成网关ip,比如`ifconfig wlan0 192.168.1.1`
6. 设置需要连接该热点的设备为静态IP，比如 192.168.1.2
7. 可以安装DHCP服务器实现动态获取IP，方法见google
8. 然后systemctl enable hostapd
9, 在/etc/rc.d/rc.local(没有创建，并设置为可执行)中添加命令 `ifconfig wlan0 192.168.1.1` 实现开机启动
