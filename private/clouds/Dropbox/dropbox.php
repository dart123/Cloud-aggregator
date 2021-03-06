<?php
require '../../DBManager.php';

session_start();

$client_id = 'rg9ydnukkym00xp';
$client_secret = '0zrxv8vhd79scl1';
$callbackUrl = "http://localhost/cloud_aggregator/private/clouds/Dropbox/dropbox.php?f=callback";
switch($_GET['f']) {
case 'dropbox_auth':
    dropbox_auth();
    break;
case 'callback':
    callback();
    break;
case 'download_file':
    dropbox_download_file($_GET['filename']);
    break;
case 'upload_file':
    dropbox_upload_file();
    break;
case 'delete_file':
    dropbox_delete_file($_GET['filename'], $_GET['modified']);
    break;
case 'list_folder':
    dropbox_list_folder($_GET['path'], DBManager::get_token(2), true);
    break;
}
    
function dropbox_auth() {
    global $client_id, $callbackUrl;
    $redirect_uri = 'https://www.dropbox.com/oauth2/authorize?response_type=code&client_id='.$client_id.'&redirect_uri='.$callbackUrl;
    $redirect_uri = filter_var($redirect_uri, FILTER_SANITIZE_URL);
    echo $redirect_uri;
    exit;
}
function callback() {
    if (isset($_GET['code'])) { 
        global $client_id, $client_secret, $callbackUrl;
        $query = array(
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => $callbackUrl,
        );
        $query = http_build_query($query);
        
        // Формирование заголовков POST-запроса
        $header = Array("Content-type: application/x-www-form-urlencoded");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.dropboxapi.com/oauth2/token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result);
        if (!DBManager::get_token(2))
            DBManager::save_token($result->access_token, "", 2);
            else
                if (DBManager::get_token(2) != $result->access_token)
                    DBManager::update_token($result->access_token, 2);
        /*$files = */dropbox_list_folder("", $result->access_token, false);
        //if ($files)
        //{
        //    save_file_signatures($files, "dropbox");
        //}
        $redirect_url = "../../../main_view.php";
        header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
        exit;
    }
}
function dropbox_list_folder($path, $token, $ajax)
{
    if (isset($token)) {       
        $query = array(
            'path' => $path == "/" ? "" : $path,
        );
        $query = json_encode($query);
        
        $header = Array("Authorization: Bearer ".$token, "Content-Type: application/json");
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.dropboxapi.com/2/files/list_folder");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result);
        if ($result->entries)
        {
            DBManager::delete_file(null, null, 2);
            DBManager::save_file_signatures($result->entries, "dropbox");
            if ($ajax)
            {
                DBManager::update_token(null, 2, $path); //обновить текущую папку
                echo "true";
            }
            else
                return $result->entries;
        }
        else
            if ($ajax)
                echo "false";
            else
                return false;
        //return $result->entries;
        ////Fetch Cusrsor for listFolderContinue()
        //$cursor = $listFolderContents->getCursor();
        ////If more items are available
        //$hasMoreItems = $listFolderContents->hasMoreItems();
    }
}
function dropbox_download_file($filename)
{
    global $current_folder;
    $token = DBManager::get_token(2); //1й параметр - облако (2 - dropbox), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = dropbox_list_folder(DBManager::get_current_folder(2, false), $token, false);
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents as $value):
                    if ($value->name == $filename)
                    {
                        $download_path = $value->path_display;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на скачивание
                if ($file_found == true)
                {
                    $header = Array("Authorization: Bearer ".$token, "Dropbox-API-Arg: ". json_encode(array('path' => $download_path)),
                                    "Content-Type: text/plain");
                
                    // Выполнение POST-запроса и вывод результата
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/download');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    $result = curl_exec($ch);
                    curl_close($ch);
                    $downloaded_file = fopen(__DIR__."/".$filename, 'wb');
                    fwrite($downloaded_file, $result);
                    fclose($downloaded_file);
                    header("Content-disposition: attachment;filename=".$filename);
                    header("Content-type: ".mime_content_type($filename));
                    readfile($filename);
                    unlink(__DIR__."/".$filename);
                }
                else
                    echo false;
            }
    }
}
function dropbox_upload_file()
{
    if (sizeof($_FILES) > 0)
    {
        $file_to_upload = $_FILES['file'];
        $token = DBManager::get_token(2); //1й параметр - облако (2 - dropbox), 2й параметр - пользователь
        if (isset($token))
        {
            $header = Array("Authorization: Bearer ".$token, "Dropbox-API-Arg: ". json_encode(array('path' => "/".$file_to_upload['name'],
                                                                                                    'mode' => 'add',
                                                                                                    )),
                            "Content-Type: application/octet-stream");
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/upload');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file_to_upload['tmp_name'], false));
            $result = curl_exec($ch);
            curl_close($ch);
            echo 'success';
        }
    }
}
function dropbox_delete_file($filename, $modified)
{
    global $current_folder;
    $token = DBManager::get_token(2); //1й параметр - облако (1 - яндекс), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = dropbox_list_folder(DBManager::get_current_folder(2, false), $token, false);
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents as $value):
                    if ($value->name == $filename)
                    {
                        $delete_path = $value->path_display;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на скачивание
                if ($file_found == true)
                {
                    $header = Array("Authorization: Bearer ".$token, "Content-Type: application/json");
                
                    // Выполнение POST-запроса и вывод результата
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://api.dropboxapi.com/2/files/delete_v2');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array('path' => $delete_path)));
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    $result = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $result = json_decode($result);
                    // Check the HTTP Status code
                    switch ($httpCode) {
                        case 200:
                            DBManager::delete_file($filename, $modified);
                            $error_status = "200: Successful.";
                            echo json_encode(array($error_status));
                            break;
                    }
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
?>