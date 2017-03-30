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

function convert($base, $url, $protocol, $domain) {


     echo "\nReceived:" . $url;

     $base = rtrim($base, '/');
     if (preg_match('@https?@', $url)) {
         return $url;
     }

     if (preg_match('@^[\s]*//@', $url)) {
         return $url;
     }

     if (preg_match('@^[\s]*#@', $url)) {
         return $url;
     }

     /* $url has no protocol beyond this point */


    $url = ltrim($url, '/');
    $ourDomain = strpos($url, $domain) !== false;
    $hasAnyDomain  = preg_match('@^[^\/]*\..+\/@', $url);
    $www = 'www.';
    $UrlHasWww = stripos($url, 'www.') !== false;
    $baseHasWww = stripos($base, 'www.') !== false;

    if (! $UrlHasWww && ! $baseHasWww) {

    }

    /* contains our domain name but no protocol*/
     if ($ourDomain) {
         $url =  $protocol . $url;
     } elseif (!$hasAnyDomain) {

     /* No domain - must be ours */

         $url = $base . '/' . $url;
     } else {
         $url = 'http://' . $url;
     }
     /*if (strpos($url, $domain) === false) {

       $url = $base . '/' . $url;

        // return preg_replace('@(.*?href[\s]*\=[\s]*[\'"])([\/])*(.*)@', '\1' . $base . '/\3',$url);

     }*/

     return $url;
 }

function fullUrls($base, $html) {


    /* Extract domain name and protocol from $base */
    $splitBase = explode('//', $base);
    $protocol = $splitBase[0] . '//';
    $domain = $splitBase[1];
    $domain = rtrim($domain, '/ ');

    echo "\nProtocol: " . $protocol;
    echo "\nBase: " . $base;
    echo "\nDomain: " . $domain;


    /* get array of tags. Collects only the a or img part:
            <a href="somethings">
      or    <img src="something"> */
    $pattern = '@<(?:a|img)[\s]+(?:href|src)[^>]+\>@i';
    preg_match_all($pattern, $html, $matches);
    $tags = $matches[0];

   //  echo print_r($tags, true);
   //  exit;

    if (empty($tags)) {
        return $html;
    }

    // echo "\n\n" . print_r($tags, true) . "\n\n";

    /* $fullTag is only the a part:
            <a href="somethings">
      or    <img src="something"> */
    foreach($tags as $tag) {
        $fullTag = $tag;
        echo "\nFullTag: " . $fullTag;

        /* extract URL from tag */
        $pattern2 = '@(?:href|src)[\s]*=[\s]*[\'\"]([^\'"]+)[\'\"][\s]*>@';
        preg_match($pattern2, $fullTag, $matches);
        $originalUrl = $matches[1];
        echo "\nOriginalUrl: " . $originalUrl;

        $newUrl = convert($base, $originalUrl, $protocol, $domain);

        echo "\nReturned: ", $newUrl;
        if ($originalUrl !== $newUrl) {
            $newTag = str_replace($originalUrl, $newUrl, $fullTag);
            $html = str_replace($fullTag, $newTag, $html);
        }



    }
     return $html;
    /* remove space around = sign */
    $html = preg_replace('@(?<=href|src)\s*=\s*@', '=', $html);

    /* Fix google link weirdness */
    $html = str_ireplace('google.com/undefined', 'google.com', $html);

    /* add base protocol to naked domain links so they'll be ignored later */
    $html = str_ireplace('a href="' . $domain, 'a href="' . $protocol . '//' . $domain, $html);

    /* Standardize orthography of domain name */
    $html = str_ireplace($domain, $domain, $html);

    /* Correct base URL, if necessary */
    $server = preg_replace('@^([^\:]*)://([^/*]*)(/|$).*@', '\1://\2/', $base);

    /* Protect external links with no protocol */

    /* Handle root-relative URLs */
    //$html = preg_replace('@\<([^>]*) (href|src)="/([^"]*)"@i', '<\1 \2="'.$server.'\3"', $html);
    $html = preg_replace('@\<([^>]*) (href|src)="/([^#"]*)"@i', '<\1 \2="' . $server . '\3"', $html);

    /* Handle base-relative URLs */
    //$html = preg_replace('@\<([^>]*) (href|src)="(?!http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="'.$base.'\3"', $html);
    $html = preg_replace('@\<([^>]*) (href|src)="(?!#|http|mailto|sip|tel|callto|sms|ftp|sftp|gtalk|skype)(([^\:"])*|([^"]*:[^/"].*))"@i', '<\1 \2="' . $base . '\3"', $html);

    // preg_match('@([a-zA-Z])(\/\/)([a-zA-Z])@', $html, $matches);

    /* Remove double slashes in path */
    // $html = preg_replace('@([a-zA-Z])(\/\/)([a-zA-Z])@', '\1/\3', $html);

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
        'initial' => '<a href="http://www.external.com/page1.html">http://www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="http://www.external.com/page1.html">http://www.externaldomain.com/page1.html</a>',
    ),

    // Case 7
    array(
        'initial' => '<a href="www.external.com/page1.html">www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="' . 'http://www.external.com/page1.html">www.externaldomain.com/page1.html</a>',
    ),

    // Case 8
    array(
        'initial' => '<a href="www.domain.com/page1.html">www.domain.com/page1.html</a>',
        'expected' => '<a href="' .
            $basePlaceholder . 'page1.html">www.domain.com/page1.html</a>'
    ),

    // Case 9
    array(
        'initial' => '<a href="//www.external.com/page1.html">//www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="//www.external.com/page1.html">//www.externaldomain.com/page1.html</a>',
    ),

    // Case 10
    array (
        'initial' => '<a href="#jumpto">#jumpto</a>',
        'expected' => '<a href="#jumpto">#jumpto</a>'
    ),

    // Case 11
    array (
        'initial' => '<a href="//www.external.com/page1.html">//www.externaldomain.com/page1.html</a>',
        'expected' => '<a href="//www.external.com/page1.html">//www.externaldomain.com/page1.html</a>'
    ),


);
$i = 0;

$lorum = "\n <p> Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit  </p>";
$full = '';
$fullExpected = '';

$partOneFailCount = 0;
$partTwoFailCount = 0;
$partThreeFailCount = 0;

$fullTexts = array();
$fullExpecteds = array();


$i = 0;
$full = array();
$fullExpected = array();
foreach ($cases as $case) {
    if (1 != $i -1) {
      // continue;
    }
    $count = $i + 1;
    echo "\n\n[Case " . $count . "]";

    $j = 0;
    foreach ($bases as $base) {
        $full[$j] .= $lorum;
        $fullExpected[$j] .= $lorum;


        echo "\n\n**************************************************************";
        $initial = $case['initial'];
        $full[$j] .= $initial . $lorum;
        $expected = str_replace($basePlaceholder, $base, $case['expected']);

        /* correct Case 8 expected */
        if ((strpos($initial, '"www.') !== false) && (strpos($expected, '//www.') === false)) {
            $expected = str_replace('://', '://www.', $expected);
        }
        $fullExpected[$j] .= $expected . $lorum;
        $obtained = fullUrls($base, $initial);
        echo "\nInitial: " . $initial;
        echo "\nExpected:" . $expected;
        echo "\nObtained:" . $obtained;
        $pass = ($expected === $obtained) ? ' -- pass' : ' -- fail';
        if ($pass === ' -- fail') {
            $partOneFailCount++;
        }
        echo $pass;
        $j++;
    }
    $i++;
}

// echo "\n\nFULL: " . $full;


$partThreeFailCount = 0;
$i = 0;
foreach ($bases as $base) {

    $test = fullUrls($base, $full[$i]);
    if ($test !== $fullExpected[$i]) {
        $partThreeFailCount++;
    }
    $i++;
}
echo "\n ****************************************** \n";

echo "\nPart One Fail Count: " . $partOneFailCount;
// echo "\n\nEXPECTED: " . $fullExpected;

echo "\nPart Two Fail Count: " . $partTwoFailCount;
/* preg_match_all */
echo "\nPart Three Fail Count: " . $partThreeFailCount;



exit;


