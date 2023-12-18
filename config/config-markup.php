<?php

/**
 * Configuration for converting tags from Remarkup to WikiText.
 * The order of elements in this array is important.
 */
$config['tags'] = array(


    'cleanup_end_whitespaces' => // cleans whitespaces at the end of lines
    [
        'ph' => ['sarch' => '[ \t]*$'],
        'mw' => ['replace' => ''],
    ],

    '===== h6 =====' =>
    [
        'ph' => ['start' => '^===== ', 'end' => '=====$'],
        'mw' => ['start' => '====== ', 'end' => "======"],
    ],

    '==== h5 ====' =>
    [
        'ph' => ['start' => '^==== ', 'end' => '====$'],
        'mw' => ['start' => '===== ', 'end' => '====='],
    ],

    '=== h4 ===' =>
    [
        'ph' => ['start' => '^=== ', 'end' => '===$'],
        'mw' => ['start' => '==== ', 'end' => '===='],
    ],

    '== h3 ==' =>
    [
        'ph' => ['start' => '^== ', 'end' => '==$'],
        'mw' => ['start' => '=== ', 'end' => '==='],
    ],

    '= h2 =' =>
    [
        'ph' => ['start' => '^= ', 'end' => '=$'],
        'mw' => ['start' => '== ', 'end' => '=='],
    ],


    '===== h6' =>
    [
        'ph' => ['start' => '^===== ', 'end' => '(?<!=)$'],
        'mw' => ['start' => '====== ', 'end' => ' ======'],
    ],

    '==== h5' =>
    [
        'ph' => ['start' => '^==== ', 'end' => '(?<!=)$'],
        'mw' => ['start' => '===== ', 'end' => ' ====='],
    ],

    '=== h4' =>
    [
        'ph' => ['start' => '^=== ', 'end' => '(?<!=)$'],
        'mw' => ['start' => '==== ', 'end' => ' ===='],
    ],

    '== h3' =>
    [
        'ph' => ['start' => '^== ', 'end' => '(?<!=)$'],
        'mw' => ['start' => '=== ', 'end' => ' ==='],
    ],

    '= h2' =>
    [
        'ph' => ['start' => '^= ', 'end' => '(?<!=)$'],
        'mw' => ['start' => '== ', 'end' => ' =='],
    ],

    '```code block```' =>
    [
        'ph' => ['start' => '```', 'end' => '```'],
        'mw' => ['start' => '<syntaxhighlight lang="text" line>', 'end' => '</syntaxhighlight>'], // use SyntaxHighlight extension
        'modifiers' => 'muis',
        'keep_block_content' => true,
    ],

    '**bold**' =>
    [
        'ph' => ['start' => '\*\*', 'end' => '\*\*'],
        'mw' => ['start' => "'''", 'end' => "'''"],
    ],

    '//italic//' =>
    [
        'ph' => ['start' => '//', 'end' => '//'],
        'mw' => ['start' => "''", 'end' => "''"],
    ],

    '`monospaced`' =>
    [
        'ph' => ['start' => '`', 'end' => '`'],
        'mw' => ['start' => '<code>', 'end' => '</code>'],
    ],

    '##monospaced##' =>
    [
        'ph' => ['start' => '\#\#', 'end' => '\#\#'],
        'mw' => ['start' => '<code>', 'end' => '</code>'],
    ],

    '~~deleted~~' =>
    [
        'ph' => ['start' => '~~', 'end' => '~~'],
        'mw' => ['start' => '<del>', 'end' => '</del>'],
    ],

    '__underlined__' =>
    [
        'ph' => ['start' => '__', 'end' => '__'],
        'mw' => ['start' => '<u>', 'end' => '</u>'],
    ],

    '!!highlighted!!' =>
    [
        'ph' => ['start' => '!!', 'end' => '!!'],
        'mw' => ['start' => '{{highlight|', 'end' => '}}'], // use template https://www.mediawiki.org/wiki/Template:Highlight
    ],

    '---Horizontal rule' =>
    [
        'ph' => ['sarch' => '^\---$'],
        'mw' => ['replace' => '----'],
    ],

    '- Bullet list' =>
    [
        'ph' => ['sarch' => '^\- '],
        'mw' => ['replace' => '* '],
    ],

    '%%%Literal Blocks%%%' =>
    [
        'ph' => ['start' => '%%%', 'end' => '%%%'],
        'mw' => ['start' => '<nowiki>', 'end' => '</nowiki>'],
        'modifiers' => 'muis',
        'keep_block_content' => true,
    ],

);


/**
 * Settings for tables
 */
$config['tables'] = array(
    'mw' => [
        'first_line' => '{| class="wikitable" style="margin:auto"',
    ]
);
