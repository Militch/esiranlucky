

<?php

define('WX_MP_APPID', 'wx1cf63309abdfc8a7');
define('WX_MP_APPSECRET', '961e375148ce39a56b7af1aa551e329a');
function isStart(){
    return file_exists('start.lock');
}
function start(){
    $f = fopen("start.lock", "w") or false;
    if ($f){
        fclose($f);
    }
}
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
function jsonposturl($url, $data){

    $headerArray = [
        "Content-type: application/json;", 
        "Accept: application/json"
    ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch,CURLOPT_HTTPHEADER, $headerArray);
    $data = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($data,JSON_UNESCAPED_UNICODE);
    return $data;
}

$apihost = 'https://api.weixin.qq.com';
// 获取 access_token
$rq_url = $apihost . '/cgi-bin/token';
$rq_url .= '?grant_type=client_credential';
$rq_url .= '&appid=' . WX_MP_APPID;
$rq_url .= '&secret=' . WX_MP_APPSECRET;
$data = geturl($rq_url);
$access_token = $data['access_token'];
// 创建临时二维码
$rq_url = $apihost . '/cgi-bin/qrcode/create';
$rq_url .= '?access_token=' . $access_token;
$data = jsonposturl($rq_url, [
    'expire_seconds' => '1440',
    'action_name' => 'QR_SCENE',
    'action_info' => [
        'scene' => [
            'scene_id' => 1
        ]
    ]
]);
$qr_ticket = $data['ticket'];
$qr_url = $data['url'];
$qr_img_url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
$qr_img_url .= '?ticket='. $qr_ticket;
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>大数云 - 2021-分布式存储行业分享会</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid" style="padding: 100px 50px; ">
        <div class="row">
            <div class="col-3">
                <div>
                    <div style="
                    text-align: center;
                    ">
                        <h2>扫码关注公众号<br/>参与活动抽奖</h2>
                    </div>
                    
                    <!-- <h2></h2> -->
                    <img src="<?= $qr_img_url ?>" alt="活动二维码" style="width: 100%">
                    <button id="startBtn" type="button" class="btn btn-primary btn-lg btn-block">开启活动</button>
                    <button id="runBtn" type="button" class="btn btn-primary btn-lg btn-block">开始抽奖</button>
                </div>
            </div>
            <div class="col-8">
                <div>
                    <table class="table" id="membertable">
                        <thead>
                            <tr>
                                <th>#</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- 
                            <tr>
                                <th>
                                    <div style="width: 100px;float: left;margin-right: 15px;">
                                        <img src="https://cn.gravatar.com/avatar/04174be05578cacca7be50d219909910.jpeg?s=400&d=mp" class="rounded" style="width: 100%; height:100%">
                                    </div>
                                    <div>
                                        <h4>Militch</h4>
                                        <div>
                                            185****3898
                                        </div>
                                        <div>
                                            2020-09-09 11:10:11
                                        </div>
                                    </div>
                                </th>
                            </tr> 
                            -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">开始抽奖</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                    <div>
                        <div id="luckyBody">
                                <div style="width: 100px;margin-right: 15px; display: inline-block">
                                <img src="https://cn.gravatar.com/avatar/04174be05578cacca7be50d219909910.jpeg?s=400&d=mp" class="rounded" style="width: 100%; height:100%">
                            </div>
                            <div style="
                            margin-right: 20px;
                            display: inline-block;
                            font-size: 3rem;
                            vertical-align: middle;
                            ">******</div>
                            <div style="
                            display: inline-block;
                            font-size: 3rem;
                            vertical-align: middle;
                            ">***********</div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
                <button id="runLuckyBtn" type="button" class="btn btn-primary">开始抽奖</button>
                <button id="stopLuckyBtn" type="button" class="btn btn-primary">暂停</button>
            </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        !function(){
            let memberData = [];
            $('#startBtn').on('click', function(){
                $.get(`/start.php`,function(data){
                    alert('活动已开始');
                });
            });
            $('#runBtn').on('click', function(){
                $.get(`/run.php`,function(data){
                    $('#exampleModal').modal({
                        backdrop: 'static',
                        keyboard: false
                    })
                });
            });

            Array.prototype.remove = function(v) {
                if(isNaN(v) || v > this.length){
                    return false
                }
                for(let i = 0, j = 0; i < this.length; i++) {
                    if(this[i] != this[v]){
                        this[j++] = this[i]
                    }
                }
                this.length -= 1
            }
            function showLuckyBody({avatar_url,nikename,phone}){
                // console.log(data);
                let dom = [
                    `<div id="luckyBody">`,
                    '<div style="width: 100px;margin-right: 15px; display: inline-block">',
                        `<img src="${avatar_url}" class="rounded" style="width: 100%; height:100%">`,
                    '</div>',
                    `<div style="`,
                    `margin-right: 20px;`,
                    `display: inline-block;`,
                    `font-size: 3rem;`,
                    `vertical-align: middle;`,
                    `">${nikename}</div>`,
                    `<div style="`,
                    `display: inline-block;`,
                    `font-size: 3rem;`,
                    `vertical-align: middle;`,
                    `">${phone}</div>`,
                    `</div>`
                ].join('');
                let domelem = $(dom);
                domelem.replaceAll('#luckyBody');
                // $('#luckBody').replaceWith($(dom));
            }
            let currentIndex = -1;
            let intvId = false;
            $('#runLuckyBtn').on('click', function(){
                let max = memberData.length;
                intvId = setInterval(function(){
                    let index = Math.floor(Math.random()*max);
                    currentIndex = index;
                    showLuckyBody(memberData[index]);
                },100);
            });
            $('#stopLuckyBtn').on('click', function(){
                intvId = clearInterval(intvId);
                memberData.remove(currentIndex);
            });
            $('#exampleModal').on('hidden.bs.modal', function (event) {
                intvId = clearInterval(intvId);
                // console.log('关闭');
            })
            function addMember({nikename,phone,created_at,avatar_url}){
                let dom = [
                    '<tr>',
                        '<th>',
                            '<div style="width: 100px;float: left;margin-right: 15px;">',
                                `<img src="${avatar_url}" class="rounded" style="width: 100%; height:100%">`,
                            '</div>',
                            '<div>',
                                `<h4>${nikename}</h4>`,
                                `<div>${phone}</div>`,
                                `<div>${created_at}</div>`,
                            '</div>',
                        '<th>',
                    '</tr>',
                ].join('');
                let domelem = $(dom);
                let table = $("#membertable tbody");
                domelem.prependTo(table);
                memberData.push({avatar_url,nikename,phone});
            }

            let start = 0;
            setInterval(function(){
                let end = new Date().getTime();
                $.get(`/data.php?start=${start}&end=${end}`,function(resp){
                    let {data} = resp;
                    start = end;
                    if (data.length == 0){
                        return;
                    }
                    for(let i=0;i<data.length;i++){
                        let item = data[i];
                        addMember(item);
                    }
                });
            },1000);
        }();
    </script>
</body>
</html>