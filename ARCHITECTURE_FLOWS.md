# Architecture & Flow Diagrams

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Backpack CRUD                           │
│                   ListOperation                             │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    │ User types search term
                    │ "smith"
                    ▼
┌─────────────────────────────────────────────────────────────┐
│            ListOperation::search()                          │
│  - Gets search term from DataTable                          │
│  - Calls applySearchTerm()                                  │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    │
                    ▼
┌─────────────────────────────────────────────────────────────┐
│      Search Trait: applySearchTerm()                        │
│  - Iterates through all columns                             │
│  - Calls applySearchLogicForColumn() for each               │
└───────────────────┬─────────────────────────────────────────┘
                    │
                    │ For each column...
                    ▼
┌─────────────────────────────────────────────────────────────┐
│   applySearchLogicForColumn()                               │
│                                                              │
│   Is field translatable + JSON?                             │
│   YES ↓              NO ↓                                    │
└──────┬──────────────────┬────────────────────────────────────┘
       │                  │
       ▼                  ▼
    ┌──────────┐      ┌──────────────────┐
    │Translate │      │Regular Search    │
    │Search    │      │(orWhere)         │
    │(JSON)    │      └──────────────────┘
    └────┬─────┘
         │
    ┌────▼────────────────────┐
    │TranslatableSearch Trait  │
    │applyTranslatableSearch() │
    └────┬────────────────────┘
         │
    ┌────▼────────────────┐
    │Detect DB Driver     │
    └────┬───────────────┬┘
         │               │
    ┌────▼────┐   ┌─────▼────┐
    │MySQL    │   │PostgreSQL │
    │JSON_    │   │JSON ops   │
    │EXTRACT  │   │+ ILIKE    │
    └────┬────┘   └─────┬────┘
         │               │
    ┌────▼──────────────────┐
    │Apply Case-Insensitive │
    │Search (LOWER + LIKE)  │
    └────┬──────────────────┘
         │
    ┌────▼──────────────────────────┐
    │Database returns matching rows  │
    │(case-insensitive match)        │
    └────┬──────────────────────────┘
         │
    ┌────▼──────────────────────────┐
    │Return results to DataTable UI  │
    └───────────────────────────────┘
```

## Search Flow Decision Tree

```
Start: Search Term Received
         │
         ▼
    Column Check Loop
         │
         ├─ Get column type (text, email, textarea, date, etc.)
         │
         ▼
    Does column have custom searchLogic?
         │
         ├─ YES → Execute custom searchLogic closure
         │         └─ Done (skip rest)
         │
         ├─ NO → Continue to type-based logic
         │        │
         │        ▼
         │   Is it text/email/textarea?
         │        │
         │        ├─ YES ↓
         │        │   Is field translatable?
         │        │   ├─ YES → Is column JSON?
         │        │   │        ├─ YES → Use applyTranslatableJsonSearch()
         │        │   │        └─ NO → Use regular orWhere()
         │        │   └─ NO → Use regular orWhere()
         │        │
         │        ├─ NO → Check if date/datetime
         │        │        └─ YES → Use whereDate()
         │        │
         │        └─ Other types (select, etc.)
         │              └─ Apply type-specific logic
         │
         └─ Next column...

After all columns processed:
         ▼
    Return filtered query results
         ▼
    Display in DataTable
```

## Database-Specific Query Examples

### MySQL 5.7.8+

```
Input: Search "smith" in column "company_name"
Field: {"en": "Smith Manufacturing", "fr": "Manufacture Smith"}

Generated Query:
WHERE LOWER(JSON_UNQUOTE(JSON_EXTRACT(
    `companies`.`company_name`,
    '$en'
))) LIKE '%smith%'

Execution:
1. JSON_EXTRACT(..., '$en') → "Smith Manufacturing"
2. JSON_UNQUOTE(...) → Smith Manufacturing
3. LOWER(...) → smith manufacturing
4. LIKE '%smith%' → ✓ MATCH
```

### PostgreSQL 9.2+

```
Input: Search "smith" in column "company_name"
Field: {"en": "Smith Manufacturing", "fr": "Manufacture Smith"}

Generated Query:
WHERE LOWER(`companies`.`company_name` -> 'en') ILIKE '%smith%'

Execution:
1. column -> 'en' → "Smith Manufacturing"
2. LOWER(...) → smith manufacturing
3. ILIKE '%smith%' → ✓ MATCH (ILIKE is case-insensitive)
```

### SQLite 3.38.0+

```
Input: Search "smith" in column "company_name"
Field: {"en": "Smith Manufacturing", "fr": "Manufacture Smith"}

Generated Query:
WHERE LOWER(JSON_EXTRACT(
    `companies`.`company_name`,
    '$en'
)) LIKE '%smith%'

Execution:
1. JSON_EXTRACT(..., '$en') → "Smith Manufacturing"
2. LOWER(...) → smith manufacturing
3. LIKE '%smith%' → ✓ MATCH
```

## Code Execution Flow

```
ListOperation::search() [HTTP POST /search]
    ↓
ListOperation Line 87: applySearchTerm($search['value'])
    ↓
Search.php:applySearchTerm()
    │ Loops through all columns
    ├─ Column 1: name
    │   └─ applySearchLogicForColumn($query, $column, $searchTerm)
    │       ├─ Check if custom searchLogic exists? NO
    │       ├─ Type is 'text'? YES
    │       └─ Is translatable + JSON?
    │           ├─ YES → TranslatableSearch::applyTranslatableJsonSearch()
    │           │        ├─ Get column name, table, locale
    │           │        ├─ Check database driver
    │           │        ├─ Build appropriate SQL query
    │           │        └─ $query->orWhereRaw(..., [$locale, '%term%'])
    │           └─ NO → $query->orWhere(...)
    │
    ├─ Column 2: description
    │   └─ ... (same process)
    │
    └─ Column N: ...

ListOperation Line 99: $entries = $this->crud->getEntries()
    ↓
Returns filtered results with case-insensitive matches
    ↓
ListOperation Line 106: getEntriesAsJsonForDatatables()
    ↓
Return JSON to DataTable JavaScript
    ↓
DataTable displays matching rows to user
```

## Class Hierarchy

```
┌──────────────────────────────────────┐
│     CrudPanel                        │
│  (main CRUD management class)        │
└────────────┬─────────────────────────┘
             │
             │ uses
             ▼
┌──────────────────────────────────────┐
│    Search Trait                      │  ← ENHANCED
│  (search functionality)              │
│                                      │
│  - applySearchTerm()                 │
│  - applySearchLogicForColumn()       │
│    └─ MODIFIED to detect             │
│       translatable fields            │
└────────┬────────────────────────────┘
         │
         │ uses
         ▼
┌──────────────────────────────────────┐
│  TranslatableSearch Trait            │  ← NEW
│  (translatable-specific logic)       │
│                                      │
│  - applyTranslatableJsonSearch()     │
│  - isTranslatableField()             │
│  - isJsonColumn()                    │
│  - isDatabaseMySQL()                 │
│  - isDatabasePostgreSQL()            │
│  - isDatabaseSQLite()                │
└──────────────────────────────────────┘
```

## Data Flow Example

### Scenario: Search for "tech" in translatable Company model

```
User Input:
┌──────────────────────────────────┐
│  Search Box: "tech"              │
│  (lowercase, partial match)      │
└──────────────────────────────────┘
         │
         ▼
Database Contains (JSON):
┌──────────────────────────────────────────┐
│  id | name                               │
├──────────────────────────────────────────┤
│  1  | {"en": "TechVision Inc"}           │
│  2  | {"en": "Microsoft Corporation"}    │
│  3  | {"en": "TechWorld Solutions"}      │
│  4  | {"en": "Google LLC"}               │
└──────────────────────────────────────────┘
         │
         ▼
Processing Steps:
┌──────────────────────────────────────────┐
│ 1. Search term: "tech"                   │
│ 2. Column: "name" (type: text)           │
│ 3. Is translatable? YES                  │
│ 4. Is JSON? YES                          │
│ 5. Locale: "en"                          │
│ 6. Generate query:                       │
│    LOWER(JSON_UNQUOTE(                  │
│     JSON_EXTRACT(name, '$en')           │
│    )) LIKE '%tech%'                     │
│ 7. Execute query                         │
└──────────────────────────────────────────┘
         │
         ▼
Results Returned:
┌──────────────────────────────────────────┐
│  id | name                               │
├──────────────────────────────────────────┤
│  1  | {"en": "TechVision Inc"} ✓        │
│  3  | {"en": "TechWorld Solutions"} ✓   │
└──────────────────────────────────────────┘
         │
         ▼
Display to User:
┌──────────────────────────────────────────┐
│  Results for "tech":                     │
│  • TechVision Inc                        │
│  • TechWorld Solutions                   │
│                                          │
│  (without solution: 0 results)           │
└──────────────────────────────────────────┘
```

## Comparison: Before vs After

### Before Solution ❌

```
User searches: "smith"
Database Query: WHERE name LIKE '%smith%'

Result:
- {"en": "smith"} ✓ Found
- {"en": "Smith"} ✗ NOT found (case mismatch)
- {"en": "SMITH"} ✗ NOT found (case mismatch)

Total: 1 result (incomplete)
```

### After Solution ✅

```
User searches: "smith"
Database Query: WHERE LOWER(JSON_UNQUOTE(
                    JSON_EXTRACT(name, '$en')
                )) LIKE '%smith%'

Result:
- {"en": "smith"} ✓ Found
- {"en": "Smith"} ✓ Found
- {"en": "SMITH"} ✓ Found

Total: 3 results (complete)
```

## Performance Consideration

### Query Optimization Chain

```
Raw Input: "SEARCH TERM"
    │
    ├─ Convert to lowercase (app level)
    │
    ├─ Add wildcard for partial match
    │
    ├─ Pass to database query
    │
    └─ Database executes:
       LOWER(JSON_EXTRACT(...)) LIKE '%term%'
       │
       ├─ JSON_EXTRACT: Native DB function (fast)
       ├─ LOWER: Native DB function (fast)
       └─ LIKE: Native DB function (fast)
       
       Summary: All database-side = No overhead
```

## Extension Points

If you want to customize this behavior:

```
1. Custom search logic per column:
   $this->crud->setColumnDetails('name', [
       'searchLogic' => function($query, $column, $searchTerm) {
           // Your custom search here
       }
   ]);

2. Disable search for column:
   $this->crud->setColumnDetails('name', [
       'searchLogic' => false
   ]);

3. Change search operator:
   // In config/backpack/operations/list.php
   'searchOperator' => 'like',  // or 'ilike' for PostgreSQL

4. Override translatable detection:
   // by creating custom search logic
```

---

**All diagrams are text-based for easy documentation compatibility.**
