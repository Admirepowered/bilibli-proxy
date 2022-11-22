<?php

/* Config */

$upstream_pc_url = 'https://api.bilibili.com/pgc/player/web/playurl';
$upstream_app_url = 'https://api.bilibili.com/pgc/player/api/playurl';
$upstream_pc_search_url = 'https://api.bilibili.com/x/web-interface/search/type';
$timeout = 20; // seconds


/* Read incoming request */
$request_method = $_SERVER['REQUEST_METHOD'];
$request_query = $_SERVER['QUERY_STRING'];
$request_uri = $_SERVER['REQUEST_URI'];
$req_referer = $_SERVER['HTTP_REFERER'];
$request_headers = getallheaders();
$request_body = file_get_contents('php://input');


/*tool*/
//某个字符串在另一个字符串第N此出现的下标
function str_n_pos($str,$find,$n)
{
    $pos_val=0;
        for ($i=1;$i<=$n;$i++){
            $pos = strpos($str,$find);
            $str = substr($str,$pos+1);
            $pos_val=$pos+$pos_val+1;
        }
	$count = $pos_val-1;
	return $count; 
}

function array_remove_by_key($arr, $key)
{
	if(!array_key_exists($key, $arr)){
		return $arr;
	}
	$keys = array_keys($arr);
	$index = array_search($key, $keys);
	if($index !== FALSE){
		array_splice($arr, $index, 1);
	}

	return $arr;
}

/* Forward request */
$ch = curl_init();

//处理请求相关header
$request_headers = array_remove_by_key($request_headers,'Host');
$request_headers = array_remove_by_key($request_headers,'X-Forwarded-For');
//配置body压缩方式
$request_headers = array_remove_by_key($request_headers,'Accept-Encoding');
curl_setopt($ch, CURLOPT_ENCODING, "identity");//好像b站只有br压缩

$headers = array();
foreach ($request_headers as $key => $value) {
	$headers[] = $key . ': ' . $value;
}

//判断请求接口
if(substr_count($request_uri,'/search/type')!=0){
	$url = $upstream_pc_search_url . '?' .$request_query;
	curl_setopt($ch, CURLOPT_REFERER, $req_referer);
}else {//(substr_count($request_uri,'playurl')!=0)
	//判断使用的那个pc还是app接口
	if(substr_count($request_query,'platform=android')!=0 or substr_count($request_uri,'platform=android')!=0){
		$url = $upstream_app_url . '?' .$request_query;
		//echo "Andriod";
		curl_setopt($ch, CURLOPT_USERAGENT, 'Bilibili Freedoooooom/MarkII');
	}else{
		$url = $upstream_pc_url . '?' .$request_query;
		curl_setopt($ch, CURLOPT_REFERER, $req_referer);
	}
}
//else{

	//header('HTTP/1.1 502 Bad Gateway');
	//header('Content-Type: text/plain');
	//echo 'Failed to match interface./r/n';
	//return 1;
//}

//url配置
curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request_method);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $request_body);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch,CURLOPT_COOKIE,"buvid3=F54DEBEB-2AA2-69F3-E980-E38D0BE42A8A04776infoc; b_nut=1668848904; i-wanna-go-back=-1; _uuid=AEECD326-A5BC-429F-714C-784922F9ACED05981infoc; buvid4=30C8B913-BDC7-A36C-D688-4EF8A4F3AFD506575-022111917-s1zY4ydn84P69JTvfphvCw%3D%3D; fingerprint=2cba38bcd5a7a9ed4720b68acb2ce870; buvid_fp_plain=undefined; DedeUserID=6942283; DedeUserID__ckMd5=e956942488750d72; rpdid=|(um)~JRJ~Rl0J'uYY))R)mR~; b_ut=5; nostalgia_conf=-1; LIVE_BUVID=AUTO4416688790338846; SESSDATA=f6a6f908%2C1684650405%2C0ec27%2Ab1; bili_jct=f2b2704cd8b57cfa245f7378fd02f4dc; sid=89tiffr9; theme_style=light; b_lsid=72DDB1013_1849F4F57FE; innersign=1; PVID=2; CURRENT_BLACKGAP=0; buvid_fp=2cba38bcd5a7a9ed4720b68acb2ce870; balh_server_inner=__custom__; balh_is_closed=; balh_server_custom=https://admire.iuo.ink/; balh_server_custom_hk=https://admire.iuo.ink/; balh_curr_season_id=5551; balh_upos_server=ks3; balh_=ks3; CURRENT_FNVAL=80; blackside_state=1; bp_video_offset_6942283=731358666420125700; balh_season_ss43772=1; balh_season_ss39046=1; balh_season_ss42402=1");


$response = curl_exec($ch);


if ($response === false) {
	header('HTTP/1.1 502 Bad Gateway');
	header('Content-Type: text/plain');
	echo 'Upstream host did not respond.';
} else {
	

	$header_length = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$response_headers = explode("\n", substr($response, 0, $header_length));
	$response_body = substr($response, $header_length);
	//处理返回相关header
	header('access-control-allow-credentials: true');
	header('access-control-allow-origin: '. substr( $req_referer,0,str_n_pos($req_referer,'/',3)));
	header('access-control-allow-headers: *');
	header('cache-control: no-cache');
	header('content-type: application/json');
	echo $response_body;
	
	return;
	foreach ($response_headers as $n => $response_header) {
		//配置返回的body压缩方式
        //if (strpos($response_header, "Content-Encoding") !== false) {
        //    $response_headers[$n] = "Content-Encoding: identity\n";
        //}
		//删除B站返回的Content-Length,防止浏览器只读取Content-Length长度的数据,造成json不完整
		if (strpos($response_header, "Content-Length") !== false) {
            unset($response_headers[$n]);
			header('Content-Length: '.strlen($response_body));
        }
    }
	unset($response_header); 
	
	
	/*跨域问题*/

	
	foreach ($response_headers as $header) {
		$header = trim($header);
		if ($header) {
			header(trim($header));
		}
	}
	echo $response_body;
}

curl_close($ch);
?>
