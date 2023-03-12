<?php

new ultraMsgChatBot("hfra7ktkzmbfl0fv", "instance28318");

class ultraMsgChatBot
{
    var $client;

    public function __construct($ultramsg_token, $instance_id)
    {
        //incloud ultramsg class
        require_once('vendor/autoload.php'); // if you use Composer
        //composer require ultramsg/whatsapp-php-sdk

        require_once 'phpQuery-onefile.php'; // phpQuery init file

        require_once('ultramsg.class.php'); // UlTRAMSG.INIT

        require_once('ultramsg-dictionary.php'); //Include a dictionary to generate random words and sentences

        require_once("controller/test.php");
        require_once("model/DB.php");
        require_once("model/WhatsAppBot.php");

        $ultramsgDictionary = new ultramsgDictionary();
        $this->client = new UltraMsg\WhatsAppApi($ultramsg_token, $instance_id);
        $json = file_get_contents('php://input');
        $decoded = json_decode($json, true);

        if (isset($decoded['data'])) {
            $message = $decoded['data'];
            $text = $this->convert($message['body']);
            if (!$message['fromMe']) {
                $to = $message['from'];
                $val = mb_strtolower($text, 'UTF-8');
                if (preg_match("/start.+,\s.+|start.+,.+/", $val, $match)) {
                    $filtredData = preg_split("/[,\s]+/", implode($match));
                    unset($filtredData[0]);
                    $message = $filtredData[1].', '.$filtredData[2];
                    $number = preg_replace("/[^0-9]/", '', $to);
                    $telegramBot = new \testController\Test();
                    $telegramBot->addUser('+'.$number,$message);
                    $this->client->sendChatMessage($to, '*Schedule messages started*');
                    exit();
                }
                if (preg_match("/.+,\s.+|.+,.+/", $val, $match)) {
                    $filtredData = preg_split("/[,\s]+/", implode($match));
                    $city = $filtredData[0];
                    $price = $filtredData[1];
                    $data = $this->rentInfo($city, $price);
                    if (empty($data)) {
                        $data[] = 'Nothing to rent';
                    }
                    $data[] = "\n✅ Book a viewing: http://surl.li/ehknp";
                    $stringData = implode("\n\n", $data);
                    $this->client->sendChatMessage($to, $stringData);
                    exit();
                }
                switch ($val) {
                    case in_array($val, $ultramsgDictionary->welcomeIntent()):
                    {
                        $randMsg = $ultramsgDictionary->welcomeResponses();
                        $this->client->sendChatMessage($to, $randMsg);
                        break;
                    }
                    case 'start':
                    {
                        $this->client->sendChatMessage($to, '*WhatsAppBot is working now.*');
                        break;
                    }
                    case 'stop':
                    {
                        $number = preg_replace("/[^0-9]/", '', $to);
                        $telegramBot = new \testController\Test();
                        $telegramBot->delete('+'.$number);
                        $this->client->sendChatMessage($to, '*Schedule messages stopped*');
                        exit();
                    }
                    // Incorrect command
                    case 'help':
                    default:
                    {
                        $this->welcome($message['from'], true);
                        break;
                    }
                }
            }
        }
    }

    public function rentInfo($city, $price)
    {
        $domain = 'https://www.funda.nl';
        $url = "https://www.funda.nl/huur/$city/$price/sorteer-datum-af/";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $headers = [
            "authority: " . explode('://', $domain)[1],
            "accept: */*",
            "accept-language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,uk;q=0.6",
            "content-type: application/x-www-form-urlencoded; charset=UTF-8",
            "referer: " . $domain . "/huur/heel-nederland/p3/",
            "sec-ch-ua: ^\^\"Not?A_Brand^\^\";v=^\^\"8^\^\", ^\^\"Chromium^\^\";v=^\^\"108^\^\", ^\^\"Google Chrome^\^\";v=^\^\"108^\^\"",
            "sec-ch-ua-mobile: ?0",
            "sec-ch-ua-platform: ^\^\"Windows^\^\"",
            "sec-fetch-dest: empty",
            "sec-fetch-mode: cors",
            "sec-fetch-site: same-origin",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36",
            "x-requested-with: XMLHttpRequest",
        ];
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/89.0.4389.90 Safari/537.36");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $page = curl_exec($ch);
        curl_close($ch);

        $returnDataArray = [];
        $pq = phpQuery::newDocument($page);
        $data = array();
        $rentSize = array();
        $priceData = array();
        $filtrArr = array();

        $listLinks = $pq->find('.search-result__header-title-col a');
        foreach ($listLinks as $link) {
            $data[] = $domain . pq($link)->attr('href');
        }
        $filtredData = array_unique($data);

        foreach ($filtredData as $itemData){
            $filtrArr[] = $itemData;
        }
        
        $returnDataArray["Links"] = $filtrArr;

        $priceList = $pq->find('.search-result-info-price span');
        foreach ($priceList as $price) {
            if (preg_match("/.\s.+\s.mnd/", pq($price)->text())) {
                $priceData[] = pq($price)->text();
            }
        }
        $returnDataArray["Price"] = $priceData;

        $sizeList = $pq->find('.search-result-kenmerken li span');
        foreach ($sizeList as $size) {
            $rentSize[] = pq($size)->text();
        }
        $returnDataArray["Size"] = $rentSize;

        $finallArr = array();
        for($i = 0 ;$i<count($returnDataArray['Links']);$i++){
            $finallArr[] = 'Price: '.$returnDataArray['Price'][$i].'. Size: '.$returnDataArray['Size'][$i].'. Link: '.$returnDataArray['Links'][$i];
        }
        return $finallArr;
    }

    public function welcome($to, $noWelcome = false)
    {
        $welcomeStr = ($noWelcome) ? "```📢 How to use the bot 📢 ```\n\nPlease type one of these *commands*:\n" : "Welcome to WhatsAppBot\n";
        $this->client->sendChatMessage(
            $to,
            $welcomeStr .
            "\n" .
            "information input format : *city, price*\n\n" .
            "example of input info : *heel-nederland, 1000-3000*\n\n" .
            "example of input info : *amsterdam, 5000+*\n\n" .
            "example of input info : *oudenbosch,3000-5000*\n\n" .
            "start : Check if the bot is working.\n\n" .
            "random text or help : *GuideBook.*\n\n".
            "example schedule messages start: Start *amsterdam, 4000-5000*\n\n" .
            "example schedule messages stop: *Stop*\n\n"
        );
    }

    //convert Arabic/Persian numbers to English 
    public function convert($string)
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٩', '٨', '٧', '٦', '٥', '٤', '٣', '٢', '١', '٠'];
        $num = range(0, 9);
        $convertedPersianNums = str_replace($persian, $num, $string);
        $englishNumbersOnly = str_replace($arabic, $num, $convertedPersianNums);
        return $englishNumbersOnly;
    }
}