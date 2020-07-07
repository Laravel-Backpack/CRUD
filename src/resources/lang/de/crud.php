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
    'save_action_save_and_new'         => 'Speichern und neu',
    'save_action_save_and_edit'        => 'Speichern und bearbeiten',
    'save_action_save_and_back'        => 'Speichern und zurück',
    'save_action_changed_notification' => 'Standardverhalten nach dem Speichern wurde geändert.',

    // Create form
    'add'                 => 'Anlegen: ',
    'back_to_all'         => 'Zurück zur Übersicht ',
    'cancel'              => 'Abbrechen',
    'add_a_new'           => 'Neu anlegen: ',

    // Edit form
    'edit'                 => 'Bearbeiten',
    'save'                 => 'Speichern',

    // Translatable models
    'edit_translations'    => 'ÜBERSETZUNG BEARBEITEN',
    'language'             => 'Sprache',

    // CRUD table view
    'all'                       => 'Alle ',
    'in_the_database'           => 'in der Datenbank',
    'list'                      => 'Liste',
    'actions'                   => 'Aktionen',
    'preview'                   => 'Vorschau',
    'delete'                    => 'Löschen',
    'admin'                     => 'Admin',
    'details_row'               => 'Diese Zeile zeigt die Details und kann nach Belieben geändert werden.',
    'details_row_loading_error' => 'Beim Laden der Details ist ein Fehler aufgetreten. Bitte versuche es noch einmal.',

    // Confirmation messages and bubbles
    'delete_confirm'                              => 'Soll dieser Eintrag wirklich gelöscht werden?',
    'delete_confirmation_title'                   => 'Eintrag gelöscht',
    'delete_confirmation_message'                 => 'Der Eintrag wurde erfolgreich gelöscht.',
    'delete_confirmation_not_title'               => 'NICHT gelöscht',
    'delete_confirmation_not_message'             => 'Ein Fehler ist aufgetreten. Der Eintrag wurde möglicherweise nicht gelöscht.',
    'delete_confirmation_not_deleted_title'       => 'Nicht gelöscht',
    'delete_confirmation_not_deleted_message'     => 'Nichts passiert. Der Eintrag wurde nicht gelöscht.',

    // DataTables translation
    'emptyTable'     => 'Keine Einträge vorhanden',
    'info'           => 'Zeigt _START_ bis _END_ von _TOTAL_ Einträgen',
    'infoEmpty'      => '',
    'infoFiltered'   => '(gefiltert von insgesamt _MAX_ Einträgen)',
    'infoPostFix'    => '.',
    'thousands'      => '.',
    'lengthMenu'     => '_MENU_ Einträge pro Seite',
    'loadingRecords' => 'Laden...',
    'processing'     => 'Verarbeiten...',
    'search'         => 'Suchen',
    'zeroRecords'    => 'Keine passenden Einträge gefunden',
    'paginate'       => [
        'first'    => 'Erste',
        'last'     => 'Letzte',
        'next'     => 'Nächste',
        'previous' => 'Vorherige',
    ],
    'aria' => [
        'sortAscending'  => ': aktivieren um aufsteigend zu Sortieren',
        'sortDescending' => ': aktivieren um absteigend zu Sortieren',
    ],
    'export' => [
        'copy'              => 'Kopieren',
        'excel'             => 'Excel',
        'csv'               => 'CSV',
        'pdf'               => 'PDF',
        'print'             => 'Drucken',
        'column_visibility' => 'Sichtbarkeit der Spalte',
    ],

    // global crud - errors
    'unauthorized_access' => 'Unbefugter Zugriff - Sie haben nicht die notwendigen Rechte um diese Seite anzuzeigen.',
    'please_fix'          => 'Bitte beheben Sie die folgenden Fehler:',

    // global crud - success / error notification bubbles
    'insert_success' => 'Der Eintrag wurde erfolgreich angelegt.',
    'update_success' => 'Der Eintrag wurde erfolgreich geändert.',

    // CRUD reorder view
    'reorder'                      => 'Sortiere',
    'reorder_text'                 => 'Zum Ändern der Reihenfolge Einträge verschieben. (Drag&Drop)',
    'reorder_success_title'        => 'Fertig',
    'reorder_success_message'      => 'Die Reihenfolge wurde gespeichert',
    'reorder_error_title'          => 'Fehler',
    'reorder_error_message'        => 'Die Reihenfolge konnte nicht gespeichert werden',

    // CRUD yes/no
    'yes' => 'Ja',
    'no'  => 'Nein',

    // CRUD filters navbar view
    'filters'        => 'Filter',
    'toggle_filters' => 'Filter umschalten',
    'remove_filters' => 'Filter entfernen',

    // Fields
    'browse_uploads'            => 'Uploads durchsuchen',
    'clear'                     => 'Löschen',
    'page_link'                 => 'Link zur Seite',
    'page_link_placeholder'     => 'http://example.com/ihre-gewuenschte-seite',
    'internal_link'             => 'Interner Link',
    'internal_link_placeholder' => 'URL-sicherer Name ("Slug"). Bsp: \'admin/page\' (ohne Anführungszeichen) für \':url\'',
    'external_link'             => 'Externer Link',
    'choose_file'               => 'Datei auswählen',

    //Table field
    'table_cant_add'    => 'Kann :entity nicht hinzufügen',
    'table_max_reached' => 'Maximale Anzahl von :max erreicht',

    // File manager
    'file_manager' => 'Datei-Manager',
];
