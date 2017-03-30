<?php

require_once '../../core/components/emailresource/model/emailresource/fullurls.class.php';
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
        $obtained = FullUrls::fullUrls($base, $initial);
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
    $test = FullUrls::fullUrls($base, $full[$i]);
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


