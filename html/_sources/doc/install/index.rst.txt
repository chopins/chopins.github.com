#########################
安装说明
#########################
通过以下步骤安装:
 
#. 下载压缩包到本地，解压压缩包;
#. 通过PHP命令执行 ``php vendor/toknot/initapp.php`` 应用初始化向导;
#. 然后进入应用目录，开发你自己的应用;
#. 修改应用目录下 *config/config.ini* 主配置文件，配置信息见 :doc:`主配置文件<../configuration/main>` ;
#. 根据应用需求修改其他相关配置文件;
#. 配置服务器:

    - nginx::

        location  / {
            root $dir/index.php;
        }

    - apache::

        <Directory "/your-app-path/webroot">
            RewriteBase /
            RewriteRule .*  index.php
            RewriteEngine On
        </Directory>

    - PHP CLI Web Server::

        php -S 127.0.0.1:8000 index.php


