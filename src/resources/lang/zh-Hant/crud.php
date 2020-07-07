<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backpack Crud Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used by the CRUD interface.
    | You are free to change them to anything
    | you want to customize your views to better match your application.
    |
    */

    // Forms
    'save_action_save_and_new'         => '儲存並新增',
    'save_action_save_and_edit'        => '儲存並繼續編輯',
    'save_action_save_and_back'        => '儲存並返回',
    'save_action_changed_notification' => '儲存後的預設行為已更改。',

    // Create form
    'add'                 => '新增',
    'back_to_all'         => '回到所有 ',
    'cancel'              => '取消',
    'add_a_new'           => '新增一個 ',

    // Edit form
    'edit'                 => '編輯',
    'save'                 => '儲存',

    // Translatable models
    'edit_translations' => '編輯譯文',
    'language'          => '語言',

    // CRUD table view
    'all'                       => '全部 ',
    'in_the_database'           => '資料庫裡',
    'list'                      => '清單',
    'actions'                   => '動作',
    'preview'                   => '預覽',
    'delete'                    => '剛除',
    'admin'                     => '管理員',
    'details_row'               => '這是詳情列。你可以在這裡作出編輯。',
    'details_row_loading_error' => '當載入詳情時遇到錯誤。請重試。',

    // Confirmation messages and bubbles
    'delete_confirm'                              => '你確定要刪除這項紀錄嗎？',
    'delete_confirmation_title'                   => '紀錄已被刪除',
    'delete_confirmation_message'                 => '這項紀錄已成功被刪除。',
    'delete_confirmation_not_title'               => '紀錄未被刪除',
    'delete_confirmation_not_message'             => '嘗試刪除時發生錯誤。這項紀錄或未被成功刪除。',
    'delete_confirmation_not_deleted_title'       => '紀錄未被刪除',
    'delete_confirmation_not_deleted_message'     => '沒有任何事情發生過。你的這項紀錄仍然在此。',

    'ajax_error_title' => '錯誤',
    'ajax_error_text'  => '載入頁面錯誤. 請重新整理頁面.',

    // DataTables translation
    'emptyTable'     => '資料庫中沒有相關紀錄',
    'info'           => '正在顯示 _TOTAL_ 個紀錄中的 _START_ 至 _END_ 項',
    'infoEmpty'      => '',
    'infoFiltered'   => '(自 _TOTAL_ 個紀錄中篩選出來的紀錄)',
    'infoPostFix'    => '',
    'thousands'      => ',',
    'lengthMenu'     => '每頁 _MENU_ 項紀錄',
    'loadingRecords' => '載入中...',
    'processing'     => '處理中...',
    'search'         => '搜尋',
    'zeroRecords'    => '找不到相關紀錄',
    'paginate'       => [
        'first'    => '第一頁',
        'last'     => '最後一頁',
        'next'     => '下一頁',
        'previous' => '上一頁',
    ],
    'aria' => [
        'sortAscending'  => ': 以由小至大的方式排列',
        'sortDescending' => ': 以由大至小的方式排列',
    ],
    'export' => [
        'copy'              => '複製',
        'excel'             => 'Excel',
        'csv'               => 'CSV',
        'pdf'               => 'PDF',
        'print'             => '列印',
        'column_visibility' => '顯示欄位',
    ],

    // global crud - errors
    'unauthorized_access' => '未經授權的存取 - 您沒有權限瀏覽此頁面。',
    'please_fix'          => '請修正以下的錯誤：',

    // global crud - success / error notification bubbles
    'insert_success' => '這項紀錄已成功被新增。',
    'update_success' => '這項紀錄已成功被更新。',

    // CRUD reorder view
    'reorder'                      => '重新排序',
    'reorder_text'                 => '請以拖放 (drag and drop) 的放式重新排序。',
    'reorder_success_title'        => '完成',
    'reorder_success_message'      => '你的排序已被儲存。',
    'reorder_error_title'          => '錯誤',
    'reorder_error_message'        => '你的排序尚未被儲存。',

    // CRUD yes/no
    'yes' => '確定',
    'no'  => '取消',

    // CRUD filters navbar view
    'filters'        => '篩選',
    'toggle_filters' => '切換篩選',
    'remove_filters' => '移除篩選',

    // Fields
    'browse_uploads'            => '檢視已上傳的檔案',
    'select_all'                => '選擇全部',
    'select_files'              => '選擇檔案(多個)',
    'select_file'               => '選擇檔案(單個)',
    'clear'                     => '清除',
    'page_link'                 => '頁面連結',
    'page_link_placeholder'     => 'http://example.com/your-desired-page',
    'internal_link'             => '內部連結',
    'internal_link_placeholder' => '內部連結短碼，例如: \'admin/page\' (no quotes) for \':url\'',
    'external_link'             => '外部連結',
    'choose_file'               => '選擇檔案',

    //Table field
    'table_cant_add'    => '不能再增加 :entity',
    'table_max_reached' => '已達到 :max 項紀錄的上限',

    // File manager
    'file_manager' => '檔案管理',
];
