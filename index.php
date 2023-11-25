
<?php
error_reporting(0); // 关闭第一次请求出错时的警告

$request_method = $_SERVER['REQUEST_METHOD'];
$request_query = $_SERVER['QUERY_STRING'];
$request_uri = $_SERVER['REQUEST_URI'];
$req_referer = $_SERVER['HTTP_REFERER'];
$request_headers = getallheaders();
$request_body =trim(file_get_contents('php://input'));
$url= 'https://api.bilibili.com/'.$request_uri;
$curl = curl_init();
if ($request_method=="POST"){
	curl_setopt($curl,CURLOPT_POSTFIELDS, $request_body);
}
$pos = strpos($req_referer,'/',10);
$referer = substr($req_referer,0,$pos);
curl_setopt_array($curl, array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => "",
	CURLOPT_SSL_VERIFYPEER => false,
	CURLOPT_SSL_VERIFYHOST => false,
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_CONNECTTIMEOUT => 2,
	CURLOPT_TIMEOUT => 6,
	CURLOPT_CUSTOMREQUEST => $method,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_POST => $request_method=="POST",
	CURLOPT_REFERER => $req_referer,
	CURLOPT_HTTPHEADER => $request_headers
));
$response = curl_exec($curl);
$info = curl_getinfo($curl);
//$info["errno"] = curl_errno($curl);
//$info["error"] = curl_error($curl);
//$info["request"] = json_encode($fields);
//$info["response"] = $response;
curl_close($curl);
header('access-control-allow-credentials: true');
header('access-control-allow-origin: '.$referer);
header('access-control-allow-headers: *');
header('cache-control: no-cache');
header('content-type: application/json');
echo $response;
?>

