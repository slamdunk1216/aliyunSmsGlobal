<?php
// 阿里云认证信息
$accessKeyId = '你的AccessKeyId';
$accessKeySecret = '你的AccessKeySecret';

// 短信参数配置
$params = [
    // 系统参数
    'RegionId' => 'ap-southeast-1',      // 国际短信服务区域
    'AccessKeyId' => $accessKeyId,
    'Format' => 'JSON',
    'SignatureMethod' => 'HMAC-SHA1',
    'SignatureVersion' => '1.0',
    'SignatureNonce' => uniqid(),
    'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
    'Version' => '2018-05-01',
    
    // 业务参数
    'Action' => 'SendMessageToGlobe',    // 国际短信接口名称
    'To' => '85261234567',              // 带国家码的手机号
    'Message' => 'Your verification code: 1234 [Test]', // 直接填写短信内容
    'From' => 'ALIYUN'                  // 可选发信方标识
];

// 生成签名
$signature = createSignature($params, $accessKeySecret);
$params['Signature'] = $signature;

// 发送请求
$result = sendRequest('https://dysmsapi.ap-southeast-1.aliyuncs.com', $params);

// 处理响应
$response = json_decode($result, true);
if(isset($response['ResponseCode']) && $response['ResponseCode'] == 'OK') {
    echo "短信发送成功，消息ID：".$response['MessageId'];
} else {
    echo "发送失败，错误信息：".$response['Message'] ?? '未知错误';
}

/**
 * 签名生成函数 (与之前相同)
 */
function createSignature($params, $accessKeySecret) {
    ksort($params);
    $queryString = '';
    foreach ($params as $key => $val) {
        $queryString .= '&' . percentEncode($key) . '=' . percentEncode($val);
    }
    $stringToSign = 'POST&%2F&' . percentEncode(substr($queryString, 1));
    return base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
}

/**
 * 发送POST请求
 */
function sendRequest($url, $params) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($params),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

/**
 * 特殊字符编码处理
 */
function percentEncode($str) {
    $res = urlencode($str);
    return str_replace(['+', '*', '%7E'], ['%20', '%2A', '~'], $res);
}
?>