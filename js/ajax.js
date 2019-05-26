function YandexDiskAuth() {
    $.ajax({
        type: "GET",
        url: "../private/clouds/YandexDisk/yandex.php?f=yandex_auth",
        cache: false,
        success: function(response) {
                window.location.replace(response);
                return true;
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
                return false;
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
                return true;
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
                return false;
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
                return true;
        },
         error: function(data) {
                alert("ERROR:" + JSON.stringify(data));
                return false;
            },
    });
}
function GetFiles(cloud=0) {
    $.ajax({
        type: "GET",
        url: "../private/DBManager.php?f=getfiles&cloud=" + cloud,
        cache: false,
        success: function(response) {
            if (response)
            {
                var files = JSON.parse(response);
                $('#files_table tbody').empty();
                files.forEach(function(entry) {
                $("#files_table tbody").append(
                "<tr>" +
                    "<td class='img_col'><img src='../media/Folder_icon.svg'></td>" +
                    "<td>" + entry.filename + "</td>" +
                    "<td>" + entry.filesize/1024 + "KB</td>" +
                    "<td>" + (entry.lastupdate ? entry.lastupdate : "")+ "</td>" +
                "</tr>");});
                //Выбор файла
                $("#files_table tbody tr").click(function(){
                    $(this).addClass('selected').siblings().removeClass('selected');    
                });
                $("#files_table tbody tr").contextmenu(function(){
                    $(this).addClass('selected').siblings().removeClass('selected');    
                });
            }
            else alert("REPONSE FALSE");

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