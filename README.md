# Phabricator Wiki (Phriction) Exporter for MediaWiki

This is PHP command line tool which connects to the [Phriction](https://www.phacility.com/phabricator/phriction/) database and exports wiki pages to an XML file
which can then be imported into [MediaWiki](https://www.mediawiki.org/wiki/MediaWiki).

Tested with Phabricator version d5a7d4081daa Jun 16 2020 and MediaWiki 1.40.1



## Remarkup to Wikitext conversion

Phrictions uses a markup language called [Remarkup](https://secure.phabricator.com/book/phabricator/article/remarkup/) 
and MediaWiki uses [Wikitext](https://en.wikipedia.org/wiki/Help:Wikitext).

Supported Phrictions markup which will be converted to wikitext:

- headings
- bold
- italic
- monospaced
- deleted
- underlined
- highlighted
- literals
- lists
- links
- code blocks
- tables

Images and files used in Phriction content are not supported.



## Install

`git clone https://github.com/bmauser/phriction-to-mediawiki.git`

`cd phriction-to-mediawiki/config`

`cp config-db.php.example config-db.php`


Edit `config-db.php` and enter the database connection parameters for Phriction.



## **Examples**

Export all Phriction wiki pages to export.xml file:

`php phriction-to-mediawiki.php -o export.xml`

Export all Phriction wiki pages with revisions (history) to export.xml file:

`php phriction-to-mediawiki.php -r -o export.xml`

Convert remarkup content from file to wikitext

`php phriction-to-mediawiki.php -f test/phriction-test-content.txt`



## Import into MediaWiki

You can use the *Special:Import* MediaWiki special page to import an XML file.
This may be slow and could potentially timeout for larger XML files.


You can also import with [importDump.php](https://www.mediawiki.org/wiki/Manual:ImportDump.php) maintenance script:

`php run.php importDump.php < export.xml`



## Configuration

In addition to the `config-db.php` file which holds the database connection parameters,
the `config-markup.php` file contains markup settings.

If needed, you can modify certain markup elements. For instance, code blocks uses the 
[SyntaxHighlight](https://www.mediawiki.org/wiki/Extension:SyntaxHighlight) MediaWiki extension,
and highlighted text uses the [Highlight](https://www.mediawiki.org/wiki/Template:Highlight) template.

You can edit `config-markup.php` directly or create a `config-markup.local.php` file with values 
that will override those in `config-markup.php`.



## Disclaimer

This script was written for a one-time import task that I had, so it hasn't been
thoroughly tested. It worked for the specific case I had.
