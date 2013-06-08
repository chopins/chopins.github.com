svn查看指定用户更新记录 

`svn log -rhead:{2013-05-20} -v|sed -n '/username/,/-----$/ p'`
`svn log -rhead:12344 -v|sed -n '/username/,/-----$/ p'`
