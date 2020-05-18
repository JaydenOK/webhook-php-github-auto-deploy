# webhook-auto-deploy
使用github的webhook，结合php,bash实现站点部署（支持扩展多站点）

###
hook.php 接收github服务器的webhook请求，数据校验等

###
auto_deploy.sh  根据不同的仓库hook请求，执行资源更新、部署操作

####
详细操作流程

使用 Github 的 webhook钩子 实现线上项目的自动部署

在github创建一个项目，如 Jcai12321/webhook-php-github-auto-deploy，点击 Repositories -> 项目 -> Settings -> Webhooks -> Add Webhook
添加 接收Payload地址和Secret: http://xxx.com:11111/hook (本项目hook.php访问地址) ,设置 Content-Type:application/json，填写secret, 勾选 Active ，提交即可

生成秘钥对：ssh-keygen -t rsa -C "你的邮箱"

查看服务器是否安装git，没有则在 centos 安装git : yum install git

配置用户，邮箱
git config --global user.name "你在github的用户名"
git config --global user.email "你注册github的邮箱"

生成公私钥对-》回车、回车，删除秘钥保存在用户.ssh下，下载 /root/.ssh/id_rsa.pub
ssh-keygen -t rsa -C "你注册github的邮箱"

保存公钥到github, 复制已生成的公钥添加到git服务器,https://github.com/settings/profile ->setting  ->  SSH and GPG keys  ->  添加SSH keys

验证下这个key是不是正常工作：
ssh -T git@github.com
当你第一次使用Git的clone或者push命令连接GitHub时，会得到一个警告：输入yes即可


可能出现：ssh: connect to host github.com port 22: Connection refused
解决：进入用户.ssh的目录，创建文件 touch config && chmod 600 config ,内容如下，出现测试成功: ssh -T git@github.com

Host github.com
User 603480498@qq.com
Hostname ssh.github.com
PreferredAuthentications publickey  
IdentityFile ~/.ssh/id_rsa
Port 443

首次手动部署项目到服务器
git clone git@github.com:Jcai12321/webhook-php-github-auto-deploy.git  webhook

开启www用户，免密码sudo权限（可只分配部分的执行权限）
vim /etc/sudoers 添加:
www     ALL=(ALL)       NOPASSWD:ALL

将本项目放在webhook通知可访问的站点上

最后提交代码测试下吧！！！
