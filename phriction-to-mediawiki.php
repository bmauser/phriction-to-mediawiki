<?php

/**
 * CLI script for exporting Phriction pages to XML which can be imported into MediaWiki.
 *
 * Usage examples:
 *  php phriction-to-mediawiki.php -o result.xml
 *  php phriction-to-mediawiki.php -o result.xml -r
 *  php phriction-to-mediawiki.php -f test/phriction-test-content.txt
 */

require_once __DIR__ . '/includes/Helpers.php';
require_once __DIR__ . '/includes/PhrictionToMediawiki.php';


try {

    $phriction_to_mediawiki = new PhrictionToMediawiki();
    $options = getopt("f:o:rh");
    $nl = PHP_EOL;

    if(isset($options['h'])) {
        echo "options:{$nl} -o   Output file path{$nl} -r   Include all revisions{$nl} -f   Filename with remarkup to convert{$nl}";
        echo "{$nl}example:{$nl} php phriction-to-mediawiki.php -o export.xml{$nl}";
        exit(0);
    }

    if(isset($options['f'])) {
        $output = $phriction_to_mediawiki->convertFile($options['f']);
    }
    else{
        $output = $phriction_to_mediawiki->getMediawikiImportXML(array_key_exists('r', $options));
    }


    if(isset($options['o'])) {
        file_put_contents($options['o'], $output);
    }
    else{
        echo $output;
    }

}
catch (Exception $e) {
    print $e->getMessage() . "\n";
    exit(1);
}
