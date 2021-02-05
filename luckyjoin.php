<?php

function isStart(){
    return file_exists('start.lock');
}

error_reporting(E_ALL ^ E_NOTICE); 
require 'vendor/autoload.php';
use Medoo\Medoo;
define('WX_MP_APPID', 'wx1cf63309abdfc8a7');
define('WX_MP_APPSECRET', '961e375148ce39a56b7af1aa551e329a');
$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'esiranlucky',
    'server' => '188.188.188.254',
    'username' => 'root',
    'password' => 'ruixiao123'
]);
function geturl($url) {
    $headerArray = [
        "Content-type: application/json;", 
        "Accept: application/json"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch,CURLOPT_HTTPHEADER, $headerArray);
    $output = curl_exec($ch);
    curl_close($ch);
    $output = json_decode($output,JSON_UNESCAPED_UNICODE);
    return $output;
}
function is_phone_number($number){
    return preg_match('/^1[3456789]\d{9}$/',$number,$matches);
}
function msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}
function current_page_url() {
  $pageURL = 'http';
  if (isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on") {
    $pageURL .= "s";
  }
  $pageURL .= "://";
  $pageURL .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  return $pageURL;
}

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD'])=='POST'){
    if(!isStart()){
        $out['code'] = 0;
        $out['message'] = '活动暂未开始';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    $number = $_POST['number'];
    $wxAuthCode = $_POST['wxAuthCode'];
    header('Content-Type:application/json; charset=utf-8');
    $out = [];
    if (empty($wxAuthCode)){
        $out['code'] = 0;
        $out['message'] = '授权失败请重试';
        $out['data'] = null;
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    if (empty($number)){
        $out['code'] = 0;
        $out['message'] = '请输入手机号码';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    if(!is_phone_number($number)){
        $out['code'] = 0;
        $out['message'] = '请输入正确的手机号码';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));      
    }
    $apihost = 'https://api.weixin.qq.com';
    $requrl = $apihost. '/sns/oauth2/access_token';
    $requrl .= '?appid='. WX_MP_APPID;
    $requrl .= '&secret='. WX_MP_APPSECRET;
    $requrl .= '&code=' . $wxAuthCode;
    $requrl .= '&grant_type=authorization_code';
    $data = geturl($requrl);
    $access_token = $data['access_token'];
    $openid = $data['openid'];
    if (!isset($access_token) || !isset($openid) ){
        $out['code'] = 0;
        $out['message'] = '授权失败，请重试';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    $requrl = $apihost . '/sns/userinfo';
    $requrl .= '?access_token='. $access_token;
    $requrl .= '&openid='. $openid;
    $requrl .= '&lang=zh_CN';
    $data = geturl($requrl);
    $openid = $data['openid'];
    $nickname = $data['nickname'];
    $avatar_url = $data['headimgurl'];

    if (!isset($openid)){
        $out['code'] = 0;
        $out['message'] = '授权失败，请重试';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    $data = $database->select("esiranlucky_member", [
        'nikename',
        'phone',
        'avatar_url',
        'joined_at',
        'created_at',
        'updated_at'
    ], [
        "OR" => [
            "openid" => $openid,
            "phone" => $phone
        ]
    ]);
    if (count($data) > 0) {
        $out['code'] = 0;
        $out['message'] = '您已经加入活动了';
        $out['data'] = null;
        exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    }
    $nickname = empty($nickname) ? '匿名用户': $nickname;
    $avatar_url = empty($avatar_url) ? 'https://cn.gravatar.com/avatar/04174be05578cacca7be50d219909910.jpeg?s=400&d=mp': $avatar_url;
    $ct = date("Y-m-d H:i:s");
    $ja = msectime();

    

    $database->insert('esiranlucky_member', [
        'nikename' => $nickname,
        'phone' => $number,
        'avatar_url' => $avatar_url,
        'access_token' => $access_token,
        'openid' => $openid,
        'joined_at' => $ja,
        'created_at' => $ct,
        'updated_at' => $ct
    ]);

    $out['code'] = 1;
    $out['message'] = '请求成功';
    $out['data'] = null;
    exit(json_encode($out,JSON_UNESCAPED_UNICODE));
    
} else {
    if(!isStart()){
        exit('抽奖进行中，无法加入');
    }

    if(empty($_GET['code'])){
        $cpurl = current_page_url();
        $redirect_uri = urlencode($cpurl);;
        $state = '1';
        $apihost = 'https://open.weixin.qq.com';
        $requrl = $apihost . '/connect/oauth2/authorize';
        $requrl .= '?appid='.WX_MP_APPID;
        $requrl .= '&redirect_uri=' . $redirect_uri;
        $requrl .= '&response_type=code';
        $requrl .= '&scope=snsapi_userinfo';
        $requrl .= '&state=' . $state;
        $requrl .= '#wechat_redirect';
        header('Location: ' . $requrl);
        exit();
    }
    $wxAuthCode = $_GET['code'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=0">
    <title>大数云 - 2021 分布式存储行业分享会</title>
    <style>
        html, body {
            height: 100%;
        }
        html{font-size:13.333vw}
        body {
            padding: 0;
            margin: 0;
        }
        * {
            box-sizing: inherit;
            -webkit-tap-highlight-color: transparent;
        }
        button, input, select, textarea {
            padding: 0;
            margin: 0;
            outline: none;
            border: none;
            font-size: inherit;
            font-family: inherit;
        }
    </style>
</head>
<body>
    <div style="
    background-image: url('https://s1.zhuanstatic.com/platform/ZZMWebsite/static/css/page03.6341e57.png');
    position: fixed;
    z-index: -1;
    height: 100%;
    width: 100%;
    "></div>
    <div style="
    padding: .2rem;
    ">
        <div style="
        margin-top: .5rem;
        font-size: .8rem;
        color: #fff;
        ">欢迎参加<br/>2021 | 大数云<br/>
        <span  style="
        font-size: .4rem;
        ">分布式存储行业分享会</span>
        </div>
        <div style="margin-top: 1.2rem;">
            <form id="postData" action="" method="post">
                <div>
                    <div style="
                    font-size: .46rem;
                    padding: .2rem;
                    background-color: #fff;
                    border-radius: .2rem;
                    ">
                        <input type="tel" name="number" id="number" 
                        placeholder="请输入手机号码"
                        maxlength="11"
                        style="
                        width: 100%
                        ">
                    </div>
                    <input type="hidden" name="wxAuthCode" value="<?= $wxAuthCode ?>">
                    <button style="
                    display: block;
                    width: 100%;
                    border-radius: .2rem;
                    background: #767bd7;
                    color: #fff;
                    padding: .1rem 0;
                    cursor: pointer;
                    text-align: center;
                    margin-top: .45rem;
                    font-size: .46rem;
                    ">立即提交</button>
                </div>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script>
        !function(){
            $("#postData").submit(function(e){
                let phoneNumber = $('input[name="number"]').val();
                if (!/^1[3456789]\d{9}$/.test(phoneNumber)){
                    alert('请输入正确的手机号码');
                    return false;
                }
                $.ajax({
                    type: "POST",
                    data: $("#postData").serialize(),
                    url: "/luckyjoin.php",
                    dataType: 'json',
                    success: function (data) {
                        let {code,message} = data;
                        if (code === 0){
                            if (message){
                                alert(message);
                                location.replace(location.href.replaceAll(/\?.+/g,''));
                                return;
                            }
                            alert('提交失败，请稍后重试');
                            location.replace(location.href.replaceAll(/\?.+/g,''));
                            return;
                        } else {
                            alert('提交成功');
                            location.replace(location.href.replaceAll(/\?.+/g,''));
                        }
                    },
                    error: function (data={responseJSON:null}) {
                        alert('提交失败，请稍后重试');
                        location.replace(location.href.replaceAll(/\?.+/g,''));
                    }
                });
                return false;
            });
        }();
    </script>
</body>
</html>
<?php } ?>