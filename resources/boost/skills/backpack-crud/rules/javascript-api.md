# Backpack CRUD — JavaScript API (crud.field)

The `crud.field()` JS API provides programmatic control over fields in Create/Update forms. Always load JS via `Widget::add()->type('script')`.

## Selector

```javascript
crud.field('field_name');        // by name
crud.field('field_name').value;  // current value
crud.field('field_name').input;  // the DOM input element
crud.field('field_name').row;    // the wrapper div.row
crud.field('field_name').type;   // the field type
```

## Events

```javascript
// React to value changes
crud.field('category_id').onChange(function(field) {
    crud.field('subcategory_id').show(field.value == 1);
}).change();  // trigger immediately on page load

// All fields: onChange, onShow, onHide, onEnable, onDisable, onRequire, onUnrequire
```

## Visibility Methods

```javascript
crud.field('name').show();       // show field
crud.field('name').hide();       // hide field
crud.field('name').enable();     // enable input
crud.field('name').disable();    // disable input (greyed out)
crud.field('name').require();    // make required (adds asterisk)
crud.field('name').unrequire();  // remove required
```

## Checkbox / Switch Methods

```javascript
crud.field('is_active').check();    // check the box
crud.field('is_active').uncheck();  // uncheck
```

## Subfields (Repeatable, Table, etc.)

```javascript
crud.field('items').subfield('product');   // access a subfield
crud.field('items').subfield('quantity').onChange(function(field) {
    var row = field.row;  // the parent repeatable row
});
```

## Patterns

Load the script:

```php
// In controller:
Widget::add()->type('script')->content('assets/js/admin/forms/product.js');
```

**Pattern 1 — Show/hide based on select:**
```javascript
crud.field('has_discount').onChange(function(field) {
    crud.field('discount_amount').show(field.value == 1);
}).change();
```

**Pattern 2 — Dependent selects:**
```javascript
crud.field('country_id').onChange(function(field) {
    crud.field('city_id').value = '';
    crud.field('city_id').enable(field.value != '');
}).change();
```

**Pattern 3 — Compute field from others:**
```javascript
crud.field('quantity').onChange(updateTotal).change();
crud.field('price').onChange(updateTotal).change();

function updateTotal(field) {
    var qty = parseFloat(crud.field('quantity').value) || 0;
    var price = parseFloat(crud.field('price').value) || 0;
    crud.field('total').input.value = (qty * price).toFixed(2);
}
```

**Pattern 4 — Set value and disable:**
```javascript
crud.field('status').value = 'draft';
crud.field('status').disable();
```

## Gotchas
- Always call `.change()` after binding `.onChange()` if you want it to run on page load.
- Scripts MUST be loaded via `Widget::add()->type('script')` — inline `<script>` tags in field views may not work.
- Subfield access uses `.subfield('name')`, not `.field('name')`.
- Field value is always a string. Cast to number when doing math.
