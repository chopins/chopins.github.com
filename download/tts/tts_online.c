/*
* 语音合成（Text To Speech，TTS）技术能够自动将任意文字实时转换为连续的
* 自然语音，是一种能够在任何时间、任何地点，向任何人提供语音信息服务的
* 高效便捷手段，非常符合信息时代海量数据、动态更新和个性化查询的需求。
*/

#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <unistd.h>

#include "qtts.h"
#include "msp_cmn.h"
#include "msp_errors.h"
#include <math.h>

/* wav音频头部格式 */
typedef struct _wave_pcm_hdr
{
	char            riff[4];                // = "RIFF"
	int		size_8;                 // = FileSize - 8
	char            wave[4];                // = "WAVE"
	char            fmt[4];                 // = "fmt "
	int		fmt_size;		// = 下一个结构体的大小 : 16

	short int       format_tag;             // = PCM : 1
	short int       channels;               // = 通道数 : 1
	int		samples_per_sec;        // = 采样率 : 8000 | 6000 | 11025 | 16000
	int		avg_bytes_per_sec;      // = 每秒字节数 : samples_per_sec * bits_per_sample / 8
	short int       block_align;            // = 每采样点字节数 : wBitsPerSample / 8
	short int       bits_per_sample;        // = 量化比特数: 8 | 16

	char            data[4];                // = "data";
	int		data_size;              // = 纯数据长度 : FileSize - 44 
} wave_pcm_hdr;

/* 默认wav音频头部数据 */
wave_pcm_hdr default_wav_hdr = 
{
	{ 'R', 'I', 'F', 'F' },
	0,
	{'W', 'A', 'V', 'E'},
	{'f', 'm', 't', ' '},
	16,
	1,
	1,
	16000,
	32000,
	2,
	16,
	{'d', 'a', 't', 'a'},
	0  
};
/* 文本合成 */
int text_to_speech(const char* src_text, const char* des_path, const char* params)
{
	int          ret          = -1;
	FILE*        fp           = NULL;
	const char*  sessionID    = NULL;
	unsigned int audio_len    = 0;
	wave_pcm_hdr wav_hdr      = default_wav_hdr;
	int          synth_status = MSP_TTS_FLAG_STILL_HAVE_DATA;

	if (NULL == src_text || NULL == des_path)
	{
		printf("params is error!\n");
		return ret;
	}
	fp = fopen(des_path, "wb");
	if (NULL == fp)
	{
		printf("open %s error.\n", des_path);
		return ret;
	}
	/* 开始合成 */
	sessionID = QTTSSessionBegin(params, &ret);
	if (MSP_SUCCESS != ret)
	{
		printf("QTTSSessionBegin failed, error code: %d.\n", ret);
		fclose(fp);
		return ret;
	}
	ret = QTTSTextPut(sessionID, src_text, (unsigned int)strlen(src_text), NULL);
	if (MSP_SUCCESS != ret)
	{
		printf("QTTSTextPut failed, error code: %d.\n",ret);
		QTTSSessionEnd(sessionID, "TextPutError");
		fclose(fp);
		return ret;
	}
	printf("正在合成 ...\n");
	fwrite(&wav_hdr, sizeof(wav_hdr) ,1, fp); //添加wav音频头，使用采样率为16000
	while (1) 
	{
		/* 获取合成音频 */
		const void* data = QTTSAudioGet(sessionID, &audio_len, &synth_status, &ret);
		if (MSP_SUCCESS != ret)
			break;
		if (NULL != data)
		{
			fwrite(data, audio_len, 1, fp);
		    wav_hdr.data_size += audio_len; //计算data_size大小
		}
		if (MSP_TTS_FLAG_DATA_END == synth_status)
			break;
		printf(">");
		usleep(150*1000); //防止频繁占用CPU
	}
	printf("\n");
	if (MSP_SUCCESS != ret)
	{
		printf("QTTSAudioGet failed, error code: %d.\n",ret);
		QTTSSessionEnd(sessionID, "AudioGetError");
		fclose(fp);
		return ret;
	}
	/* 修正wav文件头数据的大小 */
	wav_hdr.size_8 += wav_hdr.data_size + (sizeof(wav_hdr) - 8);
	
	/* 将修正过的数据写回文件头部,音频文件为wav格式 */
	fseek(fp, 4, 0);
	fwrite(&wav_hdr.size_8,sizeof(wav_hdr.size_8), 1, fp); //写入size_8的值
	fseek(fp, 40, 0); //将文件指针偏移到存储data_size值的位置
	fwrite(&wav_hdr.data_size,sizeof(wav_hdr.data_size), 1, fp); //写入data_size的值
	fclose(fp);
	fp = NULL;
	/* 合成完毕 */
	ret = QTTSSessionEnd(sessionID, "Normal");
	if (MSP_SUCCESS != ret)
	{
		printf("QTTSSessionEnd failed, error code: %d.\n",ret);
	}

	return ret;
}

void help() {
puts("本软件基于科大讯飞在线TTS提供文字转语音\n用法参数如下:\n -d  存储文件夹\n -i  读取offset(上次处理到的 offset 数)\n -s  需转换的文件\n -v  合成发音人(可用：xiaoxi,xiaoyan, yanping, xiaofeng, jinger,xiaomeng,xiaolin)\n -sp 速度，1-100\n -vo 音量，1-100\n -fn 保存的音频文件编号起始数(新的 offset )\n -h  显示本信息");
}

int main(int argc, char* argv[])
{
	int         ret                  = MSP_SUCCESS;
	int 		read_offset 	     = 0;
	int 		seek = 0;
	int 		seek_offset = 0;
	int 		set_offset = 0;
	int 	    file_count = 1;
	int 		maxLen = 8000;
	int lnOffset = 0;
	const char* login_params         = "appid = 5c1d90a2, work_dir = .";//登录参数,appid与msc库绑定,请勿随意改动
	char read_buff[maxLen];
	int char1;
	char* source_file = "";
	char* store_dir = "wav";
	char* voice_name = "xiaoyu";
	char session_begin_params[300];
	/*
	* rdn:           合成音频数字发音方式
	* volume:        合成音频的音量
	* pitch:         合成音频的音调
	* speed:         合成音频对应的语速
	* voice_name:    合成发音人
	* sample_rate:   合成音频采样率
	* text_encoding: 合成文本编码格式
	*
	*/
	const char* session_begin_params_c = "voice_name = %s, text_encoding = utf8, sample_rate = 16000, speed = %s, volume = %s, pitch = 50, rdn = 2";
	char filename[30];            //= "wav/tts_sample%s.wav"; //合成的语音文件名称
	char text[1000];               // = "亲爱的用户，您好，这是一个语音合成示例，感谢您对科大讯飞语音技术的支持！科大讯飞是亚太地区最大的语音上市公司，股票代码：002230";
	char args[100];
	char format_arr[100];
	char* format;
	char* speed = "50";
	char* volume = "50";

	for(int i=0;i<argc;i++) {
		if(strcmp(argv[i],"-d") == 0) {
			i++;
			store_dir = argv[i];
			if(access(store_dir, 0) == -1) {
				printf("%s no such dir", store_dir);
				help();
				return 1;
			}
		} else if(strcmp(argv[i], "-i") == 0) {
			i++;
			set_offset = atoi(argv[i]);
		} else if(strcmp(argv[i],"-s") == 0) {
			i++;
			source_file = argv[i];
		} else if(strcmp(argv[i],"-v") == 0) {
			i++;
			voice_name = argv[i];
		} else if(strcmp(argv[i],"-h") == 0) {
			help();
			return 0;
		} else if(strcmp(argv[i], "-sp") == 0) {
			i++;
			speed = argv[i];
		} else if(strcmp(argv[i], "-vo") == 0) {
			i++;
			volume = argv[i];
		} else if(strcmp(argv[i], "-fn") == 0) {
			i++;
			file_count = atoi(argv[i]);
		}
	}
	if(strlen(source_file) == 0) {
		puts("require source file path");
		help();
		return 1;
	}
	sprintf(session_begin_params, session_begin_params_c, voice_name, speed, volume);

	FILE * afp;

	/* 用户登录 */
	ret = MSPLogin(NULL, NULL, login_params);//第一个参数是用户名，第二个参数是密码，第三个参数是登录参数，用户名和密码可在http://www.xfyun.cn注册获取
	if (MSP_SUCCESS != ret)
	{
		printf("MSPLogin failed, error code: %d.\n", ret);
		goto exit ;//登录失败，退出登录
	}
	/* 文本合成 */
	printf("开始合成 ...\n");

	printf("open file %s\n", source_file);
	afp = fopen(source_file, "rb");

	if (NULL == afp)
	{
		printf("open %s error.\n", source_file);
		return 1;
	}
	fseek(afp, 0L, SEEK_END);
	int filesize = ftell(afp);
	fseek(afp, 0L, SEEK_SET);
	printf("File Size %d\n", filesize);
	
	int bit_len = ceil(filesize/maxLen);
	sprintf(format_arr, "%d", bit_len);
	if (set_offset > 0) {
		seek_offset += set_offset;
		fseek(afp, seek_offset, SEEK_SET);
	}

	if(atol(format_arr) > 4) {
		sprintf(format_arr,"%ss/convert-%s0%dd.wav", "%", "%", strlen(format_arr));
	} else {	
		strcpy(format_arr,  "%s/convert-%'.04d.wav");
	}
	do {
		seek = 0;
		do {
			char1 = fgetc(afp);
			seek++;
			if(char1 == 10) {
				lnOffset = seek;
			}
		} while(feof(afp) == 0 && seek < maxLen);

		fseek(afp, seek_offset, SEEK_SET);
		fread(read_buff,1, lnOffset, afp);

		seek_offset += lnOffset;

		sprintf(filename, format_arr, store_dir, file_count);
		printf("准备合成量:%d\n", seek_offset);
		ret = text_to_speech(read_buff, filename, session_begin_params);
		
		memset(read_buff, 0x0, maxLen);
		if (MSP_SUCCESS != ret)
		{
			printf("text_to_speech failed, error code: %d.\n", ret);
		}
		printf("保存文件:%s, 已合成量:%d\n", filename, seek_offset);
		file_count++;
	} while(feof(afp) == 0 && MSP_SUCCESS == ret);

	if(feof(afp) == 0) {
		read_offset = ftell(afp);
	}
	fclose(afp);
	printf("合成完毕\n");


exit:
	printf("按任意键退出 ...\n");
	getchar();
	MSPLogout(); //退出登录

	return 0;
}

