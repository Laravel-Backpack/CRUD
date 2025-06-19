<x-dynamic-component
    :component="$crud->getOperationSetting('component')"
    :entry="$entry"
    :crud="$crud"
    :columns="$columns"
    :display-buttons="$displayActionsColumn ?? true"
/>
