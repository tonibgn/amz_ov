<?php
$content = file_get_contents("php://input");
$update = json_decode($content, true);


//~ $par = array('chat_id' => $chatId, "text" => "ss");
//~ $par["method"] = "sendMessage";
//~ echo json_encode($par);


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
//$newurl = 'http://www.amazon.it/dp/'.$asin.'/?tag=tebl0f-21';
$newurl = 'http://www.amazon.it/dp/'.$asin.'/?tag='.$ref;
//crea lo shortURL e lo invia
$newurl = json_decode(file_get_contents("http://api.bit.ly/v3/shorten?login=".$loginBitly."&apiKey=".$ApiBitly."&longUrl=".urlencode("".$newurl."")."&format=json"))->data->url;
header("Content-Type: application/json");
$parameters = array('chat_id' => $chatId, "text" => $newurl);
$parameters["method"] = "sendMessage";
echo json_encode($parameters);

function ger_origenal_url($url)
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
}
