#!/bin/bash

REPOSITORY=$1
DATE=`date "+%Y-%m-%d %H:%M:%S"`

WORKSPACE_DIR=$(cd `dirname $0`; pwd)
WWW_DIR=/www/wwwroot
# 站点目录
WEB_DIR=${WWW_DIR}/${REPOSITORY}
# 日志目录
LOG_PATH=/www/wwwroot/webhook
LOG_FILE=${LOG_PATH}/auto_deploy.log
# 修改站点配置
HOT_UPDATE_FILE=${WORKSPACE_DIR}/hot_update.sh

if [[ $REPOSITORY == "" ]] ;then
	echo "没有指定仓库"
	exit
fi

if [ ! -d $WEB_DIR ];then
	echo "仓库不存在:${WEB_DIR}"
	exit
fi

if [ ! -f ${LOG_FILE} ];then
	/bin/touch ${LOG_FILE}
fi

cd ${WEB_DIR};
echo "[${DATE}] repository:${WEB_DIR} cmd: /bin/git pull" | tee -a ${LOG_FILE}

/bin/sudo /bin/git reset --hard origin/master
# 拉取最新代码
/bin/sudo /bin/git pull origin master | tee -a ${LOG_FILE}

## 此处可做一些站点配置文件的修改操作(如数据库配置、域名替换、调试模式修改)
if [ ! -f ${HOT_UPDATE_FILE} ];then
  /bin/sudo ./${HOT_UPDATE_FILE}
fi

/bin/sudo chown -R www:www ./*
/bin/sudo chmod -R 755 ./*



exit
