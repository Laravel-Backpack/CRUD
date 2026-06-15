# CrudField JavaScript Library

---

<a name="about"></a>
## About

If you need to add custom interactions (if field is X then do Y), we have just the thing for you. You can easily add custom interactions, using our **CrudField JavaScript Library**. It's already loaded on our Create / Update pages, in the global `crud` object, and it makes it dead-simple to select a field - `crud.field('title')` - using a syntax that's very familiar to our PHP syntax, then do the most common things on it.

<a name="syntax"></a>
## Syntax

Here's everything our **CrudField JavaScript Library** provides:
- selectors:
    - `crud.field('title')` -> returns the `CrudField` object for a field with the name `title`;
    - `crud.field('testimonials').subfield('text')` -> returns the `CrudField` for the `text` subfield within the `testimonials` repeatable field;
- properties on `CrudField`:
    - `.name` - returns the field name (eg. `title`);
    - `.type` - returns the field type (eg. `text`);
    - `.input` - returns the DOM element that actually holds the value (the input/textarea/select);
    - `.value` - returns the value of that field (as a `string`);
- events on `CrudField`:
    - `.onChange(function(field) { do_someting(); })` - calls that function every time the field changes (which for most fields is upon each keytype);
- methods on `CrudField`:
    - `.hide()` - hides that field;
    - `.show()` - shows that field, if it was previously hidden;
    - `.disable()` - makes that field's input `disabled`;
    - `.enable()` - removes `disabled` from that field's input;
    - `.require()` - adds an asterisk next to that field's label;
    - `.unrequire()` - removes any asterisk next to that field's label;
    - `.change()` - trigger the change event (useful for a default state on pageload);
- specialty methods on `CrudField`:
    - `.check()` - if the field is a checkbox, checks it;
    - `.uncheck()` - if the field is a checkbox, unchecks it;
- current action:
    - `crud.action` -> returns the current action ("create" or "edit")

The beauty of this solution is that... it's flexible. Since it's only a JS library that makes the most difficult things easy... there is _no limit_ to what you can do with it. Just write pure JS or jQuery on top of it, to achieve your business logic.

<a name="how-to-use"></a>
## How to Use

### Step by Step

**Step 1.** Create a file to hold the custom JS. As a convention, we recommend you split up the JS by entity name. For example, for a Product we recommend creating `public/assets/js/admin/forms/product.js`.

**Step 2.** Load that script file in your CrudController, within `setupCreateOperation()` or `setupUpdateOperation()`, depending on when you want it loaded:

```php
    Widget::add()->type('script')->content('assets/js/admin/forms/product.js');
```

or 

```php
    Widget::add()->type('script')->content(asset('assets/js/admin/forms/product.js'));
```

**Step 3.** Inside that JS file, use the CrudField JS Library to manipulate fields, in any way you want. For example:

```javascript
 crud.field('agree_to_terms').onChange(function(field) {
    if (field.value == 1) {
        crud.field('agree_to_marketing_email').show();
    } else {
        crud.field('agree_to_marketing_email').hide();
    }
 }).change();
```

Alternatively, since all action methods also accept a `boolean` as a parameter, the above can also become:

```javascript
 crud.field('agree_to_terms').onChange(function(field) {
    crud.field('agree_to_marketing_email').show(field.value == 1);
 }).change();
```

Notice that we did three things here:
- selected a field using `crud.field('agree_to_terms')`;
- defined what happens when that field gets changed, using `.onChange()`;
- triggered the change event using `.change()` - that way, the closure will get evaluated first thing when the pageloads, not only when the first field actually gets changed;


<a name="examples"></a>
### Examples

We've identified the most common ways developers need to add interaction to their forms. Then we've documented them below. That way, it's easy for you to just copy-paste a solution, then customize to your needs. You can also see these examples fully working, in [our demo](https://demo.backpackforlaravel.com/admin/field-monster/create).

<a name="example-1"></a>
#### (1) Show / hide a field

When a checkbox is checked, show a second field:

```javascript
crud.field('visible').onChange(function(field) {
  crud.field('visible_where').show(field.value == 1);
}).change();
```
![Scenario 1 - when a checkbox is checked, show a second field](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_01.gif)

<a name="example-2"></a>
#### (2) Show/hide and enable/disable a field

When a checkbox is checked, show a second field _and_ un-disable it, by chaining the action methods:

```javascript
crud.field('visible').onChange(function(field) {
  crud.field('displayed_where').show(field.value == 1).enable(field.value == 1);
}).change();
```

Alternatively, a more readable but verbose version:

```javascript
crud.field('visible').onChange(function(field) {
    if (field.value == 1) {
        crud.field('displayed_where').show().enable();
    } else {
        crud.field('displayed_where').hide().disable();
    }
}).change();
```

<a name="example-3"></a>
#### (3) When a radio option is selected, show a second field

When a radio has something specific selected, show a second field:

```javascript
crud.field('type').onChange(function(field) {
  crud.field('custom_type').show(field.value == 3);
}).change();
```

![Scenario 3 - when a radio option is selected, show a second field](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_03.gif)

<a name="example-4"></a>
#### (4) When a select has a specific value, show a second field

When a select has something specific selected, show a second field:

```javascript
crud.field('parent').onChange(function(field) {
  crud.field('custom_parent').show(field.value == 6);
}).change();
```

![Scenario 4 - when a select has a specific value, show a second field](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_04.gif)

<a name="example-5"></a>
#### (5) When a checkbox is checked and has a certain value, do something

```javascript
function do_something() {
  console.log('Displayed AND custom parent.');
}

crud.field('parent').onChange(field => {
  if (field.value === 6 && crud.field('displayed').value == 1) {
    do_something();
  }
});
```

<a name="example-6"></a>
#### (6) When a checkbox is checked or a select has a certain value, show a third field

```javascript
let do_something_else = () => {
  console.log('Displayed OR custom parent.');
}

crud.field('displayed').onChange(field => {
  if (field.value === 1 || crud.field('parent').value == 6) {
    do_something_else();
  }
});

crud.field('parent').onChange(field => {
  if (field.value === 6 || crud.field('displayed').value == 1) {
    do_something_else();
  }
});
```

<a name="example-7"></a>
#### (7) When a select is a certain value, do something, otherwise do something else

```javascript
crud.field('parent').onChange(function(field) {
  switch(field.value) {
    case 2:
      console.log('doing something');
      break;
    case 3:
      console.log('doing something else');
      break;
    default:
      console.log('not doing anything');
  }
});
```

<a name="example-8"></a>
#### (8) When a checkbox is checked, automatically check another one

When a checkbox is checked, automatically check a different checkbox or radio:

```javascript
crud.field('visible').onChange(field => {
  crud.field('displayed').check(field.value == 1);
});
```

![Scenario 8 - when a checkbox is checked, automatically check another one](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_08.gif)

<a name="example-9"></a>
#### (9) When a text input is written into, write in a second input (eg. slug)

Create a slugged version of an input and put it into a second input:

```javascript
let slugify = text =>
  text.toString().toLowerCase().trim()
    .normalize('NFD')                // separate accent from letter
    .replace(/[\u0300-\u036f]/g, '') // remove all separated accents
    .replace(/\s+/g, '-')            // replace spaces with -
    .replace(/[^\w\-]+/g, '')        // remove all non-word chars
    .replace(/\-\-+/g, '-')          // replace multiple '-' with single '-'

crud.field('title').onChange(field => {
  crud.field('slug').input.value = slugify(field.value);
});
```

![Scenario 9 - when a text input is written into, write in a second input - eg. slug](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_09.gif)

<a name="example-10"></a>
#### (10) Calculate a total of multiple fields

When multiple inputs change, change a last input to calculate the total (or average, or difference):

```javascript
// Notice that we have to convert the input values from STRING to NUMBER
function calculate_discount_percentage() {
  let full_price = Number(crud.field('full_price').value);
  let discounted_price = Number(crud.field('discounted_price').value);
  let discount_percentage = (full_price - discounted_price) * 100 / full_price;

  crud.field('discount_percentage').input.value = discount_percentage;
}

crud.fields(['full_price', 'discounted_price']).forEach(function(field) {
  field.onChange(calculate_discount_percentage);
});
```

![Scenario 10 - update a total field](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_10.gif)

<a name="example-11"></a>
#### (11) When a repeatable subfield changes, disable another subfield

When a select subfield has been selected, enable a second subfield:

```javascript
crud.field('wish').subfield('country').onChange(function(field) {
    crud.field('wish').subfield('body', field.rowNumber).enable(field.value == '');
 });
```

![Scenario 11 - when a repeatable subfield changed, disable another subfield](https://backpackforlaravel.com/uploads/docs-5-0/fields/js-api/scenario_11.gif)


<a name="example-12"></a>
#### (12) When a checkbox is checked, hide repeatable and disable all subfields

When a checkbox is checked, disable all subfields in a repeatable and hide the repetable field entirely:

```javascript
crud.field('visible').onChange(field => {
    var subfields = $(crud.field('wish').input).parent().find('[data-repeatable-holder]').data('subfield-names');

    // disable/enable all subfields
    subfields.forEach(element => {
        crud.field('wish').subfield(element).enable(field.value == 1);
    });

    // hide/show the repeatable entirely
    crud.field('wish').show(field.value == 1);
}).change(); 
// this last change() call makes the code above also run on pageload, 
// so that if the checkbox starts checked, it's visible,
// if the checkbox starts unchecked, it's hidden
```

<a name="example-13"></a>
#### (13) When a morphable_type is selected, show another field

When using the `relationship` field on a morph relation, we can target the ID or TYPE inputs using brackets (the are _not_ subfields):

```javascript
crud.field('commentable[commentable_type]').onChange(field => {
    // hide/show the other input
    crud.field('wish').show(field.value == 'article');
}).change(); 
// this last change() call makes the code above also run on pageload
```
