<x-dynamic-component
    :component="$crud->getOperationSetting('component')"
    :columns="$columns ?? $crud->columns()"
    :entry="$entry"
    :crud="$crud"
    :display-actions-column="$displayActionsColumn ?? true"
/>
