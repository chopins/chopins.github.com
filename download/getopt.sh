#!/bin/bash

declare -a _ALL_OPTION_
declare -a _ALL_ARG_
_ALL_ARG_=$*

function checkopt()
{
    local i=1
    for (( i = 1; i < ${#_ALL_ARG_[@]} ; i++ ))
    do
        arg=$_ALL_ARG_[$i]
        case $arg in
            $1)
                if test checkreg $arg;then
                    if [ -z $_ALL_OPTION_["$arg"] ];then
                        return true
                    else
                        local nk=$(($i+1))
                        return [ $_ALL_OPTION["$arg"] ==  $_ALL_ARG_["$nk"] ]
                    fi
                else
                    return true
                fi
            ;;
        esac
    done
    return false
}

function checkreg()
{
    local arg=''
    for arg in  $_ALL_OPTION_
    do
       if [ "$arg" == "$1" ];then
            return true
       fi
    done
    return false
}

function getopt()
{
    local i=1
    local v
    for (( i=1 ; i<${#_ALL_ARR_[@]} ; i++ ))
    do
        if [ "$1" == "$_ALL_ARR[$i]" ];then
            local next=$(($i + 1))
            local nextvalue = $_ALL_ARR_[$next]
            if test checkreg $nextvalue ;then
                return ''
            fi
            if ! test checkreg $1 ;then
                return $nextvalue
            else 
                for v in "$_ALL_OPTION[$1]";
                do
                    if [ $_ALL_ARR_[$next] == $v ];then
                        return $v
                    fi
                done
                return ''    
            fi
        fi
    done
}

function regopt()
{
    if [ $# -gt 1 ];then
        _ALL_OPTION_["$1"] = ''
    elif
        _ALL_OPTION_["$1"] = $2
    fi
}
