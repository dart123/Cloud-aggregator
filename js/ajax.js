/*function getEvents(element) {
    var elemEvents = $._data(element, "events");
    var allDocEvnts = $._data(document, "events");
    for(var evntType in allDocEvnts) {
        if(allDocEvnts.hasOwnProperty(evntType)) {
            var evts = allDocEvnts[evntType];
            for(var i = 0; i < evts.length; i++) {
                if($(element).is(evts[i].selector)) {
                    if(elemEvents == null) {
                        elemEvents = {};
                    }
                    if(!elemEvents.hasOwnProperty(evntType)) {
                        elemEvents[evntType] = [];
                    }
                    elemEvents[evntType].push(evts[i]);
                }
            }
        }
    }
    return elemEvents;
}*/
function YandexDiskAuth() {
    $.ajax({
        type: "GET",
        url: "/cloud_aggregator/private/clouds/YandexDisk/yandex.php?f=yandex_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxAuth() {
    $.ajax({
        type: "GET",
        url: "private/clouds/Dropbox/dropbox.php?f=dropbox_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxAuth() {
    $.ajax({
        type: "GET",
        url: "private/clouds/Box/box.php?f=box_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function YandexListFolder(foldername)
{
    $.ajax({
        type: "GET",
        url: "private/clouds/YandexDisk/yandex.php?f=list_folder&path=" + foldername,
        cache: false,
        success: function(response) {
            GetFiles(1);
                //window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxListFolder(foldername)
{
    $.ajax({
        type: "GET",
        url: "private/clouds/Dropbox/dropbox.php?f=list_folder&path=" + foldername,
        cache: false,
        success: function(response) {
            GetFiles(2);
                //window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxListFolder(path)
{
    $.ajax({
        type: "GET",
        url: "private/clouds/Box/box.php?f=list_folder&path=" + path,
        cache: false,
        success: function(response) {
                GetFiles(3);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxGetFileId(name, isGetParent)
{
    var ajax_url;
    if (isGetParent)
    {
        ajax_url = "private/clouds/Box/box.php?f=get_parent_folder_id&path=" + name;
    }
    else
       ajax_url = "private/clouds/Box/box.php?f=get_file_id&name=" + name;
    $.ajax({
        type: "GET",
        url: ajax_url,
        cache: false,
        success: function(id) {
            BoxListFolder(id);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function GetCurrentFolder(cloud, modifier, data)
{
     $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=get_current_folder&cloud=" + cloud,
        cache: false,
        success: function(response) {
            var arg;
            if (modifier === "next")
            {
                data += "/";
            //yandex начальная папка
                if (response === "/")
                {
                    arg = response + data;
                }
                else
                //dropbox начальная папка
                    if (response === "")
                    {
                        arg = "/" + data;
                    }
                    else
                    {
                        arg = response + data;
                    }
            }
            if (modifier === "prev")
            {
                var split = response.split("/");
                //путь по типу /folder1/folder2/ - отсекаем folder2 перед последним слешем
                response = response.replace(split[split.length - 2], '');
                //убираем слеш
                arg = response.slice(0, -1);
            }
            if (!modifier)
            //dropbox
                if (cloud !== 2)
                    arg = response;
                else
                    arg = "";

            switch (cloud) {
                case 1:
                    YandexListFolder(arg);
                    break;
                case 2:
                    DropboxListFolder(arg);
                    break;
                case 3:
                    if (!modifier)
                        BoxListFolder(arg);
                    if (modifier === "next")
                        BoxGetFileId(data, false);
                    if (modifier === "prev")
                        BoxGetFileId(response, true);
                    break;
            }

        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DeleteCloud(cloud_id)
{
    DeleteToken(cloud_id);
    DeleteFilesPerCloud(cloud_id);
}
function DeleteFilesPerCloud(cloud_id)
{
     $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=delete_files&cloud_id=" + cloud_id,
        cache: false,
        success: function(response) {
                $("#added_clouds_table .selected").remove();
                GetFiles(cloud_id);
           
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DeleteToken(cloud_id) {
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=delete_token&cloud_id=" + cloud_id,
        cache: false,
        //success: function(response) {
        //    if (response)
        //        $("#added_clouds_table .selected").remove();
        //   
        //},
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function HandleTokenCloudsResponse(response)
{
    ShowAddedClouds(response);
    ShowFilesPerCloud();
}
function GetTokenClouds() {
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=get_token_clouds",
        cache: false,
        success: function(response) {
            if (response)
            {
                HandleTokenCloudsResponse(response);
            }

        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function ClearFilesPerCloud(rows, cloud)
{
    //var $current_cloud;
    var $prev_btn;
    /*rows.each(function() {
        if ($(this).find('td').attr("class") == 'cloud_header')
        {
            if ($(this).find('span').text() == text)
            {
                if ($(this).next().hasClass('prev_folder'))
                {
                    $prev_btn = $(this).next();
                }
                $current_cloud = $(this);
                var $rows_to_delete = $current_cloud.nextAll();
                if ($prev_btn)
                {
                    $rows_to_delete = $rows_to_delete.not($prev_btn);
                }
                $rows_to_delete.each(function() {
                    if ($(this).next().find('td').attr("class") == 'cloud_header')
                    {
                        $rows_to_delete = $rows_to_delete.not($(this).nextAll());
                        return false;
                    }
                });
                //$rows_to_delete.remove();
            }
        }
    });*/
    if (cloud) {
        $(".file_row.cloud" + cloud).remove();
        $prev_btn = $(".prev_folder.cloud" + cloud);
    }
    else if (window.interface_ready)
        $(".file_row").remove();
    return $prev_btn;
}
function ClearHeader(headers, cloud_name)
{
    headers.each(function() {
        if ($(this).text() === cloud_name)
        {
            $header = $(this).parent().parent();
            $prev_btn = $header.next();
            $header.remove();
            $prev_btn.remove();
        }
    });
}
function dbClickHandler($folder, prev = false)
{
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=get_is_folder&name=" + $folder.find('td:nth-child(2)').text() +
             "&modified=" + $folder.find('td:nth-child(4)').text(),
        cache: false,
        success: function(response) {

            if (response == 1 || prev)
            {
                if ($folder.hasClass('selected') || prev)
                {
                    var rows = $($('#files_table tbody tr').get().reverse());
                    rows = rows.not($folder.nextAll());
                    //Находим в каком облаке находится папка по заголовку в таблице
                    var breakLoop = false;
                                    rows.each(function() {
                                        var tmp = $(this);
                                        if (tmp.find('td').hasClass('cloud_header'))
                                        {
                                            var cloud_name = tmp.find('span').text();
                                            switch (cloud_name) {
                                                case "Yandex disk":
                                                    GetCurrentFolder(1, !prev ? "next" : "prev", $folder.find('td:nth-child(2)').text());
                                                    breakLoop = true;
                                                    break;
                                                case "Dropbox":
                                                    GetCurrentFolder(2, !prev ? "next" : "prev", $folder.find('td:nth-child(2)').text());
                                                    breakLoop = true;
                                                    break;
                                                case "Box.com":
                                                    GetCurrentFolder(3, !prev ? "next" : "prev", $folder.find('td:nth-child(2)').text());
                                                    breakLoop = true;
                                                    break;
                                            };
                                        }
                                        if (breakLoop) return false;
                                    });
                }
            }
        },
        error: function(data) {
            alert("ERROR:" + JSON.stringify(data));
        },
    });
}
function IsInterfaceReady() {
    var added_clouds = $('#added_clouds_table tbody tr td p');
    var cloud_counter = 0;
    added_clouds.each(function() {
        switch ($(this).text()) {
            case 'Yandex disk':
                if ($(".cloud_header.cloud1").length == 1 && $(".prev_folder.cloud1").length == 1 && $(".file_row.cloud1").length >= 1)
                {
                    cloud_counter++
                }
                break;
            case 'Dropbox':
                if ($(".cloud_header.cloud2").length == 1 && $(".prev_folder.cloud2").length == 1 && $(".file_row.cloud2").length >= 1)
                {
                    cloud_counter++
                }
                break;
            case 'Box.com':
                if ($(".cloud_header.cloud3").length == 1 && $(".prev_folder.cloud3").length == 1 && $(".file_row.cloud3").length >= 1)
                {
                    cloud_counter++
                }
                break;
        }
    });
    if (cloud_counter == added_clouds.length) {
        window.interface_ready = true;
    }
    else {
        window.interface_ready = false;
    }
}
function GetFiles(cloud=0) {
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=getfiles&cloud=" + cloud,
        cache: false,
        success: function(response) {
            var shown_rows = $('#files_table tbody tr');
            var cloud_headers = $('#files_table tbody .cloud_header span');
            var text;
                switch (cloud) {
                    case 1:
                        text = "Yandex disk";
                        break;
                    case 2:
                        text = "Dropbox";
                        break;
                    case 3:
                        text = "Box.com";
                        break;
                }
            if (response)
            {
                var files = JSON.parse(response);
                //$('#files_table tbody').empty();
                var header_exists = false;//, file_exists = false;
                var added_clouds = $('#added_clouds_table tbody tr td p');
                var count=0;
                //Проверяем, есть ли данное название облака в списке добавленных облаков
                added_clouds.each(function() {
                    if ($(this).text() === text)
                    {
                        count++;
                        return false;
                    }
                });
                //Если нет в списке добавленных облаков
                if (!count)
                    header_exists = true;
                if (!header_exists)
                {
                    cloud_headers.each(function() {
                        if ($(this).text() == text)
                        {
                            header_exists = true;
                            return false;
                        }
                    });
                }
                if (!header_exists)
                {
                    $("#files_table tbody").append(
                        "<tr>" +
                            "<td class='cloud_header cloud" + cloud + "' colspan='4'><span>" + text + "</span></td>" +
                        "</tr>" +
                         "<tr class='prev_folder cloud" + cloud + "'>" +
                            "<td colspan='4'><span>Назад</span></td>" +
                        "</tr>");
                }
                
                var $prev_btn = ClearFilesPerCloud(shown_rows, cloud!==0? cloud: null);
                files.forEach(function(entry) {
                        var img_path;
                        if (entry.isFolder == "1")
                        {
                            img_path = "media/Folder_icon.svg";
                        }
                        else
                            img_path = "media/file_icon.svg";
                        if ($prev_btn) {
                            var cloud_class = $prev_btn.prev().children(".cloud_header").attr('class').split(' ')[1];
                            $prev_btn.after(
                                "<tr class='file_row " + cloud_class + "'>" +
                                "<td class='img_col'><img src='" + img_path + "'></td>" +
                                "<td class='filename_col'>" + entry.filename + "</td>" +
                                "<td class='size_col'>" + entry.filesize / 1024 + "KB</td>" +
                                "<td class='modified_col'>" + entry.lastupdate + "</td>" +
                                "</tr>");
                        }
                        else {
                            var num = $('.cloud_header').length - 1;
                            var cloud_class = $('.cloud_header')[num].className.split(' ')[1];
                            $("#files_table tbody").append(
                                "<tr class='file_row " + cloud_class + "'>" +
                                "<td class='img_col'><img src='" + img_path + "'></td>" +
                                "<td class='filename_col'>" + entry.filename + "</td>" +
                                "<td class='size_col'>" + entry.filesize / 1024 + "KB</td>" +
                                "<td class='modified_col'>" + entry.lastupdate + "</td>" +
                                "</tr>");
                        }
                    //}
                });
                //if (typeof window.interface_ready !== 'undefined' && window.interface_ready === false)
                if (cloud !== 0) {
                    var cloud_selection = ".cloud" + cloud;
                }
                else {
                    var cloud_selection = "";
                }
                    $("#files_table tbody .file_row" + cloud_selection).click(function () {
                        $(this).addClass('selected').siblings().removeClass('selected');
                    });
                    $("#files_table tbody .file_row" + cloud_selection).contextmenu(function () {
                        $(this).addClass('selected').siblings().removeClass('selected');
                    });
                    $("#files_table tbody .file_row" + cloud_selection).dblclick(function () {
                        var $folder = $(this);
                        dbClickHandler($folder, false);
                    });
                    if (typeof window.interface_ready !== 'undefined' && window.interface_ready === false) {
                        $("#files_table tbody .prev_folder" + cloud_selection).dblclick(function () {
                            var $btn = $(this);
                            dbClickHandler($btn, true);
                        });
                    }
                    IsInterfaceReady();
                //}
            }
            else
            {
                ClearFilesPerCloud(shown_rows, text, cloud!==0? cloud: null);
                ClearHeader(cloud_headers, text);
            }

        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DeleteFile(filename, modified)
{
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
        cache: false,
        success: function(response) {
            switch (response) {
                case '1':
                   YandexDiskDeleteFile(filename, modified);
                   break;
                case '2':
                    DropboxDeleteFile(filename, modified);
                    break;
                case '3':
                    BoxDeleteFile(filename, modified);
                    break;
            }
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function YandexDiskDeleteFile(filename, modified) {
    $.ajax({
        type: "GET",
        url: "private/clouds/YandexDisk/yandex.php?f=delete_file&filename=" + filename + "&modified=" + modified,
        cache: false,
        success: function(response) {
            $("#files_table .selected").remove();
            response = JSON.parse(response);
            if (response.length == 2)
            {
                alert(response[0]);
                YandexGetOperationStatus(response[1]);
            }
            if (response.length == 1)
                alert(response[0]);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function YandexGetOperationStatus(href)
{
    $.ajax({
        type: "GET",
        url: "private/clouds/YandexDisk/yandex.php?f=get_operation_status&href=" + href,
        cache: false,
        success: function(response) {
           alert(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxDeleteFile(filename, modified) {
    $.ajax({
        type: "GET",
        url: "private/clouds/Dropbox/dropbox.php?f=delete_file&filename=" + filename + "&modified=" + modified,
        cache: false,
        success: function(response) {
            $("#files_table .selected").remove();
            response = JSON.parse(response);
            if (response.length == 1)
                alert(response[0]);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxDeleteFile(filename, modified) {
    $.ajax({
        type: "GET",
        url: "private/clouds/Box/box.php?f=delete_file&filename=" + filename + "&modified=" + modified,
        cache: false,
        success: function(response) {
            $("#files_table .selected").remove();
            response = JSON.parse(response);
            if (response.length == 1)
                alert(response[0]);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DownloadFile(filename, modified)
{
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
        cache: false,
        success: function(response) {
            switch (response) {
                case '1':
                   YandexDiskDownloadFile(filename);
                   break;
                case '2':
                    DropboxDownloadFile(filename);
                    break;
                case '3':
                    BoxDownloadFile(filename);
                    break;
            }
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function YandexDiskDownloadFile(filename) {
    $.ajax({
        type: "GET",
        url: "private/clouds/YandexDisk/yandex.php?f=download_file&filename=" + filename,
        cache: false,
        success: function(response) {
            if (response)
            {
                window.location.replace(response);
            }
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxDownloadFile(filename) {
    window.location.replace("private/clouds/Dropbox/dropbox.php?f=download_file&filename=" + filename);
}
function BoxDownloadFile(filename) {
    window.location.replace("private/clouds/Box/box.php?f=box_download&filename=" + filename);
}
function UploadFile(file)
{
    //$.ajax({
    //    type: "GET",
    //    url: "private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
    //    cache: false,
    //    success: function(response) {
    //        switch (response) {
    //            case '1':
    //               YandexDiskUploadFile(filename);
    //               break;
    //            case '2':
    //                DropboxUploadFile(filename);
    //                break;
    //            case '3':
    //                BoxUploadFile(filename);
    //                break;
    //        }
    //    },
    //     error: function(data) {
    //            alert("ERROR:" + JSON.stringify(data));
    //        },
    //});
}
function YandexDiskUploadFile(form_data) {
    uploadStatus(-1);
    $.ajax({
        type: "POST",
        url: "private/clouds/YandexDisk/yandex.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response === 'success')
            {
                uploadStatus(1);
            }
            else
                uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxUploadFile(form_data) {
    uploadStatus(-1);
    $.ajax({
        type: "POST",
        url: "private/clouds/Dropbox/dropbox.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response === 'success')
            {
                uploadStatus(1);
            }
            else
                uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxUploadFile(form_data) {
    uploadStatus(-1);
    $.ajax({
        type: "POST",
        url: "private/clouds/Box/box.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.trim() === 'success')
            {
                uploadStatus(1);
            }
            else
                uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function LogOut() {
    $.ajax({
        type: "GET",
        url: "private/DBManager.php?f=logout",
        cache: false,
        success: function(response)
        {
            window.location.replace(response);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}