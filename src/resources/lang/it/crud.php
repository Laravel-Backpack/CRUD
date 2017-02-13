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

    // Create form
    'add'                 => 'Aggiungi',
    'back_to_all'         => 'Torna a tutti i ',
    'cancel'              => 'Annulla',
    'add_a_new'           => 'Aggiungi un nuovo ',

        // Create form - advanced options
        'after_saving'            => 'Dopo il salvataggio',
        'go_to_the_table_view'    => 'vai alla lista di tutti gli elementi',
        'let_me_add_another_item' => 'aggiungi un altro elemento',
        'edit_the_new_item'       => 'modifica il nuovo elemento',

    // Edit form
    'edit'                 => 'Modifica',
    'save'                 => 'Salva',

    // Revisions
    'revisions'            => 'Revisioni',
    'no_revisions'         => 'Nessuna revisione Trovato',
    'created_this'          => 'ha creato questo',
    'changed_the'          => 'cambiato il',
    'restore_this_value'   => 'ripristinare questo valore',
    'from'                 => 'de parte di',
    'to'                   => 'a',
    'undo'                 => 'Disfare',
    'revision_restored'    => 'Revisione successo restaurato',

    // CRUD table view
    'all'                       => 'Tutti i ',
    'in_the_database'           => 'nel database',
    'list'                      => 'Lista',
    'actions'                   => 'Azioni',
    'preview'                   => 'Anteprima',
    'delete'                    => 'Elimina',
    'admin'                     => 'Amministrazione',
    'details_row'               => 'Questa è la riga dei dettagli. Modificala a tuo piacimento.',
    'details_row_loading_error' => "C'è stato un errore caricando i dettagli. Riprova.",

        // Confirmation messages and bubbles
        'delete_confirm'                              => 'Sei sicuro di eliminare questo elemento?',
        'delete_confirmation_title'                   => 'Elemento eliminato',
        'delete_confirmation_message'                 => "L'elemento è stato eliminato con successo.",
        'delete_confirmation_not_title'               => 'NON eliminato',
        'delete_confirmation_not_message'             => "C'è stato un errore. L'elemento potrebbe non essere stato eliminato.",
        'delete_confirmation_not_deleted_title'       => 'Non eliminato',
        'delete_confirmation_not_deleted_message'     => "Non è successo niente. L'elemento è al sicuro.",

        // DataTables translation
        'emptyTable'     => 'Nessun record da visualizzare',
        'info'           => 'Visualizzando da _START_ a _END_ record di _TOTAL_',
        'infoEmpty'      => 'Visualizzando da 0 a 0 record di 0',
        'infoFiltered'   => '(filtrati da _MAX_ record totali)',
        'infoPostFix'    => '',
        'thousands'      => ',',
        'lengthMenu'     => '_MENU_ record per pagina',
        'loadingRecords' => 'Caricamento...',
        'processing'     => 'Elaborazione...',
        'search'         => 'Cerca: ',
        'zeroRecords'    => 'Nessun record corrispondente',
        'paginate'       => [
            'first'    => 'Primo',
            'last'     => 'Ultimo',
            'next'     => 'Prossimo',
            'previous' => 'Precedente',
        ],
        'aria' => [
            'sortAscending'  => ': attiva per ordinare la colonna ascendentemente',
            'sortDescending' => ': attiva per ordinare la colonna discendentemente',
        ],

    // global crud - errors
    'unauthorized_access' => 'Accesso non autorizzato - non hai i permessi necessari per vedere questa pagina.',
    'please_fix' => 'Per favore correggi i seguenti errori:',

    // global crud - success / error notification bubbles
    'insert_success' => "L'elemento è stato aggiunto correttamente.",
    'update_success' => "L'elemento è stato aggiornato correttamente.",

    // CRUD reorder view
    'reorder'                      => 'Riordina',
    'reorder_text'                 => 'Seleziona e trascina per riordinare.',
    'reorder_success_title'        => 'Fatto',
    'reorder_success_message'      => 'Il tuo ordinamento è stato salvato.',
    'reorder_error_title'          => 'Errore',
    'reorder_error_message'        => 'Il tuo ordinamento non è stato salvato.',

    // CRUD yes/no
    'yes' => 'Sì',
    'no' => 'No',

    // Fields
    'browse_uploads' => 'Sfoglia file caricati',
    'clear' => 'Pulisci',
    'page_link' => 'Link Pagina',
    'page_link_placeholder' => 'http://esempio.com/pagina-desiderata',
    'internal_link' => 'Link Interno',
    'internal_link_placeholder' => 'Slug interno. Es: \'admin/page\' (no quotes) for \':url\'',
    'external_link' => 'Link Esterno',

    //Table field
    'table_cant_add' => 'Impossibile aggiungere nuovo :entity',
    'table_max_reached' => 'Massimo numero di :max reggiunto',

    // address_google field - notification bubbles
    'address_google_error_title' => 'Oops qualcosa è andato storto...',
    'address_google_error_message' => 'Google non conosce questo indirizzo.'.PHP_EOL.'I campi devono essere riempiti manualmente... ',
];
