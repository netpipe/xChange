#!/bin/bash

ffmpeg -y -i /dev/video0 -f pulse -ac 2 -i default -c:v libvpx -r 30 -g 90 -s 1280x720 -quality realtime -speed 5 -threads 2 -crf 10 -segment_time 00:00:40 -c:a libvorbis output.webm


 
#ffmpeg -f v4l2 -pix_fmt yuv420p -input_format mjpeg -r 30 -s 1280x720 -i /dev/video0 -f alsa -ar 44100 -ac 2 -i hw:2 -c:a libopus -map 0:v -map 1:a -c:v libvpx-vp9 -r 25 -g 90 -quality realtime -speed 6 -threads 2 -tile-columns 2 -frame-parallel 1 -qmin 4 -qmax 48 -b:v 4500 output.webm

#ffmpeg -stream_loop 100 -i /dev/video1 \
#  -r 30 -g 90 -s 1280x720 -quality realtime -speed 5 -threads 2 \
#  -tile-columns 3 -frame-parallel 1 -qmin 4 -qmax 48 -b:v 7800k -c:v vp9 \
#  -b:a 128k -c:a libopus -segment_time 00:00:30 -f webm pipe1%0d.webm

#ffmpeg -y -i /dev/video0 -s hd480 -vcodec libvpx -fs 1M -g 120 -lag-in-frames 16 -deadline good -cpu-used 0 -vprofile 0 -qmax 63 -qmin 0 -b:v 768k -acodec libvorbis -ab 112k -ar 44100 -f webm ./authd/output.webm

#ffmpeg -i /dev/video0 -c:v libvpx -crf 10 -b:v 500k -chunk_size 10 -c:a libvorbis ./authd/output.webm

#ffmpeg -f alsa -r 16000 -i hw:2,0 -f video4linux2 -s 640x480 -i /dev/video0 -r 30 -f avi -vcodec mpeg4 -vtag xvid -acodec libmp3lame -ab 96k -f webm output.webm 


#ffmpeg -y -i /dev/video0 -c:v libvpx -crf 10 -b:v 1M -fs 1M -c:a libvorbis ./authd/output.webm
#ffmpeg -y -i /dev/video0 -c:v libvpx -crf 10 -b:v 1M -segment_time 00:00:10 -c:a libvorbis output.webm

#ffmpeg -y -i /dev/video1 -c:v libvpx -pix_fmt yuv420p -video_size 1280x720  -quality realtime -speed 5 -threads 2 -crf 10 -segment_time 00:00:40  -c:a libvorbis  -f pulse -ac 2 -i default -f segment -reset_timestamps 1  ./authd/output%01d.webm

#ffmpeg -i input.mp4 -c copy -map 0 -segment_time 00:20:00 -f segment -reset_timestamps 1 output%03d.mp4
#ffmpeg -i input.mp4 -c copy -map 0 -segment_time 00:20:00 -f segment -reset_timestamps 1 output%03d.mp4

 # -f webm_chunk \
 #   -audio_chunk_duration 2000 \
 #   -chunk_start_index 1 \


#-f pulse -ac 2 -i default
#-framerate 25 

#ffmpeg -y -i /dev/video1 -r 30 -g 90 -s 1280x720 -quality realtime -speed 5 -threads 2 -c:v libvpx -f pulse -ac 2 -i default -fs 3M output.webm


#ffmpeg  -i /dev/video1 -video_size 1024x768 -framerate 25 -f pulse -ac 2 -i default output.mkv


#$ ffmpeg -i x.avi frame.%03d.png; ffmpeg -r 25 -f image2 -i frame.%03d.png -vcodec libx264 -crf 25 -pix_fmt yuv420p frame.mp4
#ffmpeg -pixel_format yuv420p -video_size 320x240 -framerate 75 -i file.raw file.avi

#ffmpeg -i /dev/video1 -fflags +nobuffer -re -y -f rawvideo -pix_fmt yuv420p -video_size 1280x720 -framerate 10 -i pipe:0 -fflags +nobuffer -re -f alsa -ar 16000 -ac 2 -i hw:3,0 -map 0:0 -map 1:0 -c:v libvpx -profile:v high -b:v 2000000 -c:a aac -b:a 16k -ac 1 -f mp4 %f.mp4

#ffmpeg [input1 options] -i input1 [input2 options] -i input2 [output1 options] output1 [output2 options] output2

#./ffmpeg -f alsa -i pulse -f video4linux2 -s 640x480 -i /dev/video1 -r 24 video.mpg

#arecord -L

#./ffmpeg -f alsa -i plughw:CARD=CameraB409241,DEV=0  -f video4linux2 -s 320x240 -i /dev/video0 -t 30 out.mpg

#ffmpeg -f oss -i /dev/dsp -f video4linux2 -s 320x240 -i /dev/video0 out.mpg

#ffmpeg \
#-video_size 1024x720 \
#-framerate 10 \
#-f x11grab -i :0.0+100,200 \
#-f pulse -ac 2 -i default \
#-c:a libmp3lame -ar 48000 \
#out.mkv


#ffmpeg -f alsa -ac 2 -i pulse -f v4l2 -s 1280x720 -r 10 -i /dev/video0 -vcodec libx264 -pix_fmt yuv420p101e -preset ultrafast -r 25 -g 20 -b:v 2500k -codec:a libmp3lame -ar 44100 -threads 4 -b:a 11025 -bufsize 512k -f flv rtmp://a.rtmp.youtube.com/live2/YOURSTREAMKEY
#ffmpeg -f alsa -ac 2 -i pulse -f v4l2 -s 1280x720 -r 10 -i /dev/video0 -vcodec libx264 -preset ultrafast -r 25 -g 20 -b:v 2500k -codec:a libmp3lame -ar 44100 -threads 4 -b:a 11025 -bufsize 512k -f flv rtmp://a.rtmp.youtube.com/live2/YOURSTREAMKEY
#ffmpeg -f alsa -ac 2 -i pulse -f v4l2 -s 1280x720 -r 10 -i /dev/video1 -f v4l2 -s 320x240 -r 10 -i /dev/video0 -filter_complex "[1:v]setpts=PTS-STARTPTS[bg]; [2:v]setpts=PTS-STARTPTS[fg]; [bg][fg]overlay=shortest=1 [out]" -map "[out]" -map 0:a -vcodec libx264 -pix_fmt yuv420p -preset veryfast -r 25 -g 20 -b:v 2500k -codec:a libmp3lame -ar 44100 -threads 6 -b:a 11025 -bufsize 512k -f flv rtmp://a.rtmp.youtube.com/live2/YOURSTREAMKEY

