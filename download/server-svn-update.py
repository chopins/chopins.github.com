#!/bin/env python

'''
HTTP SVN UPDATE SCRIPT
本脚本会以Daemon模式运行
'''
#SVN用户名
SVN_USERNAME = ""

#SVN用户密码
SVN_USER_PASSWORD = ""

#SVN的本地库路径
SVN_LOCAL_REPOSITORY_PATH='/home/repository'

#SVN二进制执行路径
SVN_BIN_PATH = "svn"

#SNV执行参数
SVN_EXEC_OPTION = "--accept 'theirs-full' --non-interactive --force"

#WEB服务器监听端口
SERVER_ADRESS = "0.0.0.0"
#WEB访问端口
SERVER_PORT = 8989

#日志文件
LOG_FILE = "/tmp/server-svn-update.log"

SVN_EXEC_COMMOND = "%s update --username %s --password %s %s"%(SVN_BIN_PATH,SVN_USERNAME,SVN_USER_PASSWORD,SVN_EXEC_OPTION)

import sys,os,io,shutil
import BaseHTTPServer
class HTTPHandler(BaseHTTPServer.BaseHTTPRequestHandler):
    def do_GET(self):
        global SVN_EXEC_COMMOND,SVN_LOCAL_REPOSITORY_PATH
	    os.chdir(SVN_LOCAL_REPOSITORY_PATH)
	    tmp = os.popen(SVN_EXEC_COMMOND).readlines()
	    content = ''
	    for info in tmp:
	        content = "%s<br />%s" % (content,info) 
	
	    enc="UTF-8"
        f = io.BytesIO()
        f.write(content)
        f.seek(0)
	    self.send_response(200)
	    self.send_header("Content-type", "text/html; charset=%s" % enc)
	    self.send_header("Content-Length", str(len(content)))
        self.end_headers()
	    shutil.copyfileobj(f,self.wfile)

def daemon():
    global LOG_FILE
    pid = os.fork()
    if pid>0:
       os._exit(0)

    if pid<0:
       print 'Fork1 error'
       os._exit(1)

    pid = os.fork()
    if pid <0:
       print 'Fork2 error'
       os._exit(1)
   
    if pid >0:
       os._exit(0)

    os.chdir('/')
    os.umask(0)
    os.setsid()
    sys.stdout.close()
    sys.stdin.close()
    sys.stderr.close()
    
    sys.stdout = open(LOG_FILE,'a')
    sys.stdin = open(LOG_FILE,'a')
    sys.stderr = open(LOG_FILE,'a')
    pid = os.fork()
    if pid <0:
        print 'Fork 3 error'
        os._exit(1)

    if pid >0:
        os._exit(0)

    subprocess();

def subprocess():
     while(True):
         pid = os.fork()
         if pid < 0:
             print 'Fork work error'
             os._exit(1)

         if pid >0:
             os.waitpid(pid,0)
             subprocess()
         
         if pid == 0:
             server()
             os._exit(1)

def server():
    global SERVER_PORT,SERVER_ADRESS
    ServerClass  = BaseHTTPServer.HTTPServer
    Protocol     = "HTTP/1.0"

    server_address = (SERVER_ADRESS, SERVER_PORT)

    HTTPHandler.protocol_version = Protocol
    httpd = ServerClass(server_address, HTTPHandler)

    sa = httpd.socket.getsockname()
    httpd.serve_forever()

if __name__ == "__main__":
    daemon()
