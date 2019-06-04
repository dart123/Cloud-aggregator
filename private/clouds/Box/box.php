<?php
//$file = fopen(__DIR__."/response.json", 'w');
//fwrite($file, $result);
//fclose($file);
session_start();
require '../../DBManager.php';
$client_id = 'a5780b4ou18747qctc9r6vgvzoahyou1';
$client_secret = 'rRcfCSuFWwsgurIK7nOIdatoJurwzzY9';
$callbackUrl = "http://localhost/private/clouds/Box/box.php?f=callback";
switch($_GET['f']) {
case 'callback':
    callback();
    break;
case 'box_auth':
    box_auth();
    break;
case 'box_download':
    box_download_file($_GET['filename']);
    break;
case 'upload_file':
    box_upload_file();
    break;
case 'get_file_id':
    box_get_file_id($_GET['name']);
    break;
case 'delete_file':
    box_delete_file($_GET['filename'], $_GET['modified']);
    break;
case 'list_folder':
    box_get_files(get_token(3), $_GET['path'],  true);
    break;
}
function buildMultiPartRequest($ch, $boundary, $fields, $files, $token) {
    $delimiter = '-------------' . $boundary;
    $data = '';
    foreach ($fields as $name => $content) {
        $data .= "--" . $delimiter . "\r\n"
            . 'Content-Disposition: form-data; name="' . $name . "\"\r\n\r\n"
            . $content . "\r\n";
    }
    foreach ($files as $name => $content) {
        $data .= "--" . $delimiter . "\r\n"
            . 'Content-Disposition: form-data; name="' . $name . '"; filename="' . $name . '"' . "\r\n\r\n"
            . $content . "\r\n";
    }
    $data .= "--" . $delimiter . "--\r\n";
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer ".$token,
            'Content-Type: multipart/form-data; boundary=' . $delimiter,
            'Content-Length: ' . strlen($data)
        ],
        CURLOPT_POSTFIELDS => $data
    ]);
    //$file = fopen(__DIR__."/response1.json", 'w');
    //fwrite($file, print_r($data, true));
    //fclose($file);
    return $ch;
}

function box_auth() {
    global $client_id, $callbackUrl;
    $redirect_uri = 'https://account.box.com/api/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$callbackUrl.'&state=123';
    $redirect_uri = filter_var($redirect_uri, FILTER_SANITIZE_URL);
    echo $redirect_uri;
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
            $header = "Content-type: application/x-www-form-urlencoded";
        
            // Выполнение POST-запроса и вывод результата
            $opts = array('http' =>
              array(
              'method'  => 'POST',
              'header'  => $header,
              'content' => $query
              ) 
            );
            $context = stream_context_create($opts);
            $result = file_get_contents('https://api.box.com/oauth2/token', false, $context);
            $result = json_decode($result);
            if (!get_token(3))
            {
                $tmp = save_token($result->access_token, "0", 3);
                if (!$tmp) //если не сохранился токен
                {
                    $redirect_url = "../../../main_view.php";
                    header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
                }
            }
            else
                if (get_token(3) != $result->access_token)
                    update_token($result->access_token, 3);
                
            /*$files = */box_get_files($result->access_token, 0, false);
            //if ($files)
            //{
            //    save_file_signatures($files, "box");
            //}
            $redirect_url = "../../../main_view.php";
            header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
        }
        exit;
}
function box_get_file_id($name)
{
    $token = get_token(3);
    if (isset($token))
    {
        $header = Array("Authorization: Bearer ".$token);
        $request_uri = "https://api.box.com/2.0/search?query=".urlencode($name);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        $result = json_decode($result);
        $entry = $result->entries[0];
        $id = $entry->id;
        echo $id;
    }
}
function box_get_files($token, $path, $ajax)
{
    $shortFiles = box_list_folder($token, $path);
    $fullFiles = array();
    foreach($shortFiles->entries as $value):
        $header = Array("Authorization: Bearer ".$token);
        $opts = array('http' =>
            array(
                'method'  => 'GET',
                'header'  => $header,
                'ignore_errors' => true,
                //'content' => $query
            ) 
        );
        $context = stream_context_create($opts);
        if ($value->type == "file")
            $request_uri = 'https://api.box.com/2.0/files/'.$value->id;
        if ($value->type == "folder")
            $request_uri = 'https://api.box.com/2.0/folders/'.$value->id;
        $entry = file_get_contents($request_uri, false, $context);
        $entry = json_decode($entry);
        array_push($fullFiles, $entry);
    endforeach;
    if ($fullFiles)
    {
        delete_file(null, null, 3);
        save_file_signatures($fullFiles, "box");
        if ($ajax)
        {
            update_token(null, 3, $path); //обновить текущую папку
            echo "true";
        }
        else
            return $fullFiles;
    }
    else
        if ($ajax)
            echo "false";
        else
            return false;
    //return $fullFiles;
}
function box_list_folder($token, $path=0)
{
    if (isset($token)) {
        $header = Array("Authorization: Bearer ".$token);
        $opts = array('http' =>
            array(
            'method'  => 'GET',
            'header'  => $header,
            'ignore_errors' => true,
            //'content' => $query
            ) 
        );
        $context = stream_context_create($opts);
        $request_uri = "https://api.box.com/2.0/folders/$path";
        $result = file_get_contents($request_uri, false, $context);
        $result = json_decode($result);
        return $result->item_collection;
    }
}
function box_download_file($filename)
{
    global $current_folder;
    $token = get_token(3); //1й параметр - облако (3 - box), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = box_list_folder($token, get_current_folder(3, false));
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents->entries as $value):
                    if ($value->name == $filename)
                    {
                        $download_id = $value->id;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на скачивание
                if ($file_found == true)
                {
                    $header = Array("Authorization: Bearer ".$token);
                
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/files/$download_id/content");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $downloaded_file = fopen(__DIR__."/".$filename, 'wb');
                    fwrite($downloaded_file, $result);
                    fclose($downloaded_file);
                    header("Content-disposition: attachment;filename=".$filename);
                    header("Content-type: ".mime_content_type ($filename));
                    readfile($filename);
                    unlink(__DIR__."/".$filename);
                }
                else
                    echo false;
            }
    }
}
function box_upload_file()
{
    if (sizeof($_FILES) > 0)
    {
        $file_to_upload = $_FILES['file'];
        $token = get_token(3); //1й параметр - облако (2 - dropbox), 2й параметр - пользователь
        if (isset($token))
        {
            //$header = Array("Authorization: Bearer ".$token, "Content-Type: multipart/form-data");
            //$query = array(
            //  'attributes' => json_encode(array('name' => $file_to_upload['name'], 'parent' => array('id' => '0'))),
            //  'file' => file_get_contents($file_to_upload['tmp_name'], false),
            //);
            //$headers = [];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://upload.box.com/api/2.0/files/content');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//            curl_setopt($ch, CURLOPT_HEADERFUNCTION,
//  function($curl, $header) use (&$headers)
//  {
//    $len = strlen($header);
//    $header = explode(':', $header, 2);
//    if (count($header) < 2) // ignore invalid headers
//      return $len;
//
//    $name = strtolower(trim($header[0]));
//    if (!array_key_exists($name, $headers))
//      $headers[$name] = [trim($header[1])];
//    else
//      $headers[$name][] = trim($header[1]);
//
//    return $len;
//  }
//);
            //curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            //curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            //curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
            $ch = buildMultiPartRequest($ch, uniqid(),  //Токен проставляется здесь
                ['attributes' => json_encode(array('name' => $file_to_upload['name'], 'parent' => array('id' => '0')))],
                [$file_to_upload['name'] => file_get_contents($file_to_upload['tmp_name'], false)], $token);
            $result = curl_exec($ch);
            $file = fopen(__DIR__."/response.json", 'w');
            fwrite($file, $result);
            fclose($file);
            curl_close($ch);
            echo 'success';
        }
    }
}
function box_delete_file($filename, $modified)
{
    global $current_folder;
    $token = get_token(3); //1й параметр - облако (3 - box)
    if (isset($token)) {
            $folder_contents = box_list_folder($token, get_current_folder(3, false));
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents->entries as $value):
                    if ($value->name == $filename)
                    {
                        $delete_id = $value->id;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на скачивание
                if ($file_found == true)
                {
                    $header = Array("Authorization: Bearer ".$token);
                
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://api.box.com/2.0/files/$delete_id");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    $result = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    if ($httpCode == 204)
                    {
                        delete_file($filename, $modified);
                        $error_status = "204: success";
                        echo json_encode(array($error_status));
                    }
                }
                else
                    echo false;
            }
    }
}
?>

