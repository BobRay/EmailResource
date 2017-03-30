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

$debug = false;

function convert($base, $url, $protocol, $domain) {
    global $debug;

     if ($debug) { echo "\nReceived:" . $url; }

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
    global $debug;

    /* Extract domain name and protocol from $base */
    $splitBase = explode('//', $base);
    $protocol = $splitBase[0] . '//';
    $domain = $splitBase[1];
    $domain = rtrim($domain, '/ ');
    if ($debug) {
        echo "\nProtocol: " . $protocol;
        echo "\nBase: " . $base;
        echo "\nDomain: " . $domain;
    }


    /* get array of tags. Collects only the a or img part:
            <a href="somethings">
      or    <img src="something"> */
    $pattern = '@<(?:a|img)[\s]+[^>]*(?:href|src)[^>]+\>@i';
    preg_match_all($pattern, $html, $matches);
    if (isset($matches[0])) {
        $tags = $matches[0];
    } else {
        /* No tags with URLs in source */
        echo "\nNO TAGS FOUND";
        return $html;
    }

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
        if ($debug) {
            echo "\nFullTag: " . $fullTag;
        }

        /* extract URL from tag */
        $pattern2 = '@(?:href|src)[\s]*=[\s]*[\"\']([^\'\"]+).*>@i';

        preg_match($pattern2, $fullTag, $matches);
        if (isset($matches[1])) {
            $originalUrl = $matches[1];
        } else {
            echo "\n NO URL MATCH INSIDE TAG";
            continue;
        }

        if ($debug) {
            echo "\nOriginalUrl: " . $originalUrl;
        }

        $newUrl = convert($base, $originalUrl, $protocol, $domain);

        if ($debug) {
            echo "\nReturned: ", $newUrl;
        }
        if ($originalUrl !== $newUrl) {
            $newTag = str_replace($originalUrl, $newUrl, $fullTag);
            $html = str_replace($fullTag, $newTag, $html);
        }



    }
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
    // Case 12
    array(
        'initial' =>  '<a style="color: Red;" href="/section1/page1.html#jumpto">/section1/page1.html#jumpto</a>',
        'expected' => '<a style="color: Red;" href="' . $basePlaceholder . 'section1/page1.html#jumpto">/section1/page1.html#jumpto</a>'
    ),
    // Case 13
    array(
        'initial' => '<img style="border:none;" src="/section1/page1.jpg">/section1/page1.html#jumpto</img>',
        'expected' => '<img style="border:none;" src="' . $basePlaceholder . 'section1/page1.jpg">/section1/page1.html#jumpto</img>'
    ),
    // Case 14
    array(
        'initial' => '<a style="color: Red;" href="/section1/page1.html#jumpto" class="large">/section1/page1.html#jumpto</a>',
        'expected' => '<a style="color: Red;" href="' . $basePlaceholder . 'section1/page1.html#jumpto" class="large">/section1/page1.html#jumpto</a>'
    ),
    // Case 15
    array(
        'initial' => '<img style="border:none;" src="/section1/page1.jpg" class="large">/section1/page1.html#jumpto</img>',
        'expected' => '<img style="border:none;" src="' . $basePlaceholder . 'section1/page1.jpg" class="large">/section1/page1.html#jumpto</img>'
    ),

);
$i = 0;

$lorum = "\n <p> Neque porro quisquam est qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit  </p>";
$full = '';
$fullExpected = '';

$partOneFailCount = 0;
$partTwoFailCount = 0;
$partThreeFailCount = 0;

$i = 0;
$full = array();
$fullExpected = array();

for ($k = 0; $k < count($bases); $k++) {
    $full[$k] = '';
    $fullExpected[$k] = '';
}
foreach ($cases as $case) {

    if (12 != $i -1) {
     //  continue;
    }

    $count = $i + 1;
    echo "\n\n[Case " . $count . "]";

    $j = 0;
    foreach ($bases as $base) {
        $full[$j] .= $lorum;
        $fullExpected[$j] .= $lorum;


        if ($debug) {
            echo "\n\n**************************************************************";
        }
        $initial = $case['initial'];
        $full[$j] .= $initial . $lorum;
        $expected = str_replace($basePlaceholder, $base, $case['expected']);

        /* correct Case 8 expected */
        if ((strpos($initial, '"www.') !== false) && (strpos($expected, '//www.') === false)) {
            $expected = str_replace('://', '://www.', $expected);
        }
        $fullExpected[$j] .= $expected . $lorum;
        $obtained = fullUrls($base, $initial);
        if ($debug) {
            echo "\nInitial: " . $initial;
            echo "\nExpected:" . $expected;
            echo "\nObtained:" . $obtained;
        }
        $pass = ($expected === $obtained) ? ' -- pass' : ' -- fail';
        if ($pass === ' -- fail') {
            $partOneFailCount++;
        }

        echo $pass;

        $j++;
    }
    $i++;
}

// echo "\n\nFULL: " . print_r($full, true);


$partThreeFailCount = 0;
$i = 0;
foreach ($bases as $base) {
    if (empty($full[$i])) {
        echo "\n Full[" . $i . '] is empty';
    }
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


