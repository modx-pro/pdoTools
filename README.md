## pdoTools

Library for MODX Revolution for creating fast snippets and using file elements instead of DB elements. It's used by Tickets and miniShop2.

### Main features
- Builds queries with xPDO.
- Retrieve results with PDO.
- New pdoTools::getChunk() function, that processing placeholders faster, than original modX::getChunk().
- New pdoTools::runSnippet() function, that processing placeholders faster, than original modX::getChunk().
- Adds Fenom template engine.
- Presents some snippets for convenient and fast development.
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

Required by Tickets and miniShop2.