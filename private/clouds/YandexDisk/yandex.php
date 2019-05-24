<?php
session_start();
require '../../DBManager.php';
    // Идентификатор приложения
$client_id = 'd94f009c98d04b02b13d0912667ff44b'; 
// Пароль приложения
$client_secret = 'cd3d07901f6e40c78a27f180be0cf7fb';
$current_folder = "/";
switch($_GET['f']) {
case 'callback':
    callback();
    break;
case 'yandex_auth':
    yandex_auth();
    break;
case 'download_file':
    yandex_download_file($_GET['filename']);
    break;
case 'upload_file':
    yandex_upload_file();
    break;
}
function yandex_auth() {
    $redirect_uri = 'https://oauth.yandex.ru/authorize?response_type=code&client_id='.$GLOBALS['client_id'];
    $redirect_uri = filter_var($redirect_uri, FILTER_SANITIZE_URL);
    echo $redirect_uri;
    //header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    exit;
}
function callback() {
    if (isset($_GET['code']))
        {
            // Формирование параметров (тела) POST-запроса с указанием кода подтверждения
            $query = array(
              'grant_type' => 'authorization_code',
              'code' => $_GET['code'],
              'client_id' => $GLOBALS['client_id'],
              'client_secret' => $GLOBALS['client_secret']
            );
            $query = http_build_query($query);
        
            // Формирование заголовков POST-запроса
            $header = Array("Content-type: application/x-www-form-urlencoded");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth.yandex.ru/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $result = curl_exec($ch);
            curl_close($ch);
            $result = json_decode($result);
            debug_to_file("yandex session: ".$_SESSION['session_id']);
            if (!get_token(1))
                save_token($result->access_token, 1);
            else
                if (get_token(1) != $result->access_token)
                    update_token($result->access_token, 1);
            $files = yandex_list_folder("/", $result->access_token);
            if ($files)
                save_file_signatures($files, "yandex");
            $redirect_url = "../../../main_view.php";
            //header('Content-Type: text/event-stream');  Send server event
            //header('Cache-Control: no-cache');
            header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
            //echo "data: yandex\n\n";
            //flush();
        }
        exit;
}
//function yandex_get_files()
//{
//    global $token;
//    if (isset($token)) {
//        
//            $header = "Authorization: OAuth ".$token;
//            
//            $opts = array('http' =>
//              array(
//              'method'  => 'GET',
//              'header'  => $header,
//              //'content' => $query
//              ) 
//            );
//            $context = stream_context_create($opts);
//            $result = file_get_contents('https://cloud-api.yandex.net/v1/disk/resources/files?limit=50', false, $context);
//            $result = json_decode($result);
//            yandex_get_folder_contents($token);
//            return $result->items;
//    }
//}
function yandex_list_folder($path, $token)
{
    if (isset($token)) {
        $header = Array("Authorization: OAuth ".$token);
        $opts = array('http' =>
            array(
            'method'  => 'GET',
            'header'  => $header,
            'ignore_errors' => true,
            //'content' => $query
            ) 
        );
        $context = stream_context_create($opts);
        $request_uri = "https://cloud-api.yandex.net/v1/disk/resources?path=".urlencode($path)."&limit=50";
        $result = file_get_contents($request_uri, false, $context);
        $result = json_decode($result);
        $embedded = $result->_embedded;
        return $embedded->items;
    }
}
function yandex_download_file($filename)
{
    global $current_folder;
    $token = get_token(1); //1й параметр - облако (1 - яндекс), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = yandex_list_folder("/", $token);
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents as $value):
                    if ($value->name == $filename)
                    {
                        $download_path = $value->path;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на получение url для скачивания
                if ($file_found == true)
                {
                    $header = array("Authorization: OAuth ".$token);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources/download?path='.urlencode($download_path));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $result = json_decode($result);
                    echo $result->href;
                    exit;
                }
                else
                {
                    echo false;
                    exit;
                }
            }
    }
}
function yandex_upload_file()
{
    if (sizeof($_FILES) > 0)
    {
        $file_to_upload = $_FILES['file'];
        $token = get_token(1); //1й параметр - облако (1 - яндекс), 2й параметр - пользователь
        if (isset($token))
        {
            $header = array("Authorization: OAuth ".$token);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources/upload?path='.urlencode("/".$file_to_upload['name']));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $result = curl_exec($ch);
            $result = json_decode($result);
            $upload_url = $result->href;
            curl_setopt($ch, CURLOPT_URL, $result->href);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array());
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file_to_upload['tmp_name'], false));
            $result = curl_exec($ch);
            curl_close($ch);
            echo 'success';
            exit;
        }
    }
}
?>

