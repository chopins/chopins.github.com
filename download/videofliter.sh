#!/bin/bash

ffmpeg -i ${VF} -filter_complex "delogo=x=10:y=500:w=400:h=300" -c:v libx264 -crf 28 -preset veryfast -c:a copy -movflags +faststart output.mkv -y