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
    'save_action_save_and_new' => 'Salvează și adaugă o nouă intrare',
    'save_action_save_and_edit' => 'Salvează și editează intrarea',
    'save_action_save_and_back' => 'Salvează și mergi la listă',
    'save_action_save_and_preview' => 'Salvează și previzualizează',
    'save_action_changed_notification' => 'A fost salvată preferința redirecționării după salvare.',

    // Create form
    'add' => 'Adaugă',
    'back_to_all' => 'Înapoi la ',
    'cancel' => 'Anulează',
    'add_a_new' => 'Adaugă ',

    // Edit form
    'edit' => 'Editează',
    'save' => 'Salvează',

    // Translatable models
    'edit_translations' => 'EDITEAZĂ TRADUCERILE',
    'language' => 'Limbă',

    // CRUD table view
    'all' => 'Toate ',
    'in_the_database' => 'din baza de date',
    'list' => 'Listă',
    'reset' => 'Resetează ',
    'actions' => 'Operațiuni',
    'preview' => 'Previzualizare',
    'delete' => 'Șterge',
    'admin' => 'Administrator',
    'details_row' => 'Acesta este rândul detalii. Modifică cum dorești',
    'details_row_loading_error' => 'A apărut o eroare la încărcarea detaliilor. Te rog să reîncerci.',
    'clone' => 'Clonează',
    'clone_success' => '<strong>Intrarea a fost clonată</strong><br>O nouă intrare a fost adăugată, cu aceleași informații ca aceasta.',
    'clone_failure' => '<strong>Clonarea a eșuat</strong><br>Noua intrare nu a putut fi creată. Te rugăm să încerci din nou.',

    // Confirmation messages and bubbles
    'delete_confirm' => 'Ești sigur că vrei să ștergi această intrare?',
    'delete_confirmation_title' => 'Intrare ștearsă',
    'delete_confirmation_message' => 'Intrarea a fost ștearsă cu succes.',
    'delete_confirmation_not_title' => 'Eroare',
    'delete_confirmation_not_message' => 'A avut loc o eroare. E posibil ca intrarea să nu fi fost ștearsă.',
    'delete_confirmation_not_deleted_title' => 'Intrarea nu a fost ștearsă',
    'delete_confirmation_not_deleted_message' => 'Nu am șters intrarea din baza de date.',

    // Bulk actions
    'bulk_no_entries_selected_title' => 'Nu au fost selectate intrări',
    'bulk_no_entries_selected_message' => 'Vă rugăm să selectați una sau mai multe elemente pentru a efectua o acțiune în lot.',

    // Bulk delete
    'bulk_delete_are_you_sure' => 'Ești sigur că vrei să ștergi aceste :number intrări?',
    'bulk_delete_sucess_title' => 'Intrări șterse',
    'bulk_delete_sucess_message' => ' elemente au fost șterse',
    'bulk_delete_error_title' => 'Ștergerea a eșuat',
    'bulk_delete_error_message' => 'Unul sau mai multe elemente nu au putut fi șterse',

    // Bulk clone
    'bulk_clone_are_you_sure' => 'Ești sigur că vrei să clonezi aceste :number intrări?',
    'bulk_clone_sucess_title' => 'Intrări clonate',
    'bulk_clone_sucess_message' => ' elemente au fost clonate.',
    'bulk_clone_error_title' => 'Clonarea a eșuat',
    'bulk_clone_error_message' => 'Una sau mai multe intrări nu au putut fi create. Te rugăm să încerci din nou.',

    // Ajax errors
    'ajax_error_title' => 'Eroare',
    'ajax_error_text' => 'Eroare la încărcarea paginii. Te rog să reîncarci pagina.',

    // DataTables translation
    'emptyTable' => 'Nu există intrări în baza de date',
    'info' => 'Sunt afișate intrările _START_-_END_ din _TOTAL_',
    'infoEmpty' => '',
    'infoFiltered' => '(filtrate din totalul de _MAX_ )',
    'infoPostFix' => '.',
    'thousands' => ',',
    'lengthMenu' => '_MENU_ pe pagină',
    'loadingRecords' => 'Se încarcă...',
    'processing' => 'Se procesează...',
    'search' => 'Caută',
    'zeroRecords' => 'Nu au fost găsite intrări care să se potrivească',
    'paginate' => [
        'first' => 'Prima pagină',
        'last' => 'Ultima pagină',
        'next' => 'Pagina următoare',
        'previous' => 'Pagina anterioară',
    ],
    'aria' => [
        'sortAscending' => ': activează pentru a ordona ascendent coloana',
        'sortDescending' => ': activează pentru a ordona descendent coloana',
    ],
    'export' => [
        'export' => 'Export',
        'copy' => 'Copiere',
        'excel' => 'Fișier Excel',
        'csv' => 'Fișier CSV',
        'pdf' => 'PDF',
        'print' => 'Imprimă',
        'column_visibility' => 'Vizibilitate coloane',
    ],
    'custom_views' => [
        'title' => 'vizualizări personalizate',
        'title_short' => 'vizualizări',
        'default' => 'implicit',
    ],

    // global crud - errors
    'unauthorized_access' => 'Acces neautorizat - Nu ai permisiunea necesară pentru a accesa pagina.',
    'please_fix' => 'Vă rugăm să reparați următoarele erori:',

    // global crud - success / error notification bubbles
    'insert_success' => 'Intrarea a fost adăugată cu succes.',
    'update_success' => 'Intrarea a fost modificată cu succes.',

    // CRUD reorder view
    'reorder' => 'Reordonare',
    'reorder_text' => 'Folosește drag&drop pentru a reordona.',
    'reorder_success_title' => 'Terminat',
    'reorder_success_message' => 'Ordinea a fost salvată.',
    'reorder_error_title' => 'Eroare',
    'reorder_error_message' => 'Ordinea nu a fost salvată.',

    // CRUD yes/no
    'yes' => 'Da',
    'no' => 'Nu',

    // CRUD filters navbar view
    'filters' => 'Filtre',
    'toggle_filters' => 'Comutare filtre',
    'remove_filters' => 'Anulează filtre',
    'apply' => 'Aplică',

    //filters language strings
    'today' => 'Astăzi',
    'yesterday' => 'Ieri',
    'last_7_days' => 'Ultimele 7 zile',
    'last_30_days' => 'Ultimele 30 de zile',
    'this_month' => 'Luna aceasta',
    'last_month' => 'Luna trecută',
    'custom_range' => 'Interval personalizat',
    'weekLabel' => 'S',

    // Fields
    'browse_uploads' => 'Alege din fișierele urcate',
    'select_all' => 'Selectează tot',
    'unselect_all' => 'Deselectează tot',
    'select_files' => 'Selectează fișiere',
    'select_file' => 'Selectează fișier',
    'clear' => 'Curăță',
    'page_link' => 'Link către pagină',
    'page_link_placeholder' => 'http://example.com/pagina-dorita-de-tine',
    'internal_link' => 'Link intern',
    'internal_link_placeholder' => 'Rută internă. De ex: \'admin/page\' (fără ghilimele) pentru \':url\'',
    'external_link' => 'Link extern',
    'choose_file' => 'Alege fișier',
    'new_item' => 'Element nou',
    'select_entry' => 'Selectează o intrare',
    'select_entries' => 'Selectează intrări',
    'upload_multiple_files_selected' => 'Fișiere selectate. După salvare, vor apărea mai sus.',

    //Table field
    'table_cant_add' => 'Nu pot adăuga o nouă :entity',
    'table_max_reached' => 'Numărul maxim :max a fost atins',

    // google_map
    'google_map_locate' => 'Obține locația mea',

    // File manager
    'file_manager' => 'Manager fișiere',

    // InlineCreateOperation
    'related_entry_created_success' => 'Intrarea asociată a fost creată și selectată.',
    'related_entry_created_error' => 'Nu s-a putut crea intrarea asociată.',
    'inline_saving' => 'Se salvează...',

    // returned when no translations found in select inputs
    'empty_translations' => '(gol)',

    // The pivot selector required validation message
    'pivot_selector_required_validation_message' => 'Câmpul pivot este obligatoriu.',

    // Quick button messages
    'quick_button_ajax_error_title' => 'Cererea a eșuat!',
    'quick_button_ajax_error_message' => 'A apărut o eroare la procesarea cererii dumneavoastră.',
    'quick_button_ajax_success_title' => 'Cererea finalizată!',
    'quick_button_ajax_success_message' => 'Cererea dumneavoastră a fost finalizată cu succes.',

    // translations
    'no_attributes_translated' => 'Această intrare nu este tradusă în :locale.',
    'no_attributes_translated_href_text' => 'Completează câmpurile din :locale',
];
