var Utils = {
  get_datatable : function(table_id, url, columns, disable_sort, callback, details_callback, sort_column=null, sort_order=null, created_row_callback, draw_callback){
    if($.fn.dataTable.isDataTable('#'+table_id)){
      details_callback = false;
      $('#'+table_id).DataTable().destroy();
    }
    var table = $('#'+table_id).DataTable({
      "language": {
          "processing": '<div class="loading-message loading-message-boxed"><img src="../assets/global/img/loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;LOADING...</span></div>',
          "aria": {
              "sortAscending": ": activate to sort column ascending",
              "sortDescending": ": activate to sort column descending"
          },
          "emptyTable": "No data available in table",
          "info": "Showing _START_ to _END_ of _TOTAL_ entries",
          "infoEmpty": "No entries found",
          "infoFiltered": "(filtered1 from _MAX_ total entries)",
          "lengthMenu": "Show _MENU_ records",
          "search": "Search:",
          "zeroRecords": "No matching records found"
      },
      /* "responsive": true, */
      "order": [sort_column == null ? 3 : sort_column, sort_order == null ? 'desc' : sort_order],
      "orderClasses": false,
      "pagingType": "bootstrap_full_number",
      "columns": columns,
      "buttons": [
          { extend: 'print' },
          { extend: 'copy', },
          { extend: 'pdf', },
          { extend: 'excel' },
          { extend: 'csv' }
      ],
      "columnDefs": [{"orderable": false, "targets": disable_sort}],
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": url,
        "type": "GET",
        "headers": {"sis-access-token": SISConfig.app_access_token, "sis-user-token": Utils.get_from_localstorage('user').jwt_token}
      },
      "lengthMenu": [ [5, 10, 15, 50, 100, 200, 500], [5, 10, 15, 50, 100, 200, 500] ],
      "pageLength": 10,
      "initComplete": function() {
        if(callback) callback();
      },
      "drawCallback": function(settings) {
        if(draw_callback) draw_callback();
      },
      "rowCallback": function(row, data) {
        if(created_row_callback) created_row_callback(row, data);
      }
    });
    /* Datatable buttons */
    $('#'+table_id + '_tools > li > a.tool-action').on('click', function() {
        var action = $(this).attr('data-action');
        table.DataTable().button(action).trigger();
    });

    if(details_callback){
      $('#' + table_id + ' tbody').on('click', 'td.details', function () {
        var tr = $(this).closest('tr');
        var row = $('#'+table_id).DataTable().row( tr );
        if ( row.child.isShown() ) {
          row.child.hide();
          tr.removeClass('shown');
        }else {
          row.child('<div id="details-container-' + row.data().id + '" style="text-align: center;"><i class="fa fa-spinner fa-spin fa-lg fa-fw"></i><span class="sr-only">Loading...</span></div>').show();
          details_callback(row.data().id);
          tr.addClass('shown');
        }
      });
    }
  },
  get_client_datatable(table,data,columns,disable_sort,default_sort_column_index,custom_columns_width,select_type,centered_columns,page_lenght, footerCallback){
    var table=$('#'+table).DataTable({
      "language": {
          /* "processing": "<img src='../web-client/assets/global/img/hourglass.gif' />", */
          "processing": '<i class="fa fa-spinner fa-spin fa-4x fa-fw"></i><span class="sr-only">Loading...</span> ',
          "aria": {
              "sortAscending": ": activate to sort column ascending",
              "sortDescending": ": activate to sort column descending"
          },
          "emptyTable": "No data available in table",
          "info": "Showing _START_ to _END_ of _TOTAL_ entries",
          "infoEmpty": "No entries found",
          "infoFiltered": "(filtered1 from _MAX_ total entries)",
          "lengthMenu": "Show _MENU_ records",
          "search": "Search:",
          "zeroRecords": "No matching records found"
      },
      "order": [default_sort_column_index, 'desc'],
      "pagingType": "bootstrap_full_number",
      "columns": columns,
      "columnDefs": [
        {"orderable": false, "targets": disable_sort},
        {"width": custom_columns_width.width, "targets": custom_columns_width.targets},
        {"className": "dt-center", "targets": centered_columns}
      ],
      "select": select_type,
      "processing": true,
      "lengthMenu": [ [5, 10, 15, 50, 100, 200, 500], [5, 10, 15, 50, 100, 200, 500] ],
      "pageLength": page_lenght,
      "data": data,
      "footerCallback": function(row, data, start, end, display) {
        var api = this.api(), data;
        if(footerCallback) footerCallback(row, data, start, end, display, api);
      }
    });
  },
  reload_datatable : function(table_id) {
    $('#'+table_id).DataTable().ajax.reload();
  },
  build_dropdown : function(data, selector, empty_option){
    var html = '';
    if(empty_option){
      html += '<option value="null">ALL</option>';
    }
    var selected = false;
    $.each(data, function(key,value){
      if (value.selected == 'selected'){
        selected = true;
        html += '<option value="'+value.id+'" selected="selected">'+value.name+'</option>';
      }else{
        html += '<option value="'+value.id+'">'+value.name+'</option>';
      }
    });
    $(selector).html(html);
    if (!selected){
      $(selector+" option:first").attr('selected','selected');
    }
  },
  manage_crop_modal : function(hide_selector,show_selector){
    $(hide_selector).modal('hide');
    $(show_selector).modal('show');
  },

  clear_cache: function(){
    $.each(window.localStorage, function(key, value){
      if(key != 'user'){
        window.localStorage.removeItem(key);
      }
    })
    toastr.success("Local storage has been cleared");
  },

  remove_from_localstorage : function (key){
    window.localStorage.removeItem(key);
  },
  get_from_localstorage : function(key){
    return JSON.parse(window.localStorage.getItem(key));
  },
  set_to_localstorage : function(key,value){
    window.localStorage.setItem(key,JSON.stringify(value));
  },

  get_query_param : function( name ){
    var regexS = "[\\?&]"+name+"=([^&#]*)",
    regex = new RegExp( regexS ),
    results = regex.exec( window.location.search );
    if( results == null ){
      return "";
    } else{
      return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
  },
  block_ui : function(target,message){
    var html='<div class="loading-message loading-message-boxed"><img src="../assets/global/img/loading-spinner-grey.gif" align=""><span>&nbsp;&nbsp;'+(message ? message : 'LOADING...')+'</span></div>';
    $(target).block({
      message: html,
      baseZ: 1000,
      css: {
        top: '10%',
        border: '0',
        padding: '0',
        backgroundColor: 'none'
      },
      overlayCSS: {
          backgroundColor: '#555',
          opacity: 0.1,
          cursor: 'wait'
      }
    });
  },
  unblock_ui : function(target){
    $(target).unblock({
        onUnblock: function() {
            $(target).css('position', '');
            $(target).css('zoom', '');
        }
    });
  },
  generate_email_slug(first_name, last_name){
    first_name = first_name.replace(/ /g, '.').toLowerCase();
    last_name = last_name.replace(/ /g, '.').toLowerCase();

    var special_characters = ['č', 'ć', 'ž', 'š', 'đ', 'ğ', 'ö', 'ü', 'ı', 'ç', 'ş', 'ä', 'ß'];
    var special_characters_replacements = ['c', 'c', 'z', 's', 'd', 'g', 'g', 'u', 'i', 'c', 's', 'a', 's'];

    for(var i = 0; i<special_characters.length; i++){
      first_name = first_name.split(special_characters[i]).join(special_characters_replacements[i]);
      last_name = last_name.split(special_characters[i]).join(special_characters_replacements[i]);
    }
    return first_name +'.'+last_name;
  },
  parse_json_to_list : function(json_array){
    list = [];
    $.each(json_array, function(idx, value){
      list.push(value.name);
    });
    return list;
  },
  change_checkbox_status : function(element,entity){
    var is_checked = $(element).prop('checked');
    $('.' + entity + '_checkbox').each(function(idx,element){
      $(element).prop('checked', is_checked);
    });
  },
  toggle_selected_datatable_rows : function(table_id,checkbox){
    var checked = $(checkbox).prop('checked');
    var table=$(table_id).DataTable();
    if(checked){
      table.rows().nodes().to$().addClass('selected');
    }
    else{
      table.rows().nodes().to$().removeClass('selected');
    }
    Utils.change_checkbox_status(checkbox,'student');
  },
  toggle_single_row_selectio_datatable : function(checkbox){
    var checked = $(checkbox).prop('checked');
    var row=$(checkbox).parent().parent().parent();
    console.log(row);
    if(checked){
      $(row).addClass('selected');
    }
    else{
      $(row).removeClass('selected');
    }
  },
  position_dropdown : function(element){
    var position = $(element).offset();
    var diff = $(window).outerHeight() - position.top - $(element).next().outerHeight() + $(document).scrollTop();
    if (diff < 0){
      $(element).next().css({top: position.top - $(document).scrollTop() + diff + 20, left: position.left - $(document).scrollLeft() - 10});
    }else{
      $(element).next().css({top: position.top - $(document).scrollTop() + 20, left: position.left - $(document).scrollLeft() - 10});
    }
  },

}
