## pdoTools

Library for MODX Revolution for creating fast snippets and using file elements instead of DB elements. It's used by Tickets and miniShop2.

### Main features
- Building queries with xPDO.
- Retrieve results with PDO.
- Includes Fenom template engine.
- New pdoTools::getChunk() function, that processing placeholders faster than original modX::getChunk().
- New pdoTools::runSnippet() function, that is faster and more powerful than original modX::runSnippet().
- Added functionality of file elements.
- Includes some snippets for convenient and fast development.
  - pdoResources
  - pdoMenu
  - pdoCrumbs
  - pdoPage
  - pdoSitemap
  - pdoUsers
  - pdoTitle
  - pdoField
  - pdoArchive
  - pdoNeighbor

pdoTools snippets will work so faster, than more fields you will retrieve from database at one query.

\* Required by Tickets and miniShop2.
