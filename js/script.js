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
$("#added_clouds_table tbody tr").click(function(){
  $(this).addClass('selected').siblings().removeClass('selected');
});
$("#added_clouds_table tbody tr").contextmenu(function(){
  $(this).addClass('selected').siblings().removeClass('selected');
});
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
    
    // This is the triggered action name
    switch($(this).attr("data-action")) {
        
        // A case for each action. Your actions here
        case "remove_cloud":
          //alert("download");
          DownloadFile($("#files_table .selected td:nth-child(2)").html(), $("#files_table .selected td:nth-child(4)").html());
          break;
        case "edit_cloud":
          openModal();
          break;
    }
  
    // Hide it AFTER the action was triggered
    $(".custom-menu").hide(100);
  });
/***************************************/
function AuthorizeCloud() {
  var $selected_row = $("#clouds_table .selected");
  if ($selected_row.length > 0)
  {
      var cloud_index = $selected_row.index();
      var auth_result;
      switch (cloud_index)
      {
          case 0:
            auth_result = YandexDiskAuth();
            break;
          case 1:
            auth_result = DropboxAuth();
            break;
          case 2:
            auth_result = BoxAuth();
            break;
      } 
      closeAddCloudModal();
  }
  else
    $("#add_cloud_warning").css('display', 'inline');
}
function uploadStatus(num) {
  //if (num == 1)
  //  $("#upload-status").css('display', 'inline-block');
  //  $("#upload-status").css('color', 'green');
  //  $("#upload-status").text('Success!');
  //if (num == 0)
  //  $("#upload-status").css('display', 'inline-block');
  //  $("#upload-status").css('color', 'red');
  //  $("#upload-status").text('Failure!');
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
    
    // Avoid the real one
    event.preventDefault();
    
    // Show contextmenu
    $(".custom-menu").finish().toggle(100).
    
    // In the right position (the mouse)
    css({
        top: (event.pageY - 17) + "px",
        left: event.pageX + "px"
    });
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