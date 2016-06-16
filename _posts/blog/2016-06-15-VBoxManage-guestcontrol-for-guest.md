---
layout: blog_contents
title: VirtualBox 的 VBoxManage guestcontrol 命令使用
categories: blog
---

VirtualBox 的`VBoxManage guestcontrol`能在主机上执行客户机中的命令。这个命令对于自动化管理虚拟机十分有用。  

此命令要生效，需要在客户机中安装VirtualBox 增强功能，就是需要先安装VBoxGuestAdditions.iso这个包

下面以VirtualBox 5.0.16来举例说明使用方法，例如执行修改Linux Guest主机名命令方法的例子如下： 

```bash
VBoxManage guestcontrol "$1" run --exe "/bin/hostname" --username root --password 123456 -- -l $1
```

上面命令说明如下：

  * VBoxManage guestcontrol 客户机管理控制子命令
  * `"$1"`是虚拟机名字，可以通过`VBoxManage list vms`,
  * `run`是执行客户机的子命令
  * `--exe` 是`run`子命令的选项,该选项跟随需要执行的客户机命令的绝对路径
  * `"/bin/hostname"` 客户机上命令或程序的绝对路径
  * `--username root --password 123456` 登录客户机的用户名和密码
  * `-- -l` 该选项后面跟随客户机命令参数，__注意:官方文档中，传参数的选项只是`--`，然后跟上命令参数，但是这么做并不能正确执行命令。可是当加上`-l`这个参数后，命令将能正确执行__
     增加`-l`参数只是本人测试成功（注意:Linux `hostname`实际并没有`-l`参数），不保证任何时候都正确
  * 最后一个`$1`即为命令参数
  

  以下命令是使用bash执行修改文件的操作：
  
```bash
VBoxManage guestcontrol "$1" run --exe "/bin/bash" --username root --password 123456  -- -l -c "echo $1 >/etc/hostname"
```

与上一个例子一样，`$1`是主机名，这里的命令是使用bash的`echo`命令重定向修改文件

#VBoxManage其他常用命令使用  

```bash
#完全克隆一个现有的名叫 CopyServer 虚拟机，该虚拟机当前没有运行，新虚拟机名叫 NewServer
VBoxManage clonevm "CopyServer" --mode all --name "NewServer" --register

#关闭一个名叫RunningServer正在运行的虚拟机,使用高级电源管理模式关闭，这种模式会向虚拟机系统发送电源关闭信号
VBoxManage controlvm "RunningServer" acpipowerbutton

#直接断电关闭一个名为 RunningServer 的虚拟机
VBoxManage controlvm "RunningServer" poweroff

#无界面启动一个名为 OneServer 虚拟机
VBoxManage startvm "OneServer" --type headless

#彻底删除一个名为 OneServer 的虚拟机
VBoxManage unregistervm 'OneServer' --delete

#显示虚拟 OneVM 的硬件信息
 VBoxManage showvminfo OneVM
 
#挂在镜像文件到虚拟机 OneServer 的光驱上，当前控制器信息可用上一个命令获取
VBoxManage storageattach OneServer --storagectl storage_controller_1 --type dvddrive --port 1 --device 0 --medium /yourpath/VBoxGuestAdditions.iso

```
