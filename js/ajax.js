function YandexDiskAuth() {
    $.ajax({
        type: "GET",
        url: "../private/clouds/YandexDisk/yandex.php?f=yandex_auth",
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
        url: "../private/clouds/Dropbox/dropbox.php?f=dropbox_auth",
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
        url: "../private/clouds/Box/box.php?f=box_auth",
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
    console.log("folder name:");
    console.log(foldername);
    $.ajax({
        type: "GET",
        url: "../private/clouds/YandexDisk/yandex.php?f=list_folder&path=" + foldername,
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
function DropboxListFolder(path)
{
    $.ajax({
        type: "GET",
        url: "../private/clouds/Dropbox/dropbox.php?f=dropbox_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
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
        url: "../private/clouds/Box/box.php?f=box_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
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
        url: "../private/DBManager.php?f=delete_files&cloud_id=" + cloud_id,
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
        url: "../private/DBManager.php?f=delete_token&cloud_id=" + cloud_id,
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
        url: "../private/DBManager.php?f=get_token_clouds",
        cache: false,
        success: function(response) {
            if (response)
            {
                HandleTokenCloudsResponse(response);
            }
            else alert("REPONSE FALSE");

        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function ClearFilesPerCloud(rows, text)
{
    var $current_cloud;
    rows.each(function() {
        if ($(this).find('td').attr("class") == 'cloud_header')
        {
            if ($(this).find('span').text() == text)
            {
                $current_cloud = $(this);
                var $rows_to_delete = $current_cloud.nextAll();
                //console.log($rows_to_delete);
                $rows_to_delete.each(function() {
                    if ($(this).next().find('td').attr("class") == 'cloud_header')
                    {
                        $rows_to_delete = $rows_to_delete.not($(this).nextAll());
                        return false;
                    }
                });
                $rows_to_delete.remove();
            }
        }
    });
    return $current_cloud;
}
function ClearHeader(headers, cloud_name)
{
    headers.each(function() {
        if ($(this).text() === cloud_name)
        {
            $(this).parent().parent().remove();
        }
    });
}
function GetFiles(cloud=0) {
    $.ajax({
        type: "GET",
        url: "../private/DBManager.php?f=getfiles&cloud=" + cloud,
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
                            "<td class='cloud_header' colspan='4'><span>" + text + "</span></td>" +
                        "</tr>");
                }
                var $current_cloud = ClearFilesPerCloud(shown_rows, text);
                files.forEach(function(entry) {
                    
                        if ($current_cloud)
                            $current_cloud.after(
                            "<tr class='file_row'>" +
                                "<td class='img_col'><img src='../media/Folder_icon.svg'></td>" +
                                "<td class='filename_col'>" + entry.filename + "</td>" +
                                "<td class='size_col'>" + entry.filesize/1024 + "KB</td>" +
                                "<td class='modified_col'>" + entry.lastupdate + "</td>" +
                            "</tr>");
                        else
                            $("#files_table tbody").append(
                                "<tr class='file_row'>" +
                                "<td class='img_col'><img src='../media/Folder_icon.svg'></td>" +
                                "<td class='filename_col'>" + entry.filename + "</td>" +
                                "<td class='size_col'>" + entry.filesize/1024 + "KB</td>" +
                                "<td class='modified_col'>" + entry.lastupdate + "</td>" +
                            "</tr>");
                    //}
                });
                //Выбор файла
                $("#files_table tbody .file_row").click(function(){
                    $(this).addClass('selected').siblings().removeClass('selected');    
                });
                $("#files_table tbody .file_row").contextmenu(function(){
                    $(this).addClass('selected').siblings().removeClass('selected');    
                });
                $("#files_table tbody .file_row").dblclick(function() {
                    var $folder = $(this);
                    $.ajax({
                        type: "GET",
                        url: "../private/DBManager.php?f=get_is_folder&name=" + $folder.find('td:nth-child(2)').text() +
                                "&modified=" + $folder.find('td:nth-child(4)').text(),
                        cache: false,
                        success: function(response) {
                            if (response == 1)
                            {
                                if ($folder.hasClass('selected'))
                                {
                                    var rows = $($('#files_table tbody tr').get().reverse());
                                    rows = rows.not($folder.nextAll());
                                    //Находим в каком облаке находится папка по заголовку в таблице
                                    rows.each(function() {
                                        var tmp = $(this);
                                        if (tmp.find('td').hasClass('cloud_header'))
                                        {
                                            var cloud_name = tmp.find('span').text();
                                            switch (cloud_name) {
                                                case "Yandex disk":
                                                    YandexListFolder(tmp.next().find('td:nth-child(2)').html());
                                                    return false;
                                                case "Dropbox":
                                                    break;
                                                case "Box.com":
                                                    break;
                                            }
                                        }
                                    });
                                }
                            }
                        },
                         error: function(data) {
                                alert("ERROR:" + JSON.stringify(data));
                            },
                    });
                });
            }
            else
            {
                ClearFilesPerCloud(shown_rows, text);
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
        url: "../private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
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
        url: "../private/clouds/YandexDisk/yandex.php?f=delete_file&filename=" + filename + "&modified=" + modified,
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
        url: "../private/clouds/YandexDisk/yandex.php?f=get_operation_status&href=" + href,
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
        url: "../private/clouds/Dropbox/dropbox.php?f=delete_file&filename=" + filename + "&modified=" + modified,
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
        url: "../private/clouds/Box/box.php?f=delete_file&filename=" + filename + "&modified=" + modified,
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
        url: "../private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
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
        url: "../private/clouds/YandexDisk/yandex.php?f=download_file&filename=" + filename,
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
    window.location.replace("../private/clouds/Dropbox/dropbox.php?f=download_file&filename=" + filename);
}
function BoxDownloadFile(filename) {
    window.location.replace("../private/clouds/Box/box.php?f=box_download&filename=" + filename);
}
function UploadFile(file)
{
    //$.ajax({
    //    type: "GET",
    //    url: "../private/DBManager.php?f=get_file_cloud&filename=" + filename + "&modified=" + modified,
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
    $.ajax({
        type: "POST",
        url: "../private/clouds/YandexDisk/yandex.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            //if (strcmp(response, 'success') == 0)
            //{
            //    uploadStatus(1);
            //}
            //else
            //    uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function DropboxUploadFile(form_data) {
    $.ajax({
        type: "POST",
        url: "../private/clouds/Dropbox/dropbox.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            //if (strcmp(response, 'success') == 0)
            //{
            //    uploadStatus(1);
            //}
            //else
            //    uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function BoxUploadFile(form_data) {
    $.ajax({
        type: "POST",
        url: "../private/clouds/Box/box.php?f=upload_file",
        data: form_data,
        cache: false,
        processData: false,
        contentType: false,
        success: function(response) {
            //if (strcmp(response, 'success') == 0)
            //{
            //    uploadStatus(1);
            //}
            //else
            //    uploadStatus(0);
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
            },
    });
}
function LogOut() {
    $.ajax({
        type: "GET",
        url: "../private/DBManager.php?f=logout",
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