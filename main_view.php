<?php
    require './vendor/autoload.php';
    require_once './private/DBManager.php';
    session_start();
    if (isset($_SESSION['session_id']) && $_SESSION['session_id']!=-1)
        DBManager::get_token(0, true);
        $script = "<script type=\"text/javascript\">
            GetTokenClouds();
        </script>";
    //$result = null;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE9">
        <!--<link rel="stylesheet" type="text/css" href="libs/bootstrap/css/bootstrap.min.css">-->
        <link rel="stylesheet" type="text/css" href="css/main_view.css">
        <title>Просмотр файлов</title>
    </head>
    <body>
        <div class="container">
            <div class="empty"></div>
            <div class="right-menu">
                <ul class="nav">
                    <li><a id="logout_btn" onclick="LogOut();">Выйти</a></li>
                    <li> <?php if (isset($_SESSION['session_id']) && $_SESSION['session_id']!=-1) ?>
                    <p id="username"><?php echo DBManager::get_current_username(DBManager::get_current_user1()) ?></p></li>
                </ul>
            </div>
            
            <div class="side-bar">
                 <table id="added_clouds_table">
                    <tbody>
                    </tbody>
                 </table>
                <button class="btn_show_files" id="btn_add_cloud" onclick="openAddCloudModal();">Добавить облако</button>
            </div>
            
            <div class="main-box">
                <table id="files_table">
                    <thead>
                      <tr>
                        <th class="img_col"></th>
                        <th class="name_col">Name</th>
                        <th class="size_col">Size</th>
                        <th class="modif_col">Last modified</th>
                      </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <!--<button class="btn_show_files" onclick="YandexDiskAuth();">Авторизоваться в Yandex Disk</button>
                <button class="btn_show_files" onclick="DropboxAuth();">Авторизоваться в Dropbox</button>
                <button class="btn_show_files" onclick="BoxAuth();">Авторизоваться в Box</button>-->
                
                <!--<button class="btn_show_files" onclick="GetFiles(1);">Загрузить файлы из Yandex Disk</button>
                <button class="btn_show_files" onclick="GetFiles(2);">Загрузить файлы из Dropbox</button>
                <button class="btn_show_files" onclick="GetFiles(3);">Загрузить файлы из Box</button>
                <button class="btn_show_files" onclick="ShowFilesPerCloud();">Загрузить все файлы</button>-->
                
                <div class="upload-modal">
                    <div class="upload-modal-content">
                       <button id="btn_add" class="btn_modal">Добавить</button>
                       <button id="btn_upload_yandex" class="btn_modal">Загрузить на яндекс</button>
                       <button id="btn_upload_dropbox" class="btn_modal">Загрузить на dropbox</button>
                       <button id="btn_upload_box" class="btn_modal">Загрузить на box.com</button>
                       <button class="btn_modal btn_close" onclick="closeModal();">Отмена</button>
                        <input id="file_upload" type="file" name="name"/>
                        <label id="upload-status"/>
                    </div>
                </div>
                
                <div class="add-cloud-modal">
                    <div class="add-cloud-modal-content">
                        <table id="clouds_table">
                            <tr>
                                <td>
                                    <img src="media/yandexdisk-icon.png">
                                    <p>Yandex disk</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="media/dropbox-icon.png">
                                    <p>Dropbox</p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="media/box-icon.png">
                                    <p>Box.com</p>
                                </td>
                            </tr>
                        </table>
                       <button id="btn_add" class="btn_modal" onclick="AuthorizeCloud();">Добавить</button>
                       <button class="btn_modal btn_close" onclick="closeAddCloudModal();">Отмена</button>
                       <span id="add_cloud_warning">Выберите облачное хранилище!</span>
                    </div>
                </div>
            </div>
            <div class="footer">
                <p>Copyright</p>
            </div>
        </div>
            
            <ul class='custom-menu'>
                <li data-action="download">Скачать файл</li>
                <li data-action="upload">Загрузить файл</li>
                <li data-action="delete">Удалить файл</li>
            </ul>
        
            <ul class='custom-menu_clouds'>
                <li data-action="remove_cloud">Удалить облако</li>
                <!--<li data-action="edit_cloud">Изменить облако</li>-->
            </ul>
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"
			  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
			  crossorigin="anonymous">
        </script>
        <script src="js/ajax.js"></script>
        <script src="js/script.js"></script>
        <script>
            //var source = new EventSource("private/clouds/YandexDisk/yandexauth.php");
            //source.onmessage = function(event) {
            ////document.getElementById("result").innerHTML += event.data + "<br>";
            //alert(event.data);
            //};
        </script>
        <?php
            global $script;
            if (isset($script))
                echo $script;
        ?>
                  
        <!--<script type="text/javascript" src="libs/bootstrap/js/bootstrap.bundle.min.js"></script>-->
    </body>
</html>