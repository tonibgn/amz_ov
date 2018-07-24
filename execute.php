<?php
// inizio amazon api
require_once( 'AmazonAPI.php' );
$keyId 		= 'AKIAIYGRF2O5DYFVEUPA';
$secretKey 	= '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';
$associateId	= 'tebl0f-21';
$amazonAPI = new AmazonAPI( $keyId, $secretKey, $associateId );
$amazonAPI->SetLocale( 'it' );
$amazonAPI->SetRetrieveAsArray();
//fine amazon api

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$loginBitly="o_20dp36acoe";
$ApiBitly="R_ba00045734e84121a68e1f1194198f2f";

$ref='tebl0f-21';
if(!$update)
{
  exit;
}
function getASIN($url) {
	$pattern = "%/([a-zA-Z0-9]{10})(?:[/?]|$)%";
	preg_match($pattern, $url, $matches);
	if($matches && isset($matches[1])) {
		$asin = $matches[1];
	} else {
		echo "Couldn\'t parse url and extract ASIN: {$url}\n"; exit;
		return false;
	}
	return $asin;
}


$message = isset($update['message']) ? $update['message'] : "";
$messageId = isset($message['message_id']) ? $message['message_id'] : "";
$chatId = isset($message['chat']['id']) ? $message['chat']['id'] : "";
$firstname = isset($message['chat']['first_name']) ? $message['chat']['first_name'] : "";
$lastname = isset($message['chat']['last_name']) ? $message['chat']['last_name'] : "";
$username = isset($message['chat']['username']) ? $message['chat']['username'] : "";
$date = isset($message['date']) ? $message['date'] : "";
$text = isset($message['text']) ? $message['text'] : "";
$caption =isset($message['caption']) ? $message['caption'] : "";

if(strlen ($text) < 1)  $text = $caption;
//estrae il link dal messaggio
$n=preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $text, $match);
$text=$match[0][0];

//risolve l'url in caso di shortURL
$headers = get_headers($text);
$headers = array_reverse($headers);
foreach($headers as $header) {
	 if (stripos($header,'Location:') === 0) {
	$text = trim(substr($header, strlen('Location:')));
	break;
	}
}

//estrare il codice ASIN dal link
$asin = getASIN($text);
// chiamata api amazon
$items = $amazonAPI->ItemLookUp( $asin );

//nome articolo
$title = $items[0][title];
//link immagine articolo
$url_medium = $items[0][mediumImage];

$newurl = 'http://www.amazon.it/dp/'.$asin.'/?tag='.$ref;
//inserisce emoji
$emoticons = "\xF0\x9F\x8C\x90";
$emoticons =  json_decode('"'.$emoticons.'"');
//crea link per immagine (vecchio metodo)
//$emo='<a href="http://images.amazon.com/images/P/'.$asin.'.01.PI_SCLZZZZZZZ_.jpg">'.$emoticons.'</a>  ';

$emo='<a href="'.$url_medium.'">'.$emoticons.'</a>';
$br="\n";
$newurl = '<b>'.$title.'</b>'.$br.$emo.json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login=".$loginBitly."&apiKey=".$ApiBitly."&longUrl=".urlencode("".$newurl."")."&format=json"))->data->url.PHP_EOL.PHP_EOL;
header("Content-Type: application/json; charset: UTF-8");
$parameters = array('chat_id' => $chatId, "text" => $newurl, "parse_mode" => 'HTML');
$parameters["method"] = "sendMessage";
echo json_encode($parameters);

/*function ger_origenal_url($url)
{
    $ch = curl_init($url);
    curl_setopt($ch,CURLOPT_HEADER,true); // Get header information
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION,false);
    $header = curl_exec($ch);
    
    $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header)); // Parse information
        
    for($i=0;$i<count($fields);$i++)
    {
        if(strpos($fields[$i],'Location') !== false)
        {
            $url = str_replace("Location: ","",$fields[$i]);
        }
    }
    return $url;
} */
