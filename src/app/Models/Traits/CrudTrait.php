<?php

namespace Backpack\CRUD\app\Models\Traits;

trait CrudTrait
{
    use HasIdentifiableAttribute;
    use HasEnumFields;
    use HasRelationshipFields;
    use HasUploadFields;
    use HasFakeFields;
    use HasTranslatableFields;
    use CanGenerateUuid;
    use CanChangeRouteKey;

    public static function hasCrudTrait()
    {
        return true;
    }
}
