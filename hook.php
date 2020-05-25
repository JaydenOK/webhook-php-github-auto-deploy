<?php

class ErrorCode
{
    const EXECUTE_ERROR = 9999;
    const OK = 0;
    const ILLEGAL_REQUEST = 1;
    const SIGN_ERROR = 2;
    const OPERATE_NOT_MASTER = 3;
    const UNKNOWN_REPOSITORY = 4;
}

class WebHookUtil
{
    // webhook 设置的秘钥
    const SECRET = 'XXXXXXXXXXXXXXXXXXXXXXXX';
    protected $rawData;
    protected $data;
    protected $headers;
    protected $repository;
    protected $logFile = 'log.html';
    protected $deployPath = '/www/wwwroot/webhook';

    public function __construct()
    {
        $this->headers = $this->getHeaders();
        $this->rawData = $this->getContents();
        $this->writeLog();
    }

    public function run()
    {
        $this->checkRequestData();
        $this->checkSign();
        $this->handle();
    }

    /**
     *  获取请求头信息
     * @return array|false
     */
    private function getHeaders()
    {
        $headers = [];
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } elseif (function_exists('http_get_request_headers')) {
            $headers = http_get_request_headers();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strncmp($name, 'HTTP_', 5) === 0) {
                    $name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                    $headers[$name] = $value;
                }
            }
        }
        return $headers;
    }

    private function getContents()
    {
        return file_get_contents("php://input");
    }

    private function checkRequestData()
    {
        if (empty($this->headers) || empty($this->rawData) || !isset($this->headers['X-Hub-Signature'])) {
            $this->end(ErrorCode::ILLEGAL_REQUEST, '非法请求');
        }
        $this->data = json_decode($this->rawData, true);
        if (!isset($this->data['ref']) || strpos($this->data['ref'], 'refs/heads/master') === false) {
            $this->end(ErrorCode::OPERATE_NOT_MASTER, '不是更新master操作');
        }
        if (!isset($this->data['repository']['name'])) {
            $this->end(ErrorCode::UNKNOWN_REPOSITORY, '未识别的 repository');
        }
        $this->repository = $this->data['repository']['name'];
    }

    /**
     * @param $code
     * @param $data
     */
    private function end($code, $data = 'success')
    {
        echo json_encode(['code' => $code, 'message' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    private function checkSign()
    {
        $xHubSignature = sprintf('sha1=%s', hash_hmac('sha1', $this->rawData, static::SECRET));
        if (strcmp($this->headers['X-Hub-Signature'], $xHubSignature) !== 0) {
            $this->end(ErrorCode::SIGN_ERROR, '签名错误');
        }
    }

    //执行任务
    private function handle()
    {
        $cmd = "cd {$this->deployPath}; /bin/sudo ./auto_deploy.sh {$this->repository}";
        //后台执行nohup
        //$cmd = "cd {$this->deployPath}; /usr/bin/sudo nohup ./auto_deploy.sh {$this->repository} > /dev/null 2>&1 & echo $!";
        exec($cmd, $output, $return_var);
        ($return_var == 0) ? $this->end(ErrorCode::OK) : $this->end(ErrorCode::EXECUTE_ERROR, '执行出现异常');
    }

    private function writeLog()
    {
        is_file($this->logFile) || touch($this->logFile);
        $time = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$time]:" . $this->rawData . '<br><br>', FILE_APPEND);
    }
}

//开始执行
(new WebHookUtil())->run();
