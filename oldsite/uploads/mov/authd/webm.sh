#!/bin/bash
#ffmpeg -f v4l2 -pix_fmt yuv420p -input_format mjpeg -r 30 -s 1280x720 -i /dev/video0 -f alsa -ar 44100 -ac 2 -i hw:2 -c:a libopus -map 0:v -map 1:a -c:v libvpx-vp9 -r 25 -g 90 -quality realtime -speed 6 -threads 2 -tile-columns 2 -frame-parallel 1 -qmin 4 -qmax 48 -b:v 4500 output.webm

#ffmpeg -stream_loop 100 -i /dev/video0 \
#  -r 30 -g 90 -s 3840x2160 -quality realtime -speed 5 -threads 2 \
#  -tile-columns 3 -frame-parallel 1 -qmin 4 -qmax 48 -b:v 7800k -c:v vp9 \
#  -b:a 128k -c:a libopus -f webm pipe1

#ffmpeg -y -i /dev/video0 -s hd480 -vcodec libvpx -g 120 -lag-in-frames 16 -deadline good -cpu-used 0 -vprofile 0 -qmax 63 -qmin 0 -b:v 768k -acodec libvorbis -ab 112k -ar 44100 -f webm ./authd/output.webm

#ffmpeg -i /dev/video0 -c:v libvpx -crf 10 -b:v 500k -c:a libvorbis ./authd/output.webm

ffmpeg -y -i /dev/video0 -c:v libvpx -crf 10 -b:v 1M -fs 1M -c:a libvorbis ./authd/output.webm
 # -f webm_chunk \
 #   -audio_chunk_duration 2000 \
 #   -chunk_start_index 1 \

#ffmpeg -f alsa -r 16000 -i hw:2,0 -f video4linux2 -s 640x480 -i /dev/video0 -r 30 -f avi -vcodec mpeg4 -vtag xvid -acodec libmp3lame -ab 96k -f webm output.webm -chunk_size 10
