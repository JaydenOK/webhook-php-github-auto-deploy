# webhook-auto-deploy
使用github的webhook，结合php,bash实现站点部署（支持扩展多站点）

###
hook.php 接收github服务器的webhook请求，数据校验等

##
auto_deploy.sh  根据不同请求，执行资源更新，部署操作
