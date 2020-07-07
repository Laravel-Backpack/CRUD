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
    'save_action_save_and_new'         => 'Sačuvaj i napravi novi',
    'save_action_save_and_edit'        => 'Sačuvaj i izmeni ovaj',
    'save_action_save_and_back'        => 'Sačuvaj i nazad',
    'save_action_save_and_preview'     => 'Sačuvaj i pregledaj',
    'save_action_changed_notification' => 'Podrazumevano ponašanje nakon čuvanja je promenjeno.',

    // Create form
    'add'                 => 'Dodaj',
    'back_to_all'         => 'Nazad na listu ',
    'cancel'              => 'Otkaži',
    'add_a_new'           => 'Dodaj novi ',

    // Edit form
    'edit'                 => 'Izmeni',
    'save'                 => 'Sačuvaj',

    // Translatable models
    'edit_translations' => 'Prevod',
    'language'          => 'Jezik',

    // CRUD table view
    'all'                       => 'Svi ',
    'in_the_database'           => 'u bazi',
    'list'                      => 'Lista',
    'actions'                   => 'Akcije',
    'preview'                   => 'Pregled',
    'delete'                    => 'Obriši',
    'admin'                     => 'Administrator',
    'details_row'               => 'Ovo je red sa detaljima. Promeni ga po svojoj želji.',
    'details_row_loading_error' => 'Došlo je do greške prilikom učitavanja detalja. Pokušaj ponovo.',
    'clone' => 'Dupliraj',
    'clone_success' => '<strong>Klonirano</strong><br>Dodan je novi unos sa istim podacima.',
    'clone_failure' => '<strong>Kloniranje nije uspelo</strong><br>Novi unos nije mogao da se kreira. Molim vas, pokušajte ponovo.',

    // Confirmation messages and bubbles
    'delete_confirm'                              => 'Jeste li sigurni da želite da izbrišete ovu stavku?',
    'delete_confirmation_title'                   => 'Obrisano',
    'delete_confirmation_message'                 => 'Stavka je uspešno izbrisana.',
    'delete_confirmation_not_title'               => 'NIJE obrisano',
    'delete_confirmation_not_message'             => 'Došlo je do greške. Vaša stavka možda nije izbrisana.',
    'delete_confirmation_not_deleted_title'       => 'Nije obrisano',
    'delete_confirmation_not_deleted_message'     => 'Ništa se nije dogodilo. Vaš unos je siguran.',

    // Bulk actions
    'bulk_no_entries_selected_title'   => 'Ništa nije selektovano',
    'bulk_no_entries_selected_message' => 'Morate izaberiti jednu ili više stavki.',

    // Bulk confirmation
    'bulk_delete_are_you_sure'   => 'Da li ste sigurni da želite da obrišete izabrane stavke (:number)?',
    'bulk_delete_sucess_title'   => 'Obrisano',
    'bulk_delete_sucess_message' => ' stavke obrisane.',
    'bulk_delete_error_title'    => 'Brisanje nije uspelo',
    'bulk_delete_error_message'  => 'Jednu ili više stavki nije bilo moguće izbrisati',

    // Ajax errors
    'ajax_error_title' => 'Greška',
    'ajax_error_text'  => 'Greška pri učitavanju stranice. Osvežite stranicu.',

    // DataTables translation
    'emptyTable'     => 'Nema podataka u tabeli',
    'info'           => 'Prikaz od _START_ do _END_ od _TOTAL_ unosa',
    'infoEmpty'      => '',
    'infoFiltered'   => '(isfiltrirano od _MAX_ unosa)',
    'infoPostFix'    => '.',
    'thousands'      => ',',
    'lengthMenu'     => '_MENU_ stavki po stranici',
    'loadingRecords' => 'Učitavanje...',
    'processing'     => 'Pricesiranje...',
    'search'         => 'Pretraga',
    'zeroRecords'    => 'Nije pronadjeno ništa',
    'paginate'       => [
        'first'    => 'Prvi',
        'last'     => 'Poslednji',
        'next'     => 'Sledeći',
        'previous' => 'Prethodni',
    ],
    'aria' => [
        'sortAscending'  => ': aktivirajte za uzlazno sortiranje',
        'sortDescending' => ': aktivirajte za silazno sortiranje',
    ],
    'export' => [
        'export'            => 'Izvoz',
        'copy'              => 'Kopiraj',
        'excel'             => 'Excel',
        'csv'               => 'CSV',
        'pdf'               => 'PDF',
        'print'             => 'Štampa',
        'column_visibility' => 'Vidljivost kolone',
    ],

    // global crud - errors
    'unauthorized_access' => 'Neovlašteni pristup - nemate potrebna dozvola za prikaz ove stranice.',
    'please_fix'          => 'Molim Vas ispravite sledeće greške:',

    // global crud - success / error notification bubbles
    'insert_success' => 'Stavka je uspešno dodata.',
    'update_success' => 'Stavka je uspešno izmenjena.',

    // CRUD reorder view
    'reorder'                      => 'Promeni redosled',
    'reorder_text'                 => 'Koristite drag&drop za promenu redosleda.',
    'reorder_success_title'        => 'Gotovo',
    'reorder_success_message'      => 'Redosled je sačuvan.',
    'reorder_error_title'          => 'Greška',
    'reorder_error_message'        => 'Redosled nije sačuvan.',

    // CRUD yes/no
    'yes' => 'Da',
    'no'  => 'Ne',

    // CRUD filters navbar view
    'filters'        => 'Filteri',
    'toggle_filters' => 'Uključi/Isključi filtere',
    'remove_filters' => 'Ukloni filtere',

    // Fields
    'browse_uploads'            => 'Pregled fajlova',
    'select_all'                => 'Selektuj sve',
    'select_files'              => 'Selektuj fajlove',
    'select_file'               => 'Selektuj fajl',
    'clear'                     => 'Ukloni',
    'page_link'                 => 'Link stranice',
    'page_link_placeholder'     => 'http://example.com/your-desired-page',
    'internal_link'             => 'Interni link',
    'internal_link_placeholder' => 'Interni slug. Primer: \'admin/page\' (bez navodnika) za \':url\'',
    'external_link'             => 'Eksterni link',
    'choose_file'               => 'Izaberi fajl',

    //Table field
    'table_cant_add'    => 'Nije moguće dodati :entity',
    'table_max_reached' => 'Maksimalni broj od :max dostignut',

    // File manager
    'file_manager' => 'Menadžer Fajlova',

    // InlineCreateOperation
    'related_entry_created_success' => 'Srodni unos je kreiran i izabran.',
    'related_entry_created_error' => 'Srodni unos nije kreiran.',
];
