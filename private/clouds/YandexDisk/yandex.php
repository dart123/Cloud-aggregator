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
case 'list_folder':
    yandex_list_folder($_GET['path'], get_token(1), true);
    break;
case 'download_file':
    yandex_download_file($_GET['filename']);
    break;
case 'upload_file':
    yandex_upload_file();
    break;
case 'delete_file':
    yandex_delete_file($_GET['filename'], $_GET['modified']);
    break;
case 'get_operation_status':
    yandex_get_operation_status($_GET['href']);
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
            if (!get_token(1))
                save_token($result->access_token, "/", 1);
            else
                if (get_token(1) != $result->access_token)
                    update_token($result->access_token, 1);
            yandex_list_folder("/", $result->access_token, false);
            $redirect_url = "../../../main_view.php";
            header('Location: ' . filter_var($redirect_url, FILTER_SANITIZE_URL));
        }
        exit;
}
function yandex_list_folder($path, $token, $ajax)
{
    if (isset($token)) {
        if ($ajax && $path != get_current_folder(1, false))
        {
            $path = get_current_folder(1, false).$path;
        }
        $header = Array("Authorization: OAuth ".$token);
        $request_uri = "https://cloud-api.yandex.net/v1/disk/resources?path=".urlencode($path)."&limit=50";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        $embedded = $result->_embedded;
        if ($embedded->items)
        {
            delete_file(null, null, 1);
            save_file_signatures($embedded->items, "yandex");
            if ($ajax)
            {
                update_token(null, 1, $path); //обновить текущую папку
                echo "true";
            }
            else
                return $embedded->items;
        }
        else
            if ($ajax)
                echo "false";
            else
                return false;
    }
}
function yandex_download_file($filename)
{
    global $current_folder;
    $token = get_token(1); //1й параметр - облако (1 - яндекс), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = yandex_list_folder(get_current_folder(1, false), $token, false);
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
function yandex_delete_file($filename, $modified)
{
    global $current_folder;
    $token = get_token(1); //1й параметр - облако (1 - яндекс), 2й параметр - пользователь
    if (isset($token)) {
            $folder_contents = yandex_list_folder(get_current_folder(1, false), $token, false);
            if ($folder_contents)
            {
                $file_found = false;
                foreach($folder_contents as $value):
                    if ($value->name == $filename)
                    {
                        $delete_path = $value->path;
                        $file_found = true;
                        break;
                    }
                endforeach;
                //Если файл найден, отправляем запрос на получение url для скачивания
                if ($file_found == true)
                {
                    $header = array("Authorization: OAuth ".$token);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'https://cloud-api.yandex.net/v1/disk/resources?path='.urlencode($delete_path));//."&permanently=true");
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                    $result = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    $result = json_decode($result);
                    // Check the HTTP Status code
                    switch ($httpCode) {
                        case 204:
                            delete_file($filename, $modified);
                            $error_status = "204: No content. File or empty folder, deleted.";
                            echo json_encode(array($error_status));
                            break;
                        case 202:
                            delete_file($filename, $modified);
                            $error_status = "202. Accepted. Non-empty folder delete started.";
                            echo json_encode(array($error_status, $result->href));
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
function yandex_get_operation_status($url)
{
    $token = get_token(1);
    if (isset($token)) {
        $header = Array("Authorization: OAuth ".$token);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);
        //$context = stream_context_create($opts);
        //$result = file_get_contents($request_uri, false, $context);
        $result = json_decode($result);
        echo $result->status;
    }
}
?>