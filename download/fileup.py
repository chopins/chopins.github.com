#!/bin/env python

import sys,os,time,getpass

#subversion revision url address
rev_url ='svn://123.57.181.139'

#ssh user
suser='root'

#upload file directroy in deploy server
deploy = '/'
#deploy server ip
sip = ['192.168.1.58']

#deploy root directroy
approot='/var/www/allread'

#your web root code in revision directroy, it is relative path
# eg: webroot  /var/www/siteapp/pcweb
#              /var/www/siteapp/pcweb/public
#              /var/www/siteapp/pcweb/module
# revision:    /company/site/pcweb/public
#              /company/site/pcweb/module
#
# approot = '/var/www/siteapp/pcweb'
# svn_start_path = 'commpany/site/pcweb'
svn_start_path = 'b2b2c'

LOCAL_DOW_PATH= '/'+ getpass.getuser() +'/.svndeploy'
SVN_PATH = ''
if not SVN_PATH:
    for p in ['usr/bin/svn','/usr/local/subversion/bin/svn','/opt/subversion/bin/svn','/bin/svn','/usr/local/bin/svn']:
        if os.path.exists(p):
            SVN_PATH = p
            break

if not SVN_PATH:
    print('can not find svn');
    sys.exit(11)

if len(sys.argv) == 2:
    rev = sys.argv[1].split(',')
    revstr = sys.argv[1]
elif len(sys.argv) > 2:
    rev = sys.argv[1:]
    revstr = ','.join(rev)
else:
    print('no revision, usage:\n %s 23,223,4322\n %s 23 334 554' % (sys.argv[0],sys.argv[0]))
    sys.exit(10)

def crtpath(path):
    if not os.path.isdir(path):
        os.makedirs(path)

def callcmd(cmd):
    st = os.system(cmd)
    return 'Success' if st == 0 else 'Failure'

def savelast(v):
    lastfile = LOCAL_DOW_PATH+'/last50'
    lines = open(lastfile).readlines()
    rl = len(lines)
    if rl >= 50:
        lines = lines[1:]
        lines.append(v)

    n = '\n'.join(lines)
    open(lastfile,'w').write(n)

crtpath(LOCAL_DOW_PATH)
class savelog():
    def __init__(self):
        self.logdir = os.path.join(LOCAL_DOW_PATH,'log')
        crtpath(self.logdir)
        self.fp = open(os.path.join(self.logdir,time.strftime('%Y-%m')+'.log'), 'a+')

    def w(self,msg):
        txt = '[ %s ] %s\n' % (time.strftime('%Y-%m-%d %H:%M:%S'),msg)
        self.fp.write(txt)

logs = savelog()

logs.w('launch deploy task: rev [%s]' % revstr)


nowtime = time.strftime('%Y%m%d%H%M%S')

swpfile = os.path.join(logs.logdir,nowtime+'.swp')

localwk = os.path.join(LOCAL_DOW_PATH,'op',nowtime)
crtpath(localwk)

rev.sort()

if not rev_url:
    revinfo = '%s info ./ > %s' % (SVN_PATH, swpfile)
    st = os.system(revinfo)
    if st > 0 :
        print('set revision url or change to local revision')
        sys.exit(11)
    tmp = open(swpfile).readlines()
    rev_copy_path = tmp[1].split(':',1)[1].strip()
    rev_url = tmp[4].split(':',1)[1].strip()
    os.remove(swpfile)

os.chdir(LOCAL_DOW_PATH)

for r in rev:
    cmd = '%s log -v -r %s %s' % (SVN_PATH,r,rev_url)
    tmp = os.popen(cmd).readlines()
    log = tmp[3:(len(tmp)-3)]
    for logline in log:
        reverse = logline.strip()[::-1]
        idx = reverse.find(' ')
        filepath = reverse[0:idx][::-1]
        dirpath = os.path.dirname(filepath)

        localpath = localwk+dirpath

        if not os.path.isdir(localpath):
            os.makedirs(localpath)

        cmd = '%s export --depth=empty %s%s@%s %s%s' % (SVN_PATH,rev_url,filepath,r,localwk,filepath)
        st = callcmd(cmd)

        logs.w('export %s %s' % (filepath, st))



tarfile = os.path.join(LOCAL_DOW_PATH, nowtime+'.tar.gz')

if svn_start_path:
    tar_work = os.path.join(localwk,svn_start_path)
os.chdir(tar_work)

st = callcmd('tar cfz %s *' % tarfile)
logs.w('tar file %s %s' % (tarfile,st))

deploy_path = os.path.join(deploy,suser,'deploy')
remote_file = os.path.join(deploy_path, nowtime + '.tar.gz')
savelast(nowtime)
for ip in sip:
    suh = '%s@%s' % (suser,ip)
    st = callcmd('scp %s %s:%s' % (tarfile,suh, remote_file))
    logs.w('scp %s to %s %s' % (tarfile,remote_file, st))

    scmd = 'tar xfz %s -C %s' % (remote_file, approot)
    st = callcmd('ssh %s %s' % (suh, scmd))
    logs.w('deploy file '+ st)

logs.w('deploy end')
