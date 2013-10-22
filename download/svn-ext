#!/bin/sh
#svn search update-log script

err=0
if [ "$1" == "" ];then
        echo 'need username'
err=1
fi
if [ "$2" == ""];then
echo 'need date or version number'
err=1
fi
if [ "$3" == "" ];then
echo 'need date or version number'
err=1
fi

if [ $err -eq 1 ];then
echo 'Usage: svn-ext username -d 2012-09-23'
echo '       svn-ext username -n 2012-09-23'
exit
fi

if [ $2 == '-d' ];then
echo 'checking...'
        svn log -rhead:{$3} -v|sed -n '/'$1'/,/-----$/ p'
elif [ $2 == '-v' ];then
        svn log -rhead:$3 -v|sed -n '/'$1'/,/-----$/ p'
fi
