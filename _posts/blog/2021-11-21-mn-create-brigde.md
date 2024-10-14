---
layout: blog_contents
title: 使用NetworkManager来创建桥接设备
categories: blog
---

### 使用NetworkManager来创建桥接设备

1. 使用`nm-connection-editor`GUI工具新建网桥，可设定网桥名字为`br0`
2. 在`br0网桥`编辑中，`网桥连接`内添加一个`以太网`，其实就是添加一个`以太网配置`，配置名`br0_1`，并且设备选择一个`网卡设备`，例如`enp7s0`，`eth0`等
3. 全部保存后，删除原有的以太网连接配置，只保留网桥关联的的以太网配置
4. 在命令行下执行`nmcli connection modify br1 ipv4.addresses 192.168.1.100`, 这条命令中IP地址为期望地址,`br1`为网桥名字，这个IP可在`nm-connection-editor`中配置
5. 执行`nmcli connection up br1`，这条命令中`br1`为网桥名字，这样`NetworkManager`就托管了网桥
6. 执行`nmcli c`，查看网桥添加的以太网配置`br0_1`的`DEVICE`的值是不是`网卡设备enp7s0`，如果是即表示绑定成功
7. 如果系统托盘的网络还未连接，点击连接一下，此时会发现，连接设备是`br0_1`,但是没有IP地址，表示网桥配置成功

### 使用virt-manager 管理 QEMU/KVM 时添加桥接模式
在按创建网桥的方法创建好系统网桥后，在virt-manager的guest编辑界面添加一个网络硬件，然后网络源选择桥接设备，设备名填写系统网桥`br0`即可