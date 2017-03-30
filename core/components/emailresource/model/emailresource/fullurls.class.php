<?php

/**
 * Class to convert all non-full URLs in a block
 * of text to full, absolute URLs.
 * Created by PhpStorm.
 * User: BobRay
 * Date: 3/30/2017
 * Time: 3:21 PM
 */

 /* Note:s
  *  URLs with no protocol (//someplace.com) are not converted
  */

/**
 * Class FullUrls
 */
class FullUrls {

    /**
     * FullUrls constructor.
     */
    function __construct() {

    }



    /**
     *  Find all tags containing URLs and convert the URLs
     *  to full, absolute URLs,
     *
     * @param $base string - Base for URLs, e.g., 'http://yoursite.com'
     * @param $html string - HTML text to be converted.
     * @param $defaultProtocol string - Protocol to use for external URLs with no protocol, e.g. 'http'
     * @param $debug bool - echo debugging info.
     *
     * @return string - Original text with URLs converted.
     */
    public static function fullUrls($base, $html, $defaultProtocol = 'http', $debug = false) {

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
        foreach ($tags as $tag) {
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

            $newUrl = self::convert($base, $originalUrl, $protocol, $domain, $defaultProtocol, $debug);

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

    /**
     *  Convert a relative URL to an absolute URL
     *
     * @param $base string - Full local site URL, e.g. 'http://yoursite.com' (with or without trailing slash)
     * @param $url string - URL to be converted.
     * @param $protocol string - Full protocol for local links, e.g., 'https://'
     * @param $domain string - Domain part of URL, e.g., yoursite.com
     * @param $defaultProtocol string - Protocol to use for external URLs with no protocol, e.g. 'http'
     * @param $debug bool - echo debugging info.
     *
     * @return string - Converted URL
     */
    public static function convert($base, $url, $protocol, $domain, $defaultProtocol = 'http', $debug = false) {

        if ($debug) {
            echo "\nReceived:" . $url;
        }

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

        /* $urls beyond this point have no protocol */

        $url = ltrim($url, '/');
        $ourDomain = strpos($url, $domain) !== false;
        $hasAnyDomain = preg_match('@^[^\/]*\..+\/@', $url);

/*      Future - deal with www prefix  */

        /* $urlHasWww = stripos($url, 'www.') !== false;
        $baseHasWww = stripos($base, 'www.') !== false;

        if (!$UrlHasWww && $baseHasWww) {

        } elseif ($urlHasWww and !$baseHasWww) {

        }
        */

        /* contains our domain name but no protocol*/
        if ($ourDomain) {
            $url = $protocol . $url;
        } elseif (!$hasAnyDomain) {
            /* No domain - must be ours */
            $url = $base . '/' . $url;
        } else {
            /* foreign domain */
            $url = $defaultProtocol . '://' . $url;
        }

        return $url;
    }

}
