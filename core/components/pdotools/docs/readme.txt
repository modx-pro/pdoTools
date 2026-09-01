--------------------
pdoTools
--------------------
Author: Vasiliy Naumkin <bezumkin@yandex.ru>
--------------------

Library for MODX Revolution for creating fast snippets and using file elements instead of DB elements. Used by Tickets and miniShop2.

Main features
- Building queries with xPDO.
- Retrieving results with PDO.
- Includes the Fenom template engine.
- pdoTools::getChunk() processes placeholders faster than modX::getChunk().
- pdoTools::runSnippet() is faster and more flexible than modX::runSnippet().
- File elements (@FILE, @INLINE, @TEMPLATE).
- Snippets for common tasks:
  - pdoResources
  - pdoMenu
  - pdoCrumbs
  - pdoPage
  - pdoSitemap
  - pdoUsers
  - pdoTitle
  - pdoField
  - pdoArchive
  - pdoNeighbors

pdoTools snippets run faster when you select more fields in a single query.

--------------------
Feel free to suggest ideas/improvements/bugs on GitHub:
https://github.com/modx-pro/pdoTools/issues
