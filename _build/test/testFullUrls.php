<?php
/**
 * Created by PhpStorm.
 * User: BobRay
 * Date: 3/28/2017
 * Time: 3:43 PM
 */

 /*
 * Variables
 * ---------
 * @var $modx modX
 *
 *
 * @package emailResource
 **/


function fullUrls($base, $html) {
    /* extract domain name from $base */
    $splitBase = explode('//', $base);
    $domain = $splitBase[1];
    $domain = rtrim($domain, '/ ');

    /* remove space around = sign */
    //$html = preg_replace('@(href|src)\s*=\s*@', '\1=', $html);
    $html = preg_replace('@(?<=href|src)\s*=\s*@', '=', $html);

    /* fix google link weirdness */
    $html = str_ireplace('google.com/undefined', 'google.com', $html);

    /* add http to naked domain links so they'll be ignored later */
    $html = str_ireplace('a href="' . $domain, 'a href="http://' . $domain, $html);

    /* standardize orthography of domain name */
    $html = str_ireplace($domain, $domain, $html);

    /* Correct base URL, if necessary */
    $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

    /* handle root-relative URLs */
    $html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="' . $server . '\3"', $html);

    /* handle base-relative URLs */
    $html = preg_replace('@\<([^>]*) (href|src)="(?!http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

    return $html;
}


$basePlaceholder = '~~~';

$bases = array(
    'https://www.domain.com/',
    'http://domain.com/',
    'https://domain.com/',
    'http://www.domain.com/',
);


$cases = array(
    // Case 1
    array(
        'initial' => '<a href="page1.html">page1.html</a>',
        'expected' => '<a href="' . $basePlaceholder . 'page1.html">page1.html</a>'
    ),

    // Case 2
    array(
        'initial' => '<a href="section1/page1.html">section1/page1.html</a>',
        'expected' => '<a href="' . $basePlaceholder . 'section1/page1.html">section1/page1.html</a>'
    ),

    // Case 3
    array(
        'initial' => '<img src="folder1/img1.jpg">',
        'expected' => '<img src="' . $basePlaceholder . 'folder1/img1.jpg">'
    ),

    // Case 4
    array(
        'initial' => '<a href="/section1/page1.html">/section1/page1.html</a>',
        'expected' => '<a href="' . $basePlaceholder . 'section1/page1.html">/section1/page1.html</a>',
    ),

    // Case 5
    array(
        'initial' => '<a href="/section1/page1.html#jumpto">/section1/page1.html#jumpto</a>',
        'expected' => '<a href="' . $basePlaceholder . 'section1/page1.html#jumpto">/section1/page1.html#jumpto</a>'
    ),

    // Case 6
    array(
        'initial' => '<a href="http://www.externaldomain.com/page1.html">http://www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="http://www.externaldomain.com/page1.html">http://www.externaldomain.com/page1.html</a>'
    ),

    // Case 7
    array(
        'initial' => '<a href="www.externaldomain.com/page1.html">www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="' . $basePlaceholder . 'www.externaldomain.com/page1.html">www.externaldomain.com/page1.html</a>', //???
    ),

    // Case 8
    array(
        'initial' => '<a href="www.domain.com/page1.html">www.domain.com/page1.html</a>',
        'expected' => '<a href="' . $basePlaceholder . 'www.domain.com/page1.html">www.domain.com/page1.html</a>'
    ),

    // Case 9
    array(
        'initial' => '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>',
    ),

    // Case 10
    array (
        'initial' => '<a href="#jumpto">#jumpto</a>',
        'expected' => '<a href="#jumpto">#jumpto</a>'
    ),

    // Case 11
    array (
        'initial' => '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="//www.externaldomain.com/page1.html">//www.externaldomain.com/page1.html</a>'
    ),





);
$i = 1;
foreach ($cases as $case) {
    echo "\n\n[Case " . $i++ . "]";
    foreach ($bases as $base) {
        echo "\n\n**************************************************************";
        $initial = $case['initial'];
        $expected = str_replace($basePlaceholder, $base, $case['expected']);
        $obtained = fullUrls($base, $initial);
        echo "\nInitial: " . $initial;
        echo "\nExpected:" . $expected;
        echo "\nObtained:" . $obtained;
        echo ($expected === $obtained)? ' -- pass' : ' -- fail';
    }
}

exit;


