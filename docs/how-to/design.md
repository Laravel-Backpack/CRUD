## Design

### How to customize CRUD Panel design (CSS hooks)

Our CRUD panel design is the result of 7+ years of feedback, from both admins and developers. Each iteration has made it better and better, to the point where admins find it very intuitive to do everything they need, and Backpack is lauded for how intuitive its design is. So we do _not_ recommend moving components around.

However, you might want to change the styling - colors, border, padding etc. Especially if you're creating a new theme. For that purpose, we have made sure all CRUD operations have the `bp-section` attributes in key elements, so you can and reliably target them. For example:

- List operation
 - `bp-section=page-header` for the page header (between breadcrumbs and content)
 - `bp-section=crud-operation-reorder` for the content
- Create operation
 - `bp-section=page-header` for the page header (between breadcrumbs and content)
 - `bp-section=crud-operation-create` for the content
- Update operation
 - `bp-section=page-header` for the page header (between breadcrumbs and content)
 - `bp-section=crud-operation-update` for the content
- Show operation
 - `bp-section=page-header` for the page header (between breadcrumbs and content)
 - `bp-section=crud-operation-show` for the content
- Reorder operation
 - `bp-section=page-header` for the page header (between breadcrumbs and content)
 - `bp-section=crud-operation-reorder` for the content

This is a very simple yet effective solution for your custom CSS or JS to target the header or target specific operations, since each operation has its content wrapped around an element with `bp-section=crud-operation-xxx`.
