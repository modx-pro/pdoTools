## pdoTools

pdoTools is a set of everyday MODX Revolution snippets plus a small library that keeps them fast. Queries are built with xPDO and run through PDO. Elements can live in the database or in files. Tickets and miniShop2 use it.

**Documentation:** [docs.modx.pro/components/pdotools](https://docs.modx.pro/components/pdotools/)

### Advantages

Every pdoTools snippet shares the same core:

- Database work goes through PDO. xPDO objects are created only when needed.
- Simple placeholders in chunks are preprocessed so the MODX parser only handles complex tags.
- TV parameters are sorted, prepared, processed, and output correctly.
- Chunk code can sit inline in the snippet call, in a normal chunk, or in a static file (`@FILE`, `@INLINE`, `@TEMPLATE`).
- Fast placeholders wrap a value in tags only when it is not empty (covers many `isempty`-style cases).
- A timed work log for debugging bottlenecks.

Queries can target any tables, with joins and conditions. Pagination stays compatible with getPage via [pdoPage](https://docs.modx.pro/components/pdotools/snippets/pdopage). From 2.0 the package includes the [Fenom](https://docs.modx.pro/components/pdotools/parser) template engine.

Compared with stock MODX helpers:

- `pdoTools::getChunk()` processes placeholders faster than `modX::getChunk()`.
- `pdoTools::runSnippet()` is faster and more powerful than `modX::runSnippet()`.

These snippets usually run faster when you select more fields in a single query.

### Composition

#### Snippets

| Snippet | Role |
| --- | --- |
| [pdoResources](https://docs.modx.pro/components/pdotools/snippets/pdoresources) | Resource lists (parameter-compatible replacement for getResources) |
| [pdoMenu](https://docs.modx.pro/components/pdotools/snippets/pdomenu) | Menus (Wayfinder replacement) |
| [pdoPage](https://docs.modx.pro/components/pdotools/snippets/pdopage) | Pagination (getPage replacement) |
| [pdoCrumbs](https://docs.modx.pro/components/pdotools/snippets/pdocrumbs) | Breadcrumbs (BreadCrumb replacement) |
| [pdoUsers](https://docs.modx.pro/components/pdotools/snippets/pdousers) | Users, with role and group filters |
| [pdoSitemap](https://docs.modx.pro/components/pdotools/snippets/pdositemap) | Sitemap generation (GoogleSiteMap replacement) |
| [pdoNeighbors](https://docs.modx.pro/components/pdotools/snippets/pdoneighbors) | Links to neighboring resources |
| [pdoField](https://docs.modx.pro/components/pdotools/snippets/pdofield) | Any resource field (getResourceField / UltimateParent replacement) |
| [pdoTitle](https://docs.modx.pro/components/pdotools/snippets/pdotitle) | Page title helper |
| [pdoArchive](https://docs.modx.pro/components/pdotools/snippets/pdoarchive) | Archive listings by date |

#### Classes

| Class | Docs |
| --- | --- |
| [pdoTools](https://docs.modx.pro/components/pdotools/classes/pdotools) | Core helpers (`getChunk`, `runSnippet`, caching, …) |
| [pdoFetch](https://docs.modx.pro/components/pdotools/classes/pdofetch) | Query builder and PDO fetch layer |
| [pdoParser](https://docs.modx.pro/components/pdotools/classes/pdoparser) | Parser integration (Fenom / FastField tags) |

### More documentation

- [General properties](https://docs.modx.pro/components/pdotools/general-properties) shared by the snippets
- [File elements](https://docs.modx.pro/components/pdotools/file-elements)
- [Parser](https://docs.modx.pro/components/pdotools/parser)

### Links

- Docs: https://docs.modx.pro/components/pdotools/
- Issues: https://github.com/modx-pro/pdoTools/issues
