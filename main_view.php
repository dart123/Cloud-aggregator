<?php
    require 'vendor/autoload.php';
    session_start();
    $result = null;
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
            <a href="index.php" class="btn btn-default" style="float:right; margin: 5px">Главная</a>
            <button class="btn_show_files" onclick="LogOut();">Выйти</button>
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
            <button class="btn_show_files" onclick="YandexDiskAuth();">Авторизоваться в Yandex Disk</button>
            <button class="btn_show_files" onclick="DropboxAuth();">Авторизоваться в Dropbox</button>
            <button class="btn_show_files" onclick="BoxAuth();">Авторизоваться в Box</button>
            
            <button class="btn_show_files" onclick="GetFiles(1);">Загрузить файлы из Yandex Disk</button>
            <button class="btn_show_files" onclick="GetFiles(2);">Загрузить файлы из Dropbox</button>
            <button class="btn_show_files" onclick="GetFiles(3);">Загрузить файлы из Box</button>
            <button class="btn_show_files" onclick="GetFiles();">Загрузить все файлы</button>
            
            <ul class='custom-menu'>
                <li data-action="download">Скачать файл</li>
                <li data-action="upload">Загрузить файл</li>
            </ul>
            
            <div class="upload-modal">
                <div class="upload-modal-content">
                   <button id="btn_add">Добавить</button>
                   <button id="btn_upload_yandex">Загрузить на яндекс</button>
                   <button id="btn_upload_dropbox">Загрузить на dropbox</button>
                   <button id="btn_upload_box">Загрузить на box.com</button>
                    <input id="file_upload" type="file" name="name"/>
                    <label id="upload-status"/>
                   <button class="btn close" onclick="closeModal();">Отмена</button>
                </div>
            </div>
        </div>
        
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
        <!--<script type="text/javascript" src="libs/bootstrap/js/bootstrap.bundle.min.js"></script>-->
    </body>
</html>