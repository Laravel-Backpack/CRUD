@php
$columnCrud = \Backpack\CRUD\Backpack::crudFromController($column['controller']);
@endphp

<x-datatable :crud="$columnCrud" />