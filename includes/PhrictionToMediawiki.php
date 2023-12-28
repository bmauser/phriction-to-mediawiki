<?php

/**
 * Class with methods for conversion from remarkup to wikitext.
 */
class PhrictionToMediawiki{


    /**
     * Database connection.
     *
     * @var \PDO
     */
    protected $pdo;


    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->pdo = Helpers::getDbConnection();
        $this->config = Helpers::getConfig();
    }


    /**
     * Returns MediaWiki import XML.
     *
     * @return string
     */
    public function getMediawikiImportXML($include_revisions = false){

        $phriction_data = $this->getPhrictionData($include_revisions);

        $dom = new DOMDocument('1.0', 'UTF-8');

        $mediawiki = $dom->createElement('mediawiki');
        $dom->appendChild($mediawiki);

        $siteinfo = $dom->createElement('siteinfo');
        $siteinfo->appendChild($dom->createTextNode(''));
        $mediawiki->appendChild($siteinfo);

        foreach ($phriction_data as $phriction_page){

            $page = $dom->createElement('page');
            $title = $dom->createElement('title');
            $title->appendChild($dom->createTextNode($phriction_page['revisions'][0]['title']));
            $page->appendChild($title);

            foreach ($phriction_page['revisions'] as $phriction_revision){
                $revision = $dom->createElement('revision');
                $page->appendChild($revision);

                $model = $dom->createElement('model');
                $model->appendChild($dom->createTextNode('wikitext'));
                $revision->appendChild($model);

                $format = $dom->createElement('format');
                $format->appendChild($dom->createTextNode('text/x-wiki'));
                $revision->appendChild($format);

                $timestamp = $dom->createElement('timestamp');
                $timestamp->appendChild($dom->createTextNode($this->xmlDate($phriction_revision['dateModified'])));
                $revision->appendChild($timestamp);

                // @todo
                //$contributor = $dom->createElement('contributor');
                //$username = $dom->createElement('username');
                //$username->appendChild($dom->createTextNode($phriction_revision['MISSING']));
                //$contributor->appendChild($username);
                //$revision->appendChild($contributor);

                $text = $dom->createElement('text');
                $text->appendChild($dom->createTextNode($this->convert($phriction_revision['content'])));
                $revision->appendChild($text);
            }

            $mediawiki->appendChild($page);
        }

        $dom->formatOutput = true;
        return $dom->saveXML();
    }


    /**
     * Returns data from Phriction.
     *
     * @return array
     */
    protected function getPhrictionData($include_revisions = false){

        // all phriction documents
        $all_documents = $this->getAllDocuments();

        // get revisions for each document
        foreach ($all_documents as $content_key => $document){
            // get current content row
            $content_row = $this->getDocumentContent($document['contentPHID']);
            // get all revision till current content
            $all_documents[$content_key]['revisions'] = $this->getDocumentRevisions($document['phid'], $content_row['id'], $include_revisions);
        }

        //return [$all_documents[0]];
        return $all_documents;
    }


    /**
     * Returns date in ISO 8601 format.
     *
     * @param int $timestamp unix timestamp
     * @return string
     */
    function xmlDate($timestamp){
        $format = 'c'; // 2004-02-12T15:19:21+00:00 - ISO 8601 date
        $return = gmdate($format, $timestamp);
        return str_replace('+00:00', 'Z', $return);
    }


    /**
     * Returns documents (rows) from phriction_document table.
     *
     * @return array
     */
    protected function getAllDocuments(){

        $sql = "SELECT * FROM phriction_document WHERE status = 'active'";
        $documents = Helpers::getDbConnection()->query($sql)->fetchAll();

        return $documents;
    }


    /**
     * Returns row form phriction_content table.
     *
     * @param string $content_phid
     * @return array
     */
    protected function getDocumentContent($content_phid){

        $sql = "SELECT * FROM phriction_content WHERE phid = ? ORDER by id DESC";
        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$content_phid]);
        $row = $ps->fetch();

        return $row;
    }


    /**
     * Returns document data by phriction slug.
     *
     * @param string $slug
     * @return array
     */
    public function getDocumentBySlug($slug) {

        $slug = trim($slug);
        $slug = ltrim($slug, '/');
        $slug = rtrim($slug, '/');
        $slug = $slug . '/';

        $sql = "SELECT *, phriction_content.title AS title
                FROM phriction_document 
                JOIN phriction_content ON phriction_document.contentPHID = phriction_content.phid
                WHERE phriction_document.slug = ?";
        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$slug]);
        $document = $ps->fetch();

        return $document;
    }


    /**
     * Returns page revisions without drafts.
     *
     * @param $document_phid
     * @param $current_content_id
     * @param $all_revisions
     * @return array|false
     */
    protected function getDocumentRevisions($document_phid , $current_content_id, $all_revisions = false){

        if($all_revisions)
            $sql = "SELECT * FROM phriction_content WHERE documentPHID = ? AND id <= ?  ORDER by id DESC";
        else
            $sql = "SELECT * FROM phriction_content WHERE documentPHID = ? AND id = ? ORDER by id DESC"; // only last revision

        $ps = Helpers::getDbConnection()->prepare($sql);
        $ps->execute([$document_phid, $current_content_id]);
        $revisions = $ps->fetchAll();

        return $revisions;
    }


    /**
     * Converts remarkup to wikitext.
     *
     * @param string $content phriction content
     * @return string
     */
    protected function convert($content){

        $all_tags = array_values($this->config['tags']);
        $default_modifiers = 'mui'; // default modifiers
        $keep_blocks =[];
        $block_index = 0;

        // convert all new lines to \n
        $content = $this->unixNewLines($content);

        // keep blocks that must stay as is
        foreach ($all_tags as $tags){
            if(isset($tags['keep_block_content'])){

                $block_index ++;

                if(isset($tags['modifiers']))
                    $modifiers = $tags['modifiers'];
                else
                    $modifiers = $default_modifiers;

                if(isset($tags['ph']['start']) && isset($tags['ph']['end'])) {
                    $search = '#' . $tags['ph']['start'] . '(.*?)' . $tags['ph']['end'] . '#' . $modifiers;
                }

                preg_match_all($search, $content, $matches);

                if(isset($matches[1])){
                    $keep_blocks[$block_index] = $matches[1];
                }

                $replace = $tags['ph']['start'] . "INSERT_BLOCK_{$block_index}_PLACEHOLDER" . $tags['ph']['end'];
                $content = preg_replace($search, $replace, $content);
            }
        }

        // convert new lines
        $content = $this->convertNewLines($content);

        // replace all tags
        foreach ($all_tags as $tags){

            if(isset($tags['add_modifier']))
                $modifiers = $tags['modifiers'];
            else
                $modifiers = $default_modifiers;

            if(isset($tags['ph']['start']) && isset($tags['ph']['end'])) {
                $search = '#' . $tags['ph']['start'] . '(.*?)' . $tags['ph']['end'] . '#' . $modifiers;
                $replace = $tags['mw']['start'] . '$1' . $tags['mw']['end'];
            }

            if(isset($tags['ph']['sarch']) && isset($tags['mw']['replace'])) {
                $search = '#' . $tags['ph']['sarch'] . '#' . $modifiers;
                $replace = $tags['mw']['replace'];
            }

            $content = preg_replace($search, $replace, $content);
        }

        // convert links
        $content = $this->convertLinks($content);

        // convert tables
        $content = $this->convertTables($content);

        // return blocks
        if($keep_blocks){
            foreach ($keep_blocks as $block_index => $tag_blocks){
                $placeholder = "INSERT_BLOCK_{$block_index}_PLACEHOLDER";
                foreach ($tag_blocks as $block){;
                    // replace only first occurrence of placeholder
                    $pos = strpos($content, $placeholder);
                    if ($pos !== false) {
                        $content = substr_replace($content, $block, $pos, strlen($placeholder));
                    }
                }
            }
        }

        return $content;
    }


    /**
     * Converts phriction links to wikitext links.
     *
     * @param string $content phriction wiki text
     * @return string
     */
    protected function convertLinks($content){
        $content = preg_replace_callback('/\[\[(.*?)\]\]/u', array($this, 'makeWikitextLinks'), $content);
        return $content;
    }


    /**
     * Helper for convertLinks() method.
     *
     * @param array $matches
     * @return string|null
     */
    protected function makeWikitextLinks($matches=array()) {

        $url = $name = null;

        if(isset($matches[1])) {

            $link_content = $matches[1];

            if(stripos($link_content, '|') !== false){
                $link_content = explode('|', $link_content);
                $url = $link_content[0];
                $name = $link_content[1];
            }
            else{
                $url = $link_content;
            }
        }
        else{
            $return = '';
        }

        $url_info = parse_url($url);

        // external link
        if(isset($url_info['scheme'])) {
            if($name)
                $return = "[$url $name]";
            else
                $return = $url;
        }
        // internal link
        else {
            $document = $this->getDocumentBySlug($url);
            if($document){
                $page_title = $document['title'];
                if($name)
                    $return = "[[$page_title|$name]]";
                else
                    $return = "[[$page_title]]";
            }
            else{
                $return = "[[$url|$name]]"; // document not found
            }
        }

        return $return;
    }


    /**
     * Converts all new lines to \n.
     *
     * @param string $content
     * @return string
     */
    protected function unixNewLines($content){
        return preg_replace('~\R~u', "\n", $content);
    }


    /**
     * Converts newlines.
     *
     * Remarkup uses one new line for a new row, wikitext two.
     *
     * @param string $content remarkup
     * @return string
     */
    protected function convertNewLines($content){

        $lines = explode("\n", $content);
        foreach ($lines as $line_num => $line) {
            // is text line
            if (preg_match('/^([a-z]|[A-Z]|[0-9]|\*\*|!!|\{|@|##|#\{|\/|~~|__|\[|`)/', $lines[$line_num]) or $line === '') {
                $lines[$line_num] = $lines[$line_num] . "\n";
            }
        }
        $content = implode("\n", $lines);

        // keep max 2 new lines
        $content = preg_replace('/(\n){3,}/m', "\n\n", $content);

        return $content;
    }


    /**
     * Converts remarkup tables to wikitext tables.
     *
     * @param string $content Remarkup
     * @return string
     */
    protected function convertTables($content){

        $lines = explode("\n", $content);

        $table_line_index = 0;
        foreach ($lines as $line_num => $line) {

            $header_line = false;

            if(!isset( $lines[$line_num])) // true in case of table header divider row
                continue;

            // is table line
            if (preg_match('/^\|/', $lines[$line_num])) {
                $table_line_index ++;
            }
            else{
                // if last table line
                if($table_line_index) {
                    $lines[$line_num] = '|}' . "\n" . $lines[$line_num]; // close table
                }

                $table_line_index = 0;
                continue;
            }

            // first table line
            if($table_line_index == 1){
                // if second line is table header divider | -----
                if (preg_match('/^\|.*?\-\-\-\-\-/', $lines[$line_num+1])) {
                    // make first line table header
                    $lines[$line_num] = substr_replace($lines[$line_num], '!', 0, 1); // replace first | with !
                    $lines[$line_num] = str_replace('|', '!!', $lines[$line_num]); // replace all | with !!
                    $lines[$line_num] = $this->config['tables']['mw']['first_line'] . "\n" . $lines[$line_num]; // add wikitext table start
                    unset($lines[$line_num+1]); // delete header divider line
                    $header_line = true;
                }
            }

            // data table line
            if(!$header_line) {
                $lines[$line_num] = str_replace('|', '||', $lines[$line_num]); // replace all | with ||
                $lines[$line_num] = substr_replace($lines[$line_num], '|', 0, 2); // replace first || with |
                if($table_line_index == 1)
                    $lines[$line_num] = $this->config['tables']['mw']['first_line'] . "\n" . $lines[$line_num]; // add wikitext table start
                else
                    $lines[$line_num] = '|-' . "\n" . $lines[$line_num]; // add wikitext row divider
            }
        }

        return implode("\n", $lines);
    }


    /**
     * Converts file with remarkup content to wikitext.
     *
     * @param string $file_path
     * @return string
     */
    public function convertFile($file_path){
        $phriction_content = file_get_contents($file_path);
        return $this->convert($phriction_content);
    }

}
