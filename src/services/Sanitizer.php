<?php
/**
 * @author RYU Chua <ryu@alpstein.my>
 * @link https://www.alpstein.my
 * @copyright Copyright (c) Alpstein Software
 */

namespace alpstein\services;

use alpstein\yii\base\BaseObject;
use yii\helpers\Html;

/**
 * Class Sanitizer
 * @package alpstein\services
 */
class Sanitizer extends BaseObject
{
    /**
     * Character set
     *
     * Will be overridden by the constructor.
     * @var string
     */
    public $charset = 'UTF-8';

    /**
     * List of never allowed strings
     *
     * @var array
     */
    public $neverAllowedStringList = [
        'document.cookie' => '[removed]',
        'document.write' => '[removed]',
        '.parentNode' => '[removed]',
        '.innerHTML' => '[removed]',
        '-moz-binding' => '[removed]',
        'window.location' => '[removed]',
        '<!--' => '&lt;!--',
        '-->' => '--&gt;',
        '<![CDATA[' => '&lt;![CDATA[',
        '<comment>' => '&lt;comment&gt;',
    ];
    public $neverAllowedRegexList = [
        'javascript\s*:',
        '(document|(document\.)?window)\.(location|on\w*)',
        'expression\s*(\(|&\#40;)', // CSS and IE
        'vbscript\s*:', // IE, surprise!
        'wscript\s*:', // IE
        'jscript\s*:', // IE
        'vbs\s*:', // IE
        'Redirect\s+30\d',
        "([\"'])?data\s*:[^\\1]*?base64[^\\1]*?,[^\\1]*?\\1?",
    ];
    /**
     * @var array
     */
    public $filenameBadCharacterList = [
        '../', '<!--', '-->', '<', '>',
        "'", '"', '&', '$', '#',
        '{', '}', '[', ']', '=',
        ';', '?', '%20', '%22',
        '%3c',// <
        '%253c',// <
        '%3e',// >
        '%0e',// >
        '%28',// (
        '%29',// )
        '%2528',// (
        '%26',// &
        '%24',// $
        '%3f',// ?
        '%3b',// ;
        '%3d'// =
    ];

    public $htmlPurifierOptions = [
//        'Attr.AllowedRel' => ['noindex', 'nofollow'],
//        'Attr.DefaultImageAlt' => null,
//        'Core.ColorKeywords' => [
//            'maroon' => '#800000', 'red' => '#FF0000', 'orange' => '#FFA500', 'yellow' => '#FFFF00',
//            'olive' => '#808000', 'purple' => '#800080', 'fuchsia' => '#FF00FF', 'white' => '#FFFFFF',
//            'lime' => '#00FF00', 'green' => '#008000', 'navy' => '#000080', 'blue' => '#0000FF',
//            'aqua' => '#00FFFF', 'teal' => '#008080', 'black' => '#000000', 'silver' => '#C0C0C0', 'gray' => '#808080',
//        ],
//        //'Core.Encoding' => Yii::$app->charset,
//        'Core.EscapeInvalidTags' => false,
//        'HTML.AllowedElements' => [
//            'a','b','em','small','strong','del','q','img','span','ul','ol','li','h1','h2','h3','h4','h5','h6'
//        ],
//        'HTML.AllowedAttributes' => [
//            'href','rel','target','src', 'style',
//        ],
        'HTML.Doctype' =>  'XHTML 1.0 Transitional',
        'URI.AllowedSchemes' => [
            'http'      => true,
            'https'     => true,
            'mailto'    => true,
            'ftp'       => true,
            'nntp'      => true,
            'news'      => true,
        ],
        'URI.Base' => null,
    ];

    /**
     * @var \HtmlPurifier
     */
    protected $htmlPurifier;

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be prevented. This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     * It's not something that should be used for general
     * runtime processing.
     *
     * @link http://channel.bitflux.ch/wiki/XSS_Prevention
     * Based in part on some code and ideas from Bitflux.
     *
     * @link http://ha.ckers.org/xss.html
     * To help develop this script I used this great list of
     * vulnerabilities along with a few other hacks I've
     * harvested from examining vulnerabilities in other programs.
     *
     * @param string|string[] $value Input data
     * @param bool $isImage Whether the input is an image
     * @return string
     */
    public function xssClean($value, $isImage = false)
    {
        if (is_array($value)) {
            foreach ($value as $key => $string) {
                $value[$key] = $this->xssClean($string);
            }
            return $value;
        }

        $value = $this->removeInvisibleCharacters($value);

        /*
         * URL Decode
         * Just in case stuff like this is submitted: <a href="http://%77%77%77%2E%67%6F%6F%67%6C%65%2E%63%6F%6D">Google</a>
         * Note: Use rawurldecode() so it does not remove plus signs
         */
        do {
            $value = rawurldecode($value);
        } while (preg_match('/%[0-9a-f]{2,}/i', $value));


        $value = $this->convertToAscii($value);

        /*
         * Convert all tabs to spaces
         *
         * This prevents strings like this: ja	vascript
         * NOTE: we deal with spaces between characters later.
         * NOTE: preg_replace was found to be amazingly slow here on
         * large blocks of data, so we use str_replace.
         */
        $value = str_replace("\t", ' ', $value);

        // Capture converted string for later comparison
        $convertedValue = $value;
        $value = $this->cleanNeverAllowed($value);

        /*
         * Makes PHP tags safe
         *
         * Note: XML tags are inadvertently replaced too:
         *
         * <?xml
         *
         * But it doesn't seem to pose a problem.
         */
        if ($isImage === true) {
            // Images have a tendency to have the PHP short opening and
            // closing tags every so often so we skip those and only
            // do the long opening tags.
            $value = preg_replace('/<\?(php)/i', '&lt;?\\1', $value);
        } else {
            $value = str_replace(['<?', '?' . '>'], ['&lt;?', '?&gt;'], $value);
        }

        $value = $this->convertCompactString($value);
        $value = $this->removeJavascriptWithinLinkOrImageTag($value);
        $value = $this->cleanNaughtyHtmlElements($value);
        $value = $this->cleanNaughtyScriptElements($value);

        /*
         * Images are Handled in a Special Way
         * - Essentially, we want to know that after all of the character
         * conversion is done whether any unwanted, likely XSS, code was found.
         * If not, we return TRUE, as the image is clean.
         * However, if the string post-conversion does not matched the
         * string post-removal of XSS, then it fails, as there was unwanted XSS
         * code found and removed/changed during processing.
         */
        if ($isImage === true) {
            return ($value === $convertedValue);
        }

        return $value;
    }

    /**
     * Sanitize Filename
     *
     * @param string $value Input file name
     * @param bool $relativePath Whether to preserve paths
     * @return string
     */
    public function filename($value, $relativePath = false)
    {
        $bad = $this->filenameBadCharacterList;
        if (!$relativePath) {
            $bad[] = './';
            $bad[] = '/';
        }

        $value = $this->removeInvisibleCharacters($value, false);
        do {
            $old = $value;
            $value = str_replace($bad, '', $value);
        } while ($old !== $value);

        return stripslashes($value);
    }

    /**
     * @param string $value
     * @return string
     */
    public function purify($value)
    {
        return $this->getHtmlPurifier()->purify($value);
    }

    /**
     * @param string|array $value
     * @return string|array
     */
    public function stripClean($value)
    {
        return $this->xssClean($this->stripTags($value));
    }

    /**
     * @param string $value
     * @return string
     */
    public function cleanEncode($value)
    {
        return $this->encode($this->xssClean($value));
    }

    /**
     * @param string|string[] $value
     * @param bool $encode
     * @return string|array
     */
    public function stripTags($value, $encode = false)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->stripTags($v, $encode);
            }
            return $value;
        }

        $value = trim(strip_tags($value));

        if ($encode) {
            return $this->encode($value);
        }

        return $value;
    }

    /**
     * @param string|array $value
     * @return string
     */
    public function encode($value)
    {
        if (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->encode($v);
            }
            return $value;
        }

        return Html::encode($value);
    }

    /**
     * CmsInput::getHtmlPurifier()
     *
     * @return
     */

    /**
     * @return \HTMLPurifier|mixed
     */
    protected function getHtmlPurifier()
    {
        if (isset($this->htmlPurifier)) {
            return $this->htmlPurifier;
        }

        $config = \HTMLPurifier_Config::createDefault();
        $config->autoFinalize = false;

        $this->htmlPurifier = \HTMLPurifier::instance($config);
        $this->htmlPurifier->config->set('Cache.SerializerPath', \Yii::$app->getRuntimePath());
        $this->htmlPurifier->config->set('Cache.SerializerPermissions', 0775);
        foreach ($this->htmlPurifierOptions as $key => $value) {
            $this->htmlPurifier->config->set($key, $value);
        }

        $def = $this->htmlPurifier->config->getHTMLDefinition(true);
        $def->addAttribute('iframe', 'allowfullscreen', 'Enum#allowfullscreen');

        return $this->htmlPurifier;
    }

    /**
     * Sanitize naughty scripting elements
     *
     * Similar to above, only instead of looking for
     * tags it looks for PHP and JavaScript commands
     * that are disallowed. Rather than removing the
     * code, it simply converts the parenthesis to entities
     * rendering the code un-executable.
     *
     * For example: eval('some code')
     * Becomes: eval&#40;'some code'&#41;
     *
     * @param string $value
     * @return string
     */
    protected function cleanNaughtyScriptElements($value)
    {
        $value = preg_replace(
            '#(alert|prompt|confirm|cmd|passthru|eval|exec|expression|system|fopen|fsockopen|file|file_get_contents|readfile|unlink)(\s*)\((.*?)\)#si',
            '\\1\\2&#40;\\3&#41;',
            $value
        );
        return $this->cleanNeverAllowed($value);
    }

    /**
     * Sanitize naughty HTML elements
     *
     * If a tag containing any of the words in the list
     * below is found, the tag gets converted to entities.
     *
     * So this: <blink>
     * Becomes: &lt;blink&gt;
     * @param string $value
     * @return string
     */
    protected function cleanNaughtyHtmlElements($value)
    {
        $pattern = '#'
            . '<((?<slash>/*\s*)(?<tagName>[a-z0-9]+)(?=[^a-z0-9]|$)' // tag start and name, followed by a non-tag character
            . '[^\s\042\047a-z0-9>/=]*' // a valid attribute character immediately after the tag would count as a separator
            // optional attributes
            . '(?<attributes>(?:[\s\042\047/=]*' // non-attribute characters, excluding > (tag close) for obvious reasons
            . '[^\s\042\047>/=]+' // attribute characters
            // optional attribute-value
            . '(?:\s*=' // attribute-value separator
            . '(?:[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*))' // single, double or non-quoted value
            . ')?' // end optional attribute-value group
            . ')*)' // end optional attributes group
            . '[^>]*)(?<closeTag>\>)?#isS';

        do {
            $oldString = $value;
            $value = preg_replace_callback($pattern, [$this, 'sanitizeNaughtyHtml'], $value);
        } while ($oldString !== $value);

        return $value;
    }

    /**
     * Remove disallowed Javascript in links or img tags
     * We used to do some version comparisons and use of stripos(),
     * but it is dog slow compared to these simplified non-capturing
     * preg_match(), especially if the pattern exists in the string
     *
     * Note: It was reported that not only space characters, but all in
     * the following pattern can be parsed as separators between a tag name
     * and its attributes: [\d\s"\'`;,\/\=\(\x00\x0B\x09\x0C]
     * ... however, remove_invisible_characters() above already strips the
     * hex-encoded ones, so we'll skip them below.
     * @param string $value
     * @return string
     */
    protected function removeJavascriptWithinLinkOrImageTag($value)
    {
        do {
            $original = $value;
            if (preg_match('/<a/i', $value)) {
                $value = preg_replace_callback('#<a[^a-z0-9>]+([^>]*?)(?:>|$)#si', [$this, 'removeLinkJs'], $value);
            }
            if (preg_match('/<img/i', $value)) {
                $value = preg_replace_callback('#<img[^a-z0-9]+([^>]*?)(?:\s?/?>|$)#si', [$this, 'removeImgJs'], $value);
            }
            if (preg_match('/script|xss/i', $value)) {
                $value = preg_replace('#</*(?:script|xss).*?>#si', '[removed]', $value);
            }
        } while ($original !== $value);

        return $value;
    }

    /**
     * Compact any exploded words
     *
     * This corrects words like:  j a v a s c r i p t
     * These words are compacted back to their correct state.
     * @param string $value
     * @return string
     */
    protected function convertCompactString($value)
    {
        $words = [
            'javascript', 'expression', 'vbscript', 'jscript', 'wscript',
            'vbs', 'script', 'base64', 'applet', 'alert', 'document',
            'write', 'cookie', 'window', 'confirm', 'prompt', 'eval',
        ];
        foreach ($words as $word) {
            $word = implode('\s*', str_split($word)) . '\s*';
            // We only want to do this when it is followed by a non-word character
            // That way valid stuff like "dealer to" does not become "dealerto"
            $value = preg_replace_callback('#(' . substr($word, 0, -3) . ')(\W)#is', [$this, 'compactExplodedWords'], $value);
        }
        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function convertToAscii($value)
    {
        $value = preg_replace_callback("/[^a-z0-9>]+[a-z0-9]+=([\'\"]).*?\\1/si", [$this, 'convertAttribute'], $value);
        $value = preg_replace_callback('/<\w+.*/si', [$this, 'decodeEntity'], $value);
        return $this->removeInvisibleCharacters($value);
    }

    /**
     * Remove Invisible Characters
     *
     * This prevents sandwiching null characters
     * between ascii characters, like Java\0script.
     *
     * @param string $value
     * @param bool $urlEncoded
     * @return string
     */
    protected function removeInvisibleCharacters($value, $urlEncoded = true)
    {
        $nonDisplayable = [];
        if ($urlEncoded) {
            $nonDisplayable[] = '/%0[0-8bcef]/';// url encoded 00-08, 11, 12, 14, 15
            $nonDisplayable[] = '/%1[0-9a-f]/';// url encoded 16-31
        }
        $nonDisplayable[] = '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S';// 00-08, 11, 12, 14-31, 127

        do {
            $value = preg_replace($nonDisplayable, '', $value, -1, $count);
        } while ($count);

        return $value;
    }

    /**
     * Sanitize Naughty HTML
     *
     * Callback method for xss_clean() to remove naughty HTML elements.
     *
     * @param array $matches
     * @return string
     */
    protected function sanitizeNaughtyHtml($matches)
    {
        static $naughtyTags = [
            'alert', 'prompt', 'confirm', 'applet', 'audio', 'basefont', 'base', 'behavior', 'bgsound',
            'blink', 'body', 'embed', 'expression', 'form', 'frameset', 'frame', 'head', 'html', 'ilayer',
            'iframe', 'input', 'button', 'select', 'isindex', 'layer', 'link', 'meta', 'keygen', 'object',
            'plaintext', 'style', 'script', 'textarea', 'title', 'math', 'video', 'svg', 'xml', 'xss'
        ];

        static $evilAttributes = [
            'on\w+', 'style', 'xmlns', 'formaction', 'form', 'xlink:href', 'FSCommand', 'seekSegmentTime'
        ];

        // First, escape unclosed tags
        if (empty($matches['closeTag'])) {
            return '&lt;' . $matches[1];
        } elseif (in_array(strtolower($matches['tagName']), $naughtyTags, true)) { // Is the element that we caught naughty? If so, escape it
            return '&lt;' . $matches[1] . '&gt;';
        } elseif (isset($matches['attributes'])) { // For other tags, see if their attributes are "evil" and strip those
            // We'll store the already fitlered attributes here
            $attributes = [];
            // Attribute-catching pattern
            $attributesPattern = '#'
                . '(?<name>[^\s\042\047>/=]+)' // attribute characters
                // optional attribute-value
                . '(?:\s*=(?<value>[^\s\042\047=><`]+|\s*\042[^\042]*\042|\s*\047[^\047]*\047|\s*(?U:[^\s\042\047=><`]*)))' // attribute-value separator
                . '#i';
            // Blacklist pattern for evil attribute names
            $is_evil_pattern = '#^(' . implode('|', $evilAttributes) . ')$#i';
            // Each iteration filters a single attribute
            do {
                // Strip any non-alpha characters that may preceed an attribute.
                // Browsers often parse these incorrectly and that has been a
                // of numerous XSS issues we've had.
                $matches['attributes'] = preg_replace('#^[^a-z]+#i', '', $matches['attributes']);
                if (!preg_match($attributesPattern, $matches['attributes'], $attribute, PREG_OFFSET_CAPTURE)) {
                    // No (valid) attribute found? Discard everything else inside the tag
                    break;
                }
                // Is it indeed an "evil" attribute? Or does it have an equals sign, but no value and not quoted? Strip that too!
                if (preg_match($is_evil_pattern, $attribute['name'][0]) or (trim($attribute['value'][0]) === '')) {
                    $attributes[] = 'xss=removed';
                } else {
                    $attributes[] = $attribute[0][0];
                }
                $matches['attributes'] = substr($matches['attributes'], $attribute[0][1] + strlen($attribute[0][0]));
            } while ($matches['attributes'] !== '');
            $attributes = empty($attributes) ? '' : ' ' . implode(' ', $attributes);
            return '<' . $matches['slash'] . $matches['tagName'] . $attributes . '>';
        }
        return $matches[0];
    }

    /**
     * JS Link Removal
     *
     * Callback method for xss_clean() to sanitize links.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on link-heavy strings.
     *
     * @param array $match
     * @return string
     */
    protected function removeLinkJs($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#href=.*?(?:(?:alert|prompt|confirm)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|data\s*:)#si',
                '',
                $this->filterAttributes($match[1])
            ),
            $match[0]
        );
    }

    /**
     * JS Image Removal
     *
     * Callback method for xss_clean() to sanitize image tags.
     *
     * This limits the PCRE backtracks, making it more performance friendly
     * and prevents PREG_BACKTRACK_LIMIT_ERROR from being triggered in
     * PHP 5.2+ on image tag heavy strings.
     *
     * @param array $match
     * @return string
     */
    protected function removeImgJs($match)
    {
        return str_replace(
            $match[1],
            preg_replace(
                '#src=.*?(?:(?:alert|prompt|confirm|eval)(?:\(|&\#40;)|javascript:|livescript:|mocha:|charset=|window\.|document\.|\.cookie|<script|<xss|base64\s*,)#si',
                '',
                $this->filterAttributes($match[1])
            ),
            $match[0]
        );
    }

    /**
     * Filter Attributes
     *
     * Filters tag attributes for consistency and safety.
     *
     * @param string $value
     * @return string
     */
    protected function filterAttributes($value)
    {
        $out = '';
        if (preg_match_all('#\s*[a-z\-]+\s*=\s*(\042|\047)([^\\1]*?)\\1#is', $value, $matches)) {
            foreach ($matches[0] as $match) {
                $out .= preg_replace('#/\*.*?\*/#s', '', $match);
            }
        }
        return $out;
    }

    /**
     * Compact Exploded Words
     *
     * Callback method for xss_clean() to remove whitespace from
     * things like 'j a v a s c r i p t'.
     *
     * @param array $matches
     * @return string
     */
    protected function compactExplodedWords($matches)
    {
        return preg_replace('/\s+/s', '', $matches[1]) . $matches[2];
    }

    /**
     * Attribute Conversion
     *
     * Used as a callback for XSS Clean
     * @param array $match
     * @return string
     */
    protected function convertAttribute($match)
    {
        return str_replace(['>', '<', '\\'], ['&gt;', '&lt;', '\\\\'], $match[0]);
    }

    /**
     * HTML Entity Decode Callback
     *
     * Used as a callback for XSS Clean
     * @param array $match
     * @return string
     */
    protected function decodeEntity($match)
    {
        return $this->entityDecode($match[0], strtoupper(\Yii::$app->charset));
    }

    /**
     * HTML Entities Decode
     *
     * A replacement for html_entity_decode()
     *
     * The reason we are not using html_entity_decode() by itself is because
     * while it is not technically correct to leave out the semicolon
     * at the end of an entity most browsers will still interpret the entity
     * correctly. html_entity_decode() does not convert entities without
     * semicolons, so we are left with our own little solution here. Bummer.
     *
     * @param string $value
     * @param string $charset
     * @return string
     */
    protected function entityDecode($value, $charset = 'UTF-8')
    {
        if (strpos($value, '&') === false) {
            return $value;
        }

        static $_entities;
        $flag = ENT_COMPAT | ENT_HTML5;
        do {
            $strCompare = $value;
            // Decode standard entities, avoiding false positives
            if (preg_match_all('/&[a-z]{2,}(?![a-z;])/i', $value, $matches)) {
                if (!isset($_entities)) {
                    $_entities = array_map('strtolower', get_html_translation_table(HTML_ENTITIES, $flag));
                }

                $replace = [];
                $matches = array_unique(array_map('strtolower', $matches[0]));
                foreach ($matches as &$match) {
                    if (($char = array_search($match . ';', $_entities, true)) !== false) {
                        $replace[$match] = $char;
                    }
                }
                $value = str_ireplace(array_keys($replace), array_values($replace), $value);
            }

            $value = preg_replace('/(&#(?:x0*[0-9a-f]{2,5}(?![0-9a-f;])|(?:0*\d{2,4}(?![0-9;]))))/iS', '$1;', $value);
            // Decode numeric & UTF16 two byte entities
            $value = html_entity_decode($value, $flag, $charset);
        } while ($strCompare !== $value);

        return $value;
    }

    /**
     * the Old EntityEncode Function
     * @param string $value
     * @param string $charset
     * @return string
     */
    protected function exEntityEncode($value, $charset = null)
    {
        if (stristr($value, '&') === false) {
            return $value;
        };

        if ($charset === null) {
            $charset = \Yii::$app->charset;
        }

        // The reason we are not using html_entity_decode() by itself is because
        // while it is not technically correct to leave out the semicolon
        // at the end of an entity most browsers will still interpret the entity
        // correctly.  html_entity_decode() does not convert entities without
        // semicolons, so we are left with our own little solution here. Bummer.

        if (function_exists('html_entity_decode') && (strtolower($charset) != 'utf-8')) {
            $value = html_entity_decode($value, ENT_COMPAT, $charset);
            $value = preg_replace('~&#x(0*[0-9a-f]{2,5})~ei', 'chr(hexdec("\\1"))', $value);
            return preg_replace('~&#([0-9]{2,4})~e', 'chr(\\1)', $value);
        }

        // Numeric Entities
        $value = preg_replace('~&#x(0*[0-9a-f]{2,5});{0,1}~ei', 'chr(hexdec("\\1"))', $value);
        $value = preg_replace('~&#([0-9]{2,4});{0,1}~e', 'chr(\\1)', $value);

        // Literal Entities - Slightly slow so we do another check
        if (stristr($value, '&') === false) {
            $value = strtr($value, array_flip(get_html_translation_table(HTML_ENTITIES)));
        }

        return $value;
    }

    /**
     * @param string $value
     * @return string
     */
    protected function cleanNeverAllowed($value)
    {
        $value = str_replace(array_keys($this->neverAllowedStringList), $this->neverAllowedStringList, $value);
        foreach ($this->neverAllowedRegexList as $regex) {
            $value = preg_replace('#' . $regex . '#is', '[removed]', $value);
        }
        return $value;
    }
}
