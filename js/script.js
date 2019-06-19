function openModal() {
  $(".upload-modal").css('display', 'block');
  //$(".upload-modal").css('visibility', 'visible');
}

function closeModal() {
  $(".upload-modal").css('display', 'none');
  //$(".upload-modal").css('visibility', 'hidden');
}
function openAddCloudModal() {
  $(".add-cloud-modal").css('display', 'block');
}
function closeAddCloudModal() {
  $(".add-cloud-modal").css('display', 'none');
  $("#add_cloud_warning").css('display', 'none');
  $("#clouds_table .selected").removeClass('selected');
}
$("#clouds_table tr").click(function(){
  $(this).addClass('selected').siblings().removeClass('selected');
});
/*Добавленные облака*/
//$("#added_clouds_table tbody tr").click(function(){
//  $(this).addClass('selected').siblings().removeClass('selected');
//});
//$("#added_clouds_table tbody tr").contextmenu(function(){
//  $(this).addClass('selected').siblings().removeClass('selected');
//});
$("#added_clouds_table tbody").contextmenu(function (event) {
    
    // Avoid the real one
    event.preventDefault();
    
    // Show contextmenu
    $(".custom-menu_clouds").finish().toggle(100).
    
    // In the right position (the mouse)
    css({
        top: (event.pageY - 17) + "px",
        left: event.pageX + "px"
    });
});

// If the document is clicked somewhere
$(document).bind("mousedown", function (e) {
    
    // If the clicked element is not the menu
    if (!$(e.target).parents(".custom-menu_clouds").length > 0) {
        
        // Hide it
        $(".custom-menu_clouds").hide(100);
    }
});


// If the menu element is clicked
$(".custom-menu_clouds li").click(function(){
    var $selected_row = $("#added_clouds_table .selected p");
    if ($selected_row.length > 0)
    {
      var cloud_name = $selected_row.html();
      var cloud_index;
      switch (cloud_name) {
        case "Yandex disk" :
          cloud_index = 1;
          break;
        case "Dropbox" :
          cloud_index = 2;
          break;
        case "Box.com":
          cloud_index = 3;
          break;
      }
        switch($(this).attr("data-action")) {
          case "remove_cloud":
            DeleteCloud(cloud_index);
            break;
          //case "edit_cloud":
          //  openModal();
          //  break;
        }
        $(".custom-menu_clouds").hide(100);
    }
    // Hide it AFTER the action was triggered
  });
/***************************************/
function ShowFilesPerCloud()
{
  var added_clouds = $('#added_clouds_table tbody tr td p');
  added_clouds.each(function() {
  switch ($(this).text()) {
      case 'Yandex disk':
          GetFiles(1);
          break;
      case 'Dropbox':
          GetFiles(2);
          break;
      case 'Box.com':
          GetFiles(3);
          break;
    }
  });
}
function ShowAddedClouds(cloud_ids)
{
  var clouds = JSON.parse(cloud_ids);
  $('#added_clouds_table tbody').empty();
  clouds.forEach(function(entry) {
    var img_path, cloud_name;
    switch (entry.cloud_id) {
      case "1":
        img_path = "../media/yandexdisk-icon.png";
        cloud_name = "Yandex disk";
        break;
      case "2":
        img_path = "../media/dropbox-icon.png";
        cloud_name = "Dropbox";
        break;
      case "3":
        img_path = "../media/box-icon.png";
        cloud_name = "Box.com";
        break;
      }
    $("#added_clouds_table tbody").append(
      "<tr>" +
        "<td><img src=" + img_path + ">" +
        "<p>" + cloud_name + "</p>" +
        "</td>" +
      "</tr>");
  });
  //Выбор облака
  $("#added_clouds_table tbody tr").click(function(){
    $(this).addClass('selected').siblings().removeClass('selected');
    
    
    /////////////////////КОД ДЛЯ ОБНОВЛЕНИЯ ОБЛАКА ПРИ ВЫБОРЕ ОБЛАКА ИЗ added_clouds_table
    var $selected_row = $(this);
    var cloud_name = $selected_row.find("p").text();
      switch (cloud_name) {
        case "Yandex disk":
          GetCurrentFolder(1, null, null);
          //YandexListFolder(tmp.next().find('td:nth-child(2)').html());
          return false;
        case "Dropbox":
          GetCurrentFolder(2, null, null);
          //DropboxListFolder(tmp.next().find('td:nth-child(2)').html());
          break;
        case "Box.com":
          GetCurrentFolder(3, null, null);
          //BoxGetFileId(tmp.next().find('td:nth-child(2)').html());
          break;
        }
    });
  
  $("#added_clouds_table tbody tr").contextmenu(function(){
  $(this).addClass('selected').siblings().removeClass('selected');    
  });
}
function AuthorizeCloud() {
  var $selected_row = $("#clouds_table .selected");
  if ($selected_row.length > 0)
  {
      var cloud_index = $selected_row.index();
      var img_path, cloud_name;
      switch (cloud_index)
      {
          case 0:
            YandexDiskAuth();
            img_path = "../media/yandexdisk-icon.png";
            cloud_name = "Yandex disk";
            break;
          case 1:
            DropboxAuth();
            img_path = "../media/dropbox-icon.svg";
            cloud_name = "Dropbox";
            break;
          case 2:
            BoxAuth();
            img_path = "../media/box-icon.png";
            cloud_name = "Box.com";
            break;
      }
      closeAddCloudModal();
  }
  else
    $("#add_cloud_warning").css('display', 'inline');
}
function uploadStatus(num) {
  if (num == 1)
  {
    $("#upload-status").css('display', 'inline-block');
    $("#upload-status").css('color', 'green');
    $("#upload-status").text('Success!');
  }
  if (num == 0)
  {
    $("#upload-status").css('display', 'inline-block');
    $("#upload-status").css('color', 'red');
    $("#upload-status").text('Failure!');
  }
  if (num === -1)
  {
    $("#upload-status").css('display', 'none');
    $("#upload-status").text('');
  }
}
$(document).bind("mousedown", function(event) {
  if (!$(event.target).parents(".upload-modal-content").length > 0)
    closeModal();
  if (!$(event.target).parents(".add-cloud-modal-content").length > 0)
    closeAddCloudModal();
});
$('#btn_add').on('click', function() {
    $('#file_upload').trigger('click');
});
$('#btn_upload_yandex').on('click', function() {
    var fileInput = $('#file_upload');
    var file = fileInput[0].files[0];
    var form_data = new FormData();
    form_data.append("file", file);
    YandexDiskUploadFile(form_data);
});
$('#btn_upload_dropbox').on('click', function() {
    var fileInput = $('#file_upload');
    var file = fileInput[0].files[0];
    var form_data = new FormData();
    form_data.append("file", file);
    DropboxUploadFile(form_data);
});
$('#btn_upload_box').on('click', function() {
    var fileInput = $('#file_upload');
    var file = fileInput[0].files[0];
    var form_data = new FormData();
    form_data.append("file", file);
    BoxUploadFile(form_data);
});
$("#files_table tbody").contextmenu(function (event) {
    var headers = $("#files_table .cloud_header");
    var prev_buttons = $("#files_table .prev_folder");
    var is_header = false;
    var i = 0;
    headers.each(function()
    {
      var tmp = $(this);
      var btn = prev_buttons.eq(i);
        if (event.pageX >= tmp.offset().left && event.pageY>= tmp.offset().top &&
            event.pageX <= tmp.offset().left + tmp.width() && event.pageY<= tmp.offset().top + tmp.height() + btn.height())
        {
          console.log("yes");
          is_header = true;
          return false;
        }
      i++;
    });
    if (!is_header)
    {
        // Avoid the real one
        event.preventDefault();
        // Show contextmenu
        $(".custom-menu").finish().toggle(100).
        
        // In the right position (the mouse)
        css({
            top: (event.pageY - 17) + "px",
            left: event.pageX + "px"
        });
    }
});
// If the document is clicked somewhere
$(document).bind("mousedown", function (e) {
    
    // If the clicked element is not the menu
    if (!$(e.target).parents(".custom-menu").length > 0) {
        
        // Hide it
        $(".custom-menu").hide(100);
    }
});


// If the menu element is clicked
$(".custom-menu li").click(function(){
    
    // This is the triggered action name
    switch($(this).attr("data-action")) {
        
        // A case for each action. Your actions here
        case "download":
          //alert("download");
          DownloadFile($("#files_table .selected td:nth-child(2)").html(), $("#files_table .selected td:nth-child(4)").html());
          break;
        case "upload":
          openModal();
          break;
        case "delete":
          DeleteFile($("#files_table .selected td:nth-child(2)").html(), $("#files_table .selected td:nth-child(4)").html());
          break;
    }
  
    // Hide it AFTER the action was triggered
    $(".custom-menu").hide(100);
  });