#!/bin/bash
#
#config file content format:
#
# VIDEO FILE_PATH
# start_time end_time 
# start_time end_time
# .......
#
# first line is video file path
# then line is cut time
# time format: 00:00:00
#

IFS=!?!
if [ $# -lt 1 ];then
echo "need split config file"
exit
fi
CONFIG=()
I=0
while read line
do
CONFIG[$I]="${line}"
I=$I+1
done < $1
VIDEO_FILE="${CONFIG[0]}"
FNAME=(`basename "${VIDEO_FILE}"|tr ' ' '-'|tr '/' '-'`)
pwd=`pwd`
t=`date +%Y%m%d%H%M%S`
TMPDIR="${pwd}/tmp.${t}"
mkdir $TMPDIR
STEP=0
SKIP=0
SPLIT_FILE=()

for CL in ${CONFIG[@]};do
if [ $SKIP -eq 0 ];then
SKIP=$((SKIP+1))
continue
fi
CLA=(`echo $CL | tr ' ' '!?!'`)

SPLIT_FILE[$STEP]="$TMPDIR/part-$STEP-$FNAME"
ffmpeg -ss ${CLA[0]} -i "$VIDEO_FILE" -t ${CLA[1]} -vcodec copy -acodec copy "$TMPDIR/part-$STEP-$FNAME"
STEP=$((STEP+1))
done

CAT=(`echo ${SPLIT_FILE[@]} | tr ' ' '|'`)

ffmpeg -i concat:"$CAT" -c copy "$TMPDIR/concat-$STEP-$FNAME"

for SF in ${SPLIT_FILE[@]};do
rm -rf "$SF"
done
