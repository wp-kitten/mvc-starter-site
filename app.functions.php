<?php
/*
 * Global functions
 *
 * Imported functionality from WordPress so we can have wp_kses()
 */

use Kyt\Core\WP_Hook;
use Kyt\Helpers\Event;

if ( !defined( 'APP_DIR' ) ) {
    exit( 'No direct access please.' );
}

$allowedentitynames = array(
    'nbsp', 'iexcl', 'cent', 'pound', 'curren', 'yen',
    'brvbar', 'sect', 'uml', 'copy', 'ordf', 'laquo',
    'not', 'shy', 'reg', 'macr', 'deg', 'plusmn',
    'acute', 'micro', 'para', 'middot', 'cedil', 'ordm',
    'raquo', 'iquest', 'Agrave', 'Aacute', 'Acirc', 'Atilde',
    'Auml', 'Aring', 'AElig', 'Ccedil', 'Egrave', 'Eacute',
    'Ecirc', 'Euml', 'Igrave', 'Iacute', 'Icirc', 'Iuml',
    'ETH', 'Ntilde', 'Ograve', 'Oacute', 'Ocirc', 'Otilde',
    'Ouml', 'times', 'Oslash', 'Ugrave', 'Uacute', 'Ucirc',
    'Uuml', 'Yacute', 'THORN', 'szlig', 'agrave', 'aacute',
    'acirc', 'atilde', 'auml', 'aring', 'aelig', 'ccedil',
    'egrave', 'eacute', 'ecirc', 'euml', 'igrave', 'iacute',
    'icirc', 'iuml', 'eth', 'ntilde', 'ograve', 'oacute',
    'ocirc', 'otilde', 'ouml', 'divide', 'oslash', 'ugrave',
    'uacute', 'ucirc', 'uuml', 'yacute', 'thorn', 'yuml',
    'quot', 'amp', 'lt', 'gt', 'apos', 'OElig',
    'oelig', 'Scaron', 'scaron', 'Yuml', 'circ', 'tilde',
    'ensp', 'emsp', 'thinsp', 'zwnj', 'zwj', 'lrm',
    'rlm', 'ndash', 'mdash', 'lsquo', 'rsquo', 'sbquo',
    'ldquo', 'rdquo', 'bdquo', 'dagger', 'Dagger', 'permil',
    'lsaquo', 'rsaquo', 'euro', 'fnof', 'Alpha', 'Beta',
    'Gamma', 'Delta', 'Epsilon', 'Zeta', 'Eta', 'Theta',
    'Iota', 'Kappa', 'Lambda', 'Mu', 'Nu', 'Xi',
    'Omicron', 'Pi', 'Rho', 'Sigma', 'Tau', 'Upsilon',
    'Phi', 'Chi', 'Psi', 'Omega', 'alpha', 'beta',
    'gamma', 'delta', 'epsilon', 'zeta', 'eta', 'theta',
    'iota', 'kappa', 'lambda', 'mu', 'nu', 'xi',
    'omicron', 'pi', 'rho', 'sigmaf', 'sigma', 'tau',
    'upsilon', 'phi', 'chi', 'psi', 'omega', 'thetasym',
    'upsih', 'piv', 'bull', 'hellip', 'prime', 'Prime',
    'oline', 'frasl', 'weierp', 'image', 'real', 'trade',
    'alefsym', 'larr', 'uarr', 'rarr', 'darr', 'harr',
    'crarr', 'lArr', 'uArr', 'rArr', 'dArr', 'hArr',
    'forall', 'part', 'exist', 'empty', 'nabla', 'isin',
    'notin', 'ni', 'prod', 'sum', 'minus', 'lowast',
    'radic', 'prop', 'infin', 'ang', 'and', 'or',
    'cap', 'cup', 'int', 'sim', 'cong', 'asymp',
    'ne', 'equiv', 'le', 'ge', 'sub', 'sup',
    'nsub', 'sube', 'supe', 'oplus', 'otimes', 'perp',
    'sdot', 'lceil', 'rceil', 'lfloor', 'rfloor', 'lang',
    'rang', 'loz', 'spades', 'clubs', 'hearts', 'diams',
    'sup1', 'sup2', 'sup3', 'frac14', 'frac12', 'frac34',
    'there4',
);

/**
 * Filters text content and strips out disallowed HTML.
 *
 * This function makes sure that only the allowed HTML element names, attribute
 * names, attribute values, and HTML entities will occur in the given text string.
 *
 * This function expects unslashed data.
 *
 * @param string $string Text content to filter.
 * @param array[]|string $allowed_html An array of allowed HTML elements and attributes, or a
 *                                          context name such as 'post'.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Filtered content containing only the allowed HTML.
 * @see wp_allowed_protocols() for the default allowed protocols in link URLs.
 *
 * @since 1.0.0
 *
 * @see wp_kses_post() for specifically filtering post content and fields.
 */
function wp_kses( $string, $allowed_html, $allowed_protocols = array() )
{
    if ( empty( $allowed_protocols ) ) {
        $allowed_protocols = [ 'http', 'https', 'ftp', 'ftps', 'mailto', 'news', 'irc', 'gopher', 'nntp', 'feed', 'telnet', 'mms', 'rtsp', 'svn', 'tel', 'fax', 'xmpp', 'webcal', 'urn' ];
    }
    $string = wp_kses_no_null( $string, array( 'slash_zero' => 'keep' ) );
    $string = wp_kses_normalize_entities( $string );
    $string = preg_replace_callback( '%<[^>]*?((?=<)|>|$)%', 'wp_pre_kses_less_than_callback', $string );
    return wp_kses_split( $string, $allowed_html, $allowed_protocols );
}

/**
 * Callback function used by preg_replace.
 *
 * @param array $matches Populated by matches to preg_replace.
 * @return string The text returned after esc_html if needed.
 * @since 2.3.0
 *
 */
function wp_pre_kses_less_than_callback( $matches )
{
    if ( false === strpos( $matches[ 0 ], '>' ) ) {
        return htmlspecialchars( $matches[ 0 ] );
    }
    return $matches[ 0 ];
}

/**
 * Removes any invalid control characters in a text string.
 *
 * Also removes any instance of the `\0` string.
 *
 * @param string $string Content to filter null characters from.
 * @param array $options Set 'slash_zero' => 'keep' when '\0' is allowed. Default is 'remove'.
 * @return string Filtered content.
 * @since 1.0.0
 *
 */
function wp_kses_no_null( $string, $options = null )
{
    if ( !isset( $options[ 'slash_zero' ] ) ) {
        $options = array( 'slash_zero' => 'remove' );
    }

    $string = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $string );
    if ( 'remove' == $options[ 'slash_zero' ] ) {
        $string = preg_replace( '/\\\\+0+/', '', $string );
    }

    return $string;
}

/**
 * Converts and fixes HTML entities.
 *
 * This function normalizes HTML entities. It will convert `AT&T` to the correct
 * `AT&amp;T`, `&#00058;` to `&#58;`, `&#XYZZY;` to `&amp;#XYZZY;` and so on.
 *
 * @param string $string Content to normalize entities.
 * @return string Content with normalized entities.
 * @since 1.0.0
 *
 */
function wp_kses_normalize_entities( $string )
{
    // Disarm all entities by converting & to &amp;
    $string = str_replace( '&', '&amp;', $string );

    // Change back the allowed entities in our entity whitelist
    $string = preg_replace_callback( '/&amp;([A-Za-z]{2,8}[0-9]{0,2});/', 'wp_kses_named_entities', $string );
    $string = preg_replace_callback( '/&amp;#(0*[0-9]{1,7});/', 'wp_kses_normalize_entities2', $string );
    $string = preg_replace_callback( '/&amp;#[Xx](0*[0-9A-Fa-f]{1,6});/', 'wp_kses_normalize_entities3', $string );

    return $string;
}

/**
 * Callback for `wp_kses_normalize_entities()` regular expression.
 *
 * This function only accepts valid named entity references, which are finite,
 * case-sensitive, and highly scrutinized by HTML and XML validators.
 *
 * @param array $matches preg_replace_callback() matches array.
 * @return string Correctly encoded entity.
 * @since 3.0.0
 *
 * @global array $allowedentitynames
 *
 */
function wp_kses_named_entities( $matches )
{
    global $allowedentitynames;

    if ( empty( $matches[ 1 ] ) ) {
        return '';
    }

    $i = $matches[ 1 ];
    return ( !in_array( $i, $allowedentitynames ) ) ? "&amp;$i;" : "&$i;";
}

/**
 * Callback for wp_kses_normalize_entities() regular expression.
 *
 * This function helps wp_kses_normalize_entities() to only accept 16-bit
 * values and nothing more for `&#number;` entities.
 *
 * @access private
 * @param array $matches preg_replace_callback() matches array
 * @return string Correctly encoded entity
 * @since 1.0.0
 *
 */
function wp_kses_normalize_entities2( $matches )
{
    if ( empty( $matches[ 1 ] ) ) {
        return '';
    }

    $i = $matches[ 1 ];
    if ( valid_unicode( $i ) ) {
        $i = str_pad( ltrim( $i, '0' ), 3, '0', STR_PAD_LEFT );
        $i = "&#$i;";
    }
    else {
        $i = "&amp;#$i;";
    }

    return $i;
}

/**
 * Helper function to determine if a Unicode value is valid.
 *
 * @param int $i Unicode value
 * @return bool True if the value was a valid Unicode number
 * @since 2.7.0
 *
 */
function valid_unicode( $i )
{
    return ( $i == 0x9 || $i == 0xa || $i == 0xd ||
        ( $i >= 0x20 && $i <= 0xd7ff ) ||
        ( $i >= 0xe000 && $i <= 0xfffd ) ||
        ( $i >= 0x10000 && $i <= 0x10ffff ) );
}

/**
 * Callback for `wp_kses_normalize_entities()` for regular expression.
 *
 * This function helps `wp_kses_normalize_entities()` to only accept valid Unicode
 * numeric entities in hex form.
 *
 * @param array $matches `preg_replace_callback()` matches array.
 * @return string Correctly encoded entity.
 * @since 2.7.0
 * @access private
 * @ignore
 *
 */
function wp_kses_normalize_entities3( $matches )
{
    if ( empty( $matches[ 1 ] ) ) {
        return '';
    }

    $hexchars = $matches[ 1 ];
    return ( !valid_unicode( hexdec( $hexchars ) ) ) ? "&amp;#x$hexchars;" : '&#x' . ltrim( $hexchars, '0' ) . ';';
}

/**
 * Searches for HTML tags, no matter how malformed.
 *
 * It also matches stray `>` characters.
 *
 * @param string $string Content to filter.
 * @param array $allowed_html Allowed HTML elements.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Content with fixed HTML tags
 * @global array $pass_allowed_html
 * @global array $pass_allowed_protocols
 *
 * @since 1.0.0
 *
 */
function wp_kses_split( $string, $allowed_html, $allowed_protocols )
{
    global $pass_allowed_html, $pass_allowed_protocols;
    $pass_allowed_html = $allowed_html;
    $pass_allowed_protocols = $allowed_protocols;
    return preg_replace_callback( '%(<!--.*?(-->|$))|(<[^>]*(>|$)|>)%', '_wp_kses_split_callback', $string );
}

/**
 * Callback for `wp_kses_split()`.
 *
 * @return string
 * @ignore
 *
 * @global array $pass_allowed_html
 * @global array $pass_allowed_protocols
 *
 * @since 3.1.0
 * @access private
 */
function _wp_kses_split_callback( $match )
{
    global $pass_allowed_html, $pass_allowed_protocols;
    return wp_kses_split2( $match[ 0 ], $pass_allowed_html, $pass_allowed_protocols );
}

/**
 * Strips slashes from in front of quotes.
 *
 * This function changes the character sequence `\"` to just `"`. It leaves all other
 * slashes alone. The quoting from `preg_replace(//e)` requires this.
 *
 * @param string $string String to strip slashes from.
 * @return string Fixed string with quoted slashes.
 * @since 1.0.0
 *
 */
function wp_kses_stripslashes( $string )
{
    return preg_replace( '%\\\\"%', '"', $string );
}

/**
 * Callback for `wp_kses_split()` for fixing malformed HTML tags.
 *
 * This function does a lot of work. It rejects some very malformed things like
 * `<:::>`. It returns an empty string, if the element isn't allowed (look ma, no
 * `strip_tags()`!). Otherwise it splits the tag into an element and an attribute
 * list.
 *
 * After the tag is split into an element and an attribute list, it is run
 * through another filter which will remove illegal attributes and once that is
 * completed, will be returned.
 *
 * @access private
 * @param string $string Content to filter.
 * @param array $allowed_html Allowed HTML elements.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Fixed HTML element
 * @ignore
 * @since 1.0.0
 *
 */
function wp_kses_split2( $string, $allowed_html, $allowed_protocols )
{
    $string = wp_kses_stripslashes( $string );

    // It matched a ">" character.
    if ( substr( $string, 0, 1 ) != '<' ) {
        return '&gt;';
    }

    // Allow HTML comments.
    if ( '<!--' == substr( $string, 0, 4 ) ) {
        $string = str_replace( array( '<!--', '-->' ), '', $string );
        while ( $string != ( $newstring = wp_kses( $string, $allowed_html, $allowed_protocols ) ) ) {
            $string = $newstring;
        }
        if ( $string == '' ) {
            return '';
        }
        // prevent multiple dashes in comments
        $string = preg_replace( '/--+/', '-', $string );
        // prevent three dashes closing a comment
        $string = preg_replace( '/-$/', '', $string );
        return "<!--{$string}-->";
    }

    // It's seriously malformed.
    if ( !preg_match( '%^<\s*(/\s*)?([a-zA-Z0-9-]+)([^>]*)>?$%', $string, $matches ) ) {
        return '';
    }

    $slash = trim( $matches[ 1 ] );
    $elem = $matches[ 2 ];
    $attrlist = $matches[ 3 ];

    if ( !is_array( $allowed_html ) ) {
        $allowed_html = [];
    }

    // They are using a not allowed HTML element.
    if ( !isset( $allowed_html[ strtolower( $elem ) ] ) ) {
        return '';
    }

    // No attributes are allowed for closing elements.
    if ( $slash != '' ) {
        return "</$elem>";
    }

    return wp_kses_attr( $elem, $attrlist, $allowed_html, $allowed_protocols );
}

/**
 * Builds an attribute list from string containing attributes.
 *
 * This function does a lot of work. It parses an attribute list into an array
 * with attribute data, and tries to do the right thing even if it gets weird
 * input. It will add quotes around attribute values that don't have any quotes
 * or apostrophes around them, to make it easier to produce HTML code that will
 * conform to W3C's HTML specification. It will also remove bad URL protocols
 * from attribute values. It also reduces duplicate attributes by using the
 * attribute defined first (`foo='bar' foo='baz'` will result in `foo='bar'`).
 *
 * @param string $attr Attribute list from HTML element to closing HTML element tag.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return array[] Array of attribute information after parsing.
 * @since 1.0.0
 *
 */
function wp_kses_hair( $attr, $allowed_protocols )
{
    $attrarr = array();
    $mode = 0;
    $attrname = '';
    $uris = wp_kses_uri_attributes();

    // Loop through the whole attribute list

    while ( strlen( $attr ) != 0 ) {
        $working = 0; // Was the last operation successful?

        switch ( $mode ) {
            case 0:
                if ( preg_match( '/^([-a-zA-Z:]+)/', $attr, $match ) ) {
                    $attrname = $match[ 1 ];
                    $working = $mode = 1;
                    $attr = preg_replace( '/^[-a-zA-Z:]+/', '', $attr );
                }

                break;

            case 1:
                if ( preg_match( '/^\s*=\s*/', $attr ) ) { // equals sign
                    $working = 1;
                    $mode = 2;
                    $attr = preg_replace( '/^\s*=\s*/', '', $attr );
                    break;
                }

                if ( preg_match( '/^\s+/', $attr ) ) { // valueless
                    $working = 1;
                    $mode = 0;
                    if ( false === array_key_exists( $attrname, $attrarr ) ) {
                        $attrarr[ $attrname ] = array(
                            'name' => $attrname,
                            'value' => '',
                            'whole' => $attrname,
                            'vless' => 'y',
                        );
                    }
                    $attr = preg_replace( '/^\s+/', '', $attr );
                }

                break;

            case 2:
                if ( preg_match( '%^"([^"]*)"(\s+|/?$)%', $attr, $match ) ) {
                    // "value"
                    $thisval = $match[ 1 ];
                    if ( in_array( strtolower( $attrname ), $uris ) ) {
                        $thisval = wp_kses_bad_protocol( $thisval, $allowed_protocols );
                    }

                    if ( false === array_key_exists( $attrname, $attrarr ) ) {
                        $attrarr[ $attrname ] = array(
                            'name' => $attrname,
                            'value' => $thisval,
                            'whole' => "$attrname=\"$thisval\"",
                            'vless' => 'n',
                        );
                    }
                    $working = 1;
                    $mode = 0;
                    $attr = preg_replace( '/^"[^"]*"(\s+|$)/', '', $attr );
                    break;
                }

                if ( preg_match( "%^'([^']*)'(\s+|/?$)%", $attr, $match ) ) {
                    // 'value'
                    $thisval = $match[ 1 ];
                    if ( in_array( strtolower( $attrname ), $uris ) ) {
                        $thisval = wp_kses_bad_protocol( $thisval, $allowed_protocols );
                    }

                    if ( false === array_key_exists( $attrname, $attrarr ) ) {
                        $attrarr[ $attrname ] = array(
                            'name' => $attrname,
                            'value' => $thisval,
                            'whole' => "$attrname='$thisval'",
                            'vless' => 'n',
                        );
                    }
                    $working = 1;
                    $mode = 0;
                    $attr = preg_replace( "/^'[^']*'(\s+|$)/", '', $attr );
                    break;
                }

                if ( preg_match( "%^([^\s\"']+)(\s+|/?$)%", $attr, $match ) ) {
                    // value
                    $thisval = $match[ 1 ];
                    if ( in_array( strtolower( $attrname ), $uris ) ) {
                        $thisval = wp_kses_bad_protocol( $thisval, $allowed_protocols );
                    }

                    if ( false === array_key_exists( $attrname, $attrarr ) ) {
                        $attrarr[ $attrname ] = array(
                            'name' => $attrname,
                            'value' => $thisval,
                            'whole' => "$attrname=\"$thisval\"",
                            'vless' => 'n',
                        );
                    }
                    // We add quotes to conform to W3C's HTML spec.
                    $working = 1;
                    $mode = 0;
                    $attr = preg_replace( "%^[^\s\"']+(\s+|$)%", '', $attr );
                }

                break;
        } // switch

        if ( $working == 0 ) { // not well formed, remove and try again
            $attr = wp_kses_html_error( $attr );
            $mode = 0;
        }
    } // while

    if ( $mode == 1 && false === array_key_exists( $attrname, $attrarr ) ) {
        // special case, for when the attribute list ends with a valueless
        // attribute like "selected"
        $attrarr[ $attrname ] = array(
            'name' => $attrname,
            'value' => '',
            'whole' => $attrname,
            'vless' => 'y',
        );
    }

    return $attrarr;
}

/**
 * Helper function listing HTML attributes containing a URL.
 *
 * This function returns a list of all HTML attributes that must contain
 * a URL according to the HTML specification.
 *
 * This list includes URI attributes both allowed and disallowed by KSES.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes
 *
 * @since 5.0.1
 *
 * @return array HTML attributes that must include a URL.
 */
function wp_kses_uri_attributes()
{
    $uri_attributes = array(
        'action',
        'archive',
        'background',
        'cite',
        'classid',
        'codebase',
        'data',
        'formaction',
        'href',
        'icon',
        'longdesc',
        'manifest',
        'poster',
        'profile',
        'src',
        'usemap',
        'xmlns',
    );
    return $uri_attributes;
}

/**
 * Sanitize string from bad protocols.
 *
 * This function removes all non-allowed protocols from the beginning of
 * $string. It ignores whitespace and the case of the letters, and it does
 * understand HTML entities. It does its work in a while loop, so it won't be
 * fooled by a string like "javascript:javascript:alert(57)".
 *
 * @param string $string Content to filter bad protocols from
 * @param array $allowed_protocols Allowed protocols to keep
 * @return string Filtered content
 * @since 1.0.0
 *
 */
function wp_kses_bad_protocol( $string, $allowed_protocols )
{
    $string = wp_kses_no_null( $string );
    $iterations = 0;

    do {
        $original_string = $string;
        $string = wp_kses_bad_protocol_once( $string, $allowed_protocols );
    }
    while ( $original_string != $string && ++$iterations < 6 );

    if ( $original_string != $string ) {
        return '';
    }

    return $string;
}

/**
 * Sanitizes content from bad protocols and other characters.
 *
 * This function searches for URL protocols at the beginning of $string, while
 * handling whitespace and HTML entities.
 *
 * @param string $string Content to check for bad protocols
 * @param string|array $allowed_protocols Allowed protocols
 * @param int $count
 * @return string Sanitized content
 * @since 1.0.0
 *
 */
function wp_kses_bad_protocol_once( $string, $allowed_protocols, $count = 1 )
{
    $string2 = preg_split( '/:|&#0*58;|&#x0*3a;/i', $string, 2 );
    if ( isset( $string2[ 1 ] ) && !preg_match( '%/\?%', $string2[ 0 ] ) ) {
        $string = trim( $string2[ 1 ] );
        $protocol = wp_kses_bad_protocol_once2( $string2[ 0 ], $allowed_protocols );
        if ( 'feed:' == $protocol ) {
            if ( $count > 2 ) {
                return '';
            }
            $string = wp_kses_bad_protocol_once( $string, $allowed_protocols, ++$count );
            if ( empty( $string ) ) {
                return $string;
            }
        }
        $string = $protocol . $string;
    }

    return $string;
}

/**
 * Callback for wp_kses_bad_protocol_once() regular expression.
 *
 * This function processes URL protocols, checks to see if they're in the
 * whitelist or not, and returns different data depending on the answer.
 *
 * @access private
 * @param string $string URI scheme to check against the whitelist
 * @param string $allowed_protocols Allowed protocols
 * @return string Sanitized content
 * @since 1.0.0
 *
 */
function wp_kses_bad_protocol_once2( $string, $allowed_protocols )
{
    $string2 = wp_kses_decode_entities( $string );
    $string2 = preg_replace( '/\s/', '', $string2 );
    $string2 = wp_kses_no_null( $string2 );
    $string2 = strtolower( $string2 );

    $allowed = false;
    foreach ( (array)$allowed_protocols as $one_protocol ) {
        if ( strtolower( $one_protocol ) == $string2 ) {
            $allowed = true;
            break;
        }
    }

    if ( $allowed ) {
        return "$string2:";
    }
    else {
        return '';
    }
}

/**
 * Convert all entities to their character counterparts.
 *
 * This function decodes numeric HTML entities (`&#65;` and `&#x41;`).
 * It doesn't do anything with other entities like &auml;, but we don't
 * need them in the URL protocol whitelisting system anyway.
 *
 * @param string $string Content to change entities
 * @return string Content after decoded entities
 * @since 1.0.0
 *
 */
function wp_kses_decode_entities( $string )
{
    $string = preg_replace_callback( '/&#([0-9]+);/', '_wp_kses_decode_entities_chr', $string );
    $string = preg_replace_callback( '/&#[Xx]([0-9A-Fa-f]+);/', '_wp_kses_decode_entities_chr_hexdec', $string );

    return $string;
}

/**
 * Regex callback for wp_kses_decode_entities()
 *
 * @param array $match preg match
 * @return string
 * @since 2.9.0
 *
 */
function _wp_kses_decode_entities_chr( $match )
{
    return chr( $match[ 1 ] );
}

/**
 * Regex callback for wp_kses_decode_entities()
 *
 * @param array $match preg match
 * @return string
 * @since 2.9.0
 *
 */
function _wp_kses_decode_entities_chr_hexdec( $match )
{
    return chr( hexdec( $match[ 1 ] ) );
}

/**
 * Handles parsing errors in wp_kses_hair().
 *
 * The general plan is to remove everything to and including some whitespace,
 * but it deals with quotes and apostrophes as well.
 *
 * @param string $string
 * @return string
 * @since 1.0.0
 *
 */
function wp_kses_html_error( $string )
{
    return preg_replace( '/^("[^"]*("|$)|\'[^\']*(\'|$)|\S)*\s*/', '', $string );
}

/**
 * Removes all attributes, if none are allowed for this element.
 *
 * If some are allowed it calls `wp_kses_hair()` to split them further, and then
 * it builds up new HTML code from the data that `kses_hair()` returns. It also
 * removes `<` and `>` characters, if there are any left. One more thing it does
 * is to check if the tag has a closing XHTML slash, and if it does, it puts one
 * in the returned code as well.
 *
 * @param string $element HTML element/tag.
 * @param string $attr HTML attributes from HTML element to closing HTML element tag.
 * @param array $allowed_html Allowed HTML elements.
 * @param string[] $allowed_protocols Array of allowed URL protocols.
 * @return string Sanitized HTML element.
 * @since 1.0.0
 *
 */
function wp_kses_attr( $element, $attr, $allowed_html, $allowed_protocols )
{
    if ( !is_array( $allowed_html ) ) {
        $allowed_html = [];
    }

    // Is there a closing XHTML slash at the end of the attributes?
    $xhtml_slash = '';
    if ( preg_match( '%\s*/\s*$%', $attr ) ) {
        $xhtml_slash = ' /';
    }

    // Are any attributes allowed at all for this element?
    $element_low = strtolower( $element );
    if ( empty( $allowed_html[ $element_low ] ) || true === $allowed_html[ $element_low ] ) {
        return "<$element$xhtml_slash>";
    }

    // Split it
    $attrarr = wp_kses_hair( $attr, $allowed_protocols );

    // Go through $attrarr, and save the allowed attributes for this element
    // in $attr2
    $attr2 = '';
    foreach ( $attrarr as $arreach ) {
        if ( wp_kses_attr_check( $arreach[ 'name' ], $arreach[ 'value' ], $arreach[ 'whole' ], $arreach[ 'vless' ], $element, $allowed_html ) ) {
            $attr2 .= ' ' . $arreach[ 'whole' ];
        }
    }

    // Remove any "<" or ">" characters
    $attr2 = preg_replace( '/[<>]/', '', $attr2 );

    return "<$element$attr2$xhtml_slash>";
}

/**
 * Determines whether an attribute is allowed.
 *
 * @param string $name The attribute name. Passed by reference. Returns empty string when not allowed.
 * @param string $value The attribute value. Passed by reference. Returns a filtered value.
 * @param string $whole The `name=value` input. Passed by reference. Returns filtered input.
 * @param string $vless Whether the attribute is valueless. Use 'y' or 'n'.
 * @param string $element The name of the element to which this attribute belongs.
 * @param array $allowed_html The full list of allowed elements and attributes.
 * @return bool Whether or not the attribute is allowed.
 * @since 5.0.0 Add support for `data-*` wildcard attributes.
 *
 * @since 4.2.3
 */
function wp_kses_attr_check( &$name, &$value, &$whole, $vless, $element, $allowed_html )
{
    $allowed_attr = $allowed_html[ strtolower( $element ) ];

    $name_low = strtolower( $name );
    if ( !isset( $allowed_attr[ $name_low ] ) || '' == $allowed_attr[ $name_low ] ) {
        /*
         * Allow `data-*` attributes.
         *
         * When specifying `$allowed_html`, the attribute name should be set as
         * `data-*` (not to be mixed with the HTML 4.0 `data` attribute, see
         * https://www.w3.org/TR/html40/struct/objects.html#adef-data).
         *
         * Note: the attribute name should only contain `A-Za-z0-9_-` chars,
         * double hyphens `--` are not accepted by WordPress.
         */
        if ( strpos( $name_low, 'data-' ) === 0 && !empty( $allowed_attr[ 'data-*' ] ) && preg_match( '/^data(?:-[a-z0-9_]+)+$/', $name_low, $match ) ) {
            /*
             * Add the whole attribute name to the allowed attributes and set any restrictions
             * for the `data-*` attribute values for the current element.
             */
            $allowed_attr[ $match[ 0 ] ] = $allowed_attr[ 'data-*' ];
        }
        else {
            $name = $value = $whole = '';
            return false;
        }
    }

    if ( 'style' == $name_low ) {
        $new_value = safecss_filter_attr( $value );

        if ( empty( $new_value ) ) {
            $name = $value = $whole = '';
            return false;
        }

        $whole = str_replace( $value, $new_value, $whole );
        $value = $new_value;
    }

    if ( is_array( $allowed_attr[ $name_low ] ) ) {
        // there are some checks
        foreach ( $allowed_attr[ $name_low ] as $currkey => $currval ) {
            if ( !wp_kses_check_attr_val( $value, $vless, $currkey, $currval ) ) {
                $name = $value = $whole = '';
                return false;
            }
        }
    }

    return true;
}

/**
 * Performs different checks for attribute values.
 *
 * The currently implemented checks are "maxlen", "minlen", "maxval", "minval"
 * and "valueless".
 *
 * @param string $value Attribute value
 * @param string $vless Whether the value is valueless. Use 'y' or 'n'
 * @param string $checkname What $checkvalue is checking for.
 * @param mixed $checkvalue What constraint the value should pass
 * @return bool Whether check passes
 * @since 1.0.0
 *
 */
function wp_kses_check_attr_val( $value, $vless, $checkname, $checkvalue )
{
    $ok = true;

    switch ( strtolower( $checkname ) ) {
        case 'maxlen' :
            // The maxlen check makes sure that the attribute value has a length not
            // greater than the given value. This can be used to avoid Buffer Overflows
            // in WWW clients and various Internet servers.

            if ( strlen( $value ) > $checkvalue ) {
                $ok = false;
            }
            break;

        case 'minlen' :
            // The minlen check makes sure that the attribute value has a length not
            // smaller than the given value.

            if ( strlen( $value ) < $checkvalue ) {
                $ok = false;
            }
            break;

        case 'maxval' :
            // The maxval check does two things: it checks that the attribute value is
            // an integer from 0 and up, without an excessive amount of zeroes or
            // whitespace (to avoid Buffer Overflows). It also checks that the attribute
            // value is not greater than the given value.
            // This check can be used to avoid Denial of Service attacks.

            if ( !preg_match( '/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value ) ) {
                $ok = false;
            }
            if ( $value > $checkvalue ) {
                $ok = false;
            }
            break;

        case 'minval' :
            // The minval check makes sure that the attribute value is a positive integer,
            // and that it is not smaller than the given value.

            if ( !preg_match( '/^\s{0,6}[0-9]{1,6}\s{0,6}$/', $value ) ) {
                $ok = false;
            }
            if ( $value < $checkvalue ) {
                $ok = false;
            }
            break;

        case 'valueless' :
            // The valueless check makes sure if the attribute has a value
            // (like <a href="blah">) or not (<option selected>). If the given value
            // is a "y" or a "Y", the attribute must not have a value.
            // If the given value is an "n" or an "N", the attribute must have one.

            if ( strtolower( $checkvalue ) != $vless ) {
                $ok = false;
            }
            break;
    } // switch

    return $ok;
}

/**
 * Inline CSS filter
 *
 * @param string $css A string of CSS rules.
 * @return string            Filtered string of CSS rules.
 * @since 2.8.1
 *
 */
function safecss_filter_attr( $css )
{
    $css = wp_kses_no_null( $css );
    $css = str_replace( array( "\n", "\r", "\t" ), '', $css );

    if ( preg_match( '%[\\\\(&=}]|/\*%', $css ) ) // remove any inline css containing \ ( & } = or comments
    {
        return '';
    }

    $css_array = explode( ';', trim( $css ) );

    /**
     * Filters list of allowed CSS attributes.
     *
     * @param array $attr List of allowed CSS attributes.
     * @since 4.4.0 Added support for `min-height`, `max-height`, `min-width`, and `max-width`.
     * @since 4.6.0 Added support for `list-style-type`.
     *
     * @since 2.8.1
     */
    $allowed_attr = [
        'background',
        'background-color',

        'border',
        'border-width',
        'border-color',
        'border-style',
        'border-right',
        'border-right-color',
        'border-right-style',
        'border-right-width',
        'border-bottom',
        'border-bottom-color',
        'border-bottom-style',
        'border-bottom-width',
        'border-left',
        'border-left-color',
        'border-left-style',
        'border-left-width',
        'border-top',
        'border-top-color',
        'border-top-style',
        'border-top-width',

        'border-spacing',
        'border-collapse',
        'caption-side',

        'color',
        'font',
        'font-family',
        'font-size',
        'font-style',
        'font-variant',
        'font-weight',
        'letter-spacing',
        'line-height',
        'text-decoration',
        'text-indent',
        'text-align',

        'height',
        'min-height',
        'max-height',

        'width',
        'min-width',
        'max-width',

        'margin',
        'margin-right',
        'margin-bottom',
        'margin-left',
        'margin-top',

        'padding',
        'padding-right',
        'padding-bottom',
        'padding-left',
        'padding-top',

        'clear',
        'cursor',
        'direction',
        'float',
        'overflow',
        'vertical-align',
        'list-style-type',
    ];

    if ( empty( $allowed_attr ) ) {
        return $css;
    }

    $css = '';
    foreach ( $css_array as $css_item ) {
        if ( $css_item == '' ) {
            continue;
        }
        $css_item = trim( $css_item );
        $found = false;
        if ( strpos( $css_item, ':' ) === false ) {
            $found = true;
        }
        else {
            $parts = explode( ':', $css_item );
            if ( in_array( trim( $parts[ 0 ] ), $allowed_attr ) ) {
                $found = true;
            }
        }
        if ( $found ) {
            if ( $css != '' ) {
                $css .= ';';
            }
            $css .= $css_item;
        }
    }

    return $css;
}

/**
 * Sanitizes a filename, replacing whitespace with dashes.
 *
 * Removes special characters that are illegal in filenames on certain
 * operating systems and special characters requiring special escaping
 * to manipulate at the command line. Replaces spaces and consecutive
 * dashes with a single dash. Trims period, dash and underscore from beginning
 * and end of filename. It is not guaranteed that this function will return a
 * filename that is allowed to be uploaded.
 *
 * @param string $filename The filename to be sanitized
 * @return string The sanitized filename
 * @since 2.1.0
 *
 */
function sanitize_file_name( $filename )
{
    $special_chars = [ "?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr( 0 ) ];
    $filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
    $filename = str_replace( $special_chars, '', $filename );
    $filename = str_replace( array( '%20', '+' ), '-', $filename );
    $filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
    $filename = trim( $filename, '.-_' );

    if ( false === strpos( $filename, '.' ) ) {
        $mime_types = wp_get_mime_types();
        $filetype = wp_check_filetype( 'test.' . $filename, $mime_types );
        if ( $filetype[ 'ext' ] === $filename ) {
            $filename = 'unnamed-file.' . $filetype[ 'ext' ];
        }
    }

    // Split the filename into a base and extension[s]
    $parts = explode( '.', $filename );

    // Return if only one extension
    if ( count( $parts ) <= 2 ) {
        return $filename;
    }

    // Process multiple extensions
    $filename = array_shift( $parts );
    $extension = array_pop( $parts );
    $mimes = get_allowed_mime_types();

    /*
     * Loop over any intermediate extensions. Postfix them with a trailing underscore
     * if they are a 2 - 5 character long alpha string not in the extension whitelist.
     */
    foreach ( (array)$parts as $part ) {
        $filename .= '.' . $part;

        if ( preg_match( "/^[a-zA-Z]{2,5}\d?$/", $part ) ) {
            $allowed = false;
            foreach ( $mimes as $ext_preg => $mime_match ) {
                $ext_preg = '!^(' . $ext_preg . ')$!i';
                if ( preg_match( $ext_preg, $part ) ) {
                    $allowed = true;
                    break;
                }
            }
            if ( !$allowed ) {
                $filename .= '_';
            }
        }
    }
    $filename .= '.' . $extension;
    return $filename;
}

/**
 * Retrieve list of mime types and file extensions.
 *
 * @return array Array of mime types keyed by the file extension regex corresponding to those types.
 * @since 4.2.0 Support was added for GIMP (xcf) files.
 *
 * @since 3.5.0
 */
function wp_get_mime_types()
{
    return [
        // Image formats.
        'jpg|jpeg|jpe' => 'image/jpeg',
        'gif' => 'image/gif',
        'png' => 'image/png',
        'bmp' => 'image/bmp',
        'tiff|tif' => 'image/tiff',
        'ico' => 'image/x-icon',
        // Video formats.
        'asf|asx' => 'video/x-ms-asf',
        'wmv' => 'video/x-ms-wmv',
        'wmx' => 'video/x-ms-wmx',
        'wm' => 'video/x-ms-wm',
        'avi' => 'video/avi',
        'divx' => 'video/divx',
        'flv' => 'video/x-flv',
        'mov|qt' => 'video/quicktime',
        'mpeg|mpg|mpe' => 'video/mpeg',
        'mp4|m4v' => 'video/mp4',
        'ogv' => 'video/ogg',
        'webm' => 'video/webm',
        'mkv' => 'video/x-matroska',
        '3gp|3gpp' => 'video/3gpp', // Can also be audio
        '3g2|3gp2' => 'video/3gpp2', // Can also be audio
        // Text formats.
        'txt|asc|c|cc|h|srt' => 'text/plain',
        'csv' => 'text/csv',
        'tsv' => 'text/tab-separated-values',
        'ics' => 'text/calendar',
        'rtx' => 'text/richtext',
        'css' => 'text/css',
        'htm|html' => 'text/html',
        'vtt' => 'text/vtt',
        'dfxp' => 'application/ttaf+xml',
        // Audio formats.
        'mp3|m4a|m4b' => 'audio/mpeg',
        'aac' => 'audio/aac',
        'ra|ram' => 'audio/x-realaudio',
        'wav' => 'audio/wav',
        'ogg|oga' => 'audio/ogg',
        'flac' => 'audio/flac',
        'mid|midi' => 'audio/midi',
        'wma' => 'audio/x-ms-wma',
        'wax' => 'audio/x-ms-wax',
        'mka' => 'audio/x-matroska',
        // Misc application formats.
        'rtf' => 'application/rtf',
        'js' => 'application/javascript',
        'pdf' => 'application/pdf',
        'swf' => 'application/x-shockwave-flash',
        'class' => 'application/java',
        'tar' => 'application/x-tar',
        'zip' => 'application/zip',
        'gz|gzip' => 'application/x-gzip',
        'rar' => 'application/rar',
        '7z' => 'application/x-7z-compressed',
        'exe' => 'application/x-msdownload',
        'psd' => 'application/octet-stream',
        'xcf' => 'application/octet-stream',
        // MS Office formats.
        'doc' => 'application/msword',
        'pot|pps|ppt' => 'application/vnd.ms-powerpoint',
        'wri' => 'application/vnd.ms-write',
        'xla|xls|xlt|xlw' => 'application/vnd.ms-excel',
        'mdb' => 'application/vnd.ms-access',
        'mpp' => 'application/vnd.ms-project',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
        'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
        'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
        'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
        'onetoc|onetoc2|onetmp|onepkg' => 'application/onenote',
        'oxps' => 'application/oxps',
        'xps' => 'application/vnd.ms-xpsdocument',
        // OpenOffice formats.
        'odt' => 'application/vnd.oasis.opendocument.text',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odg' => 'application/vnd.oasis.opendocument.graphics',
        'odc' => 'application/vnd.oasis.opendocument.chart',
        'odb' => 'application/vnd.oasis.opendocument.database',
        'odf' => 'application/vnd.oasis.opendocument.formula',
        // WordPerfect formats.
        'wp|wpd' => 'application/wordperfect',
        // iWork formats.
        'key' => 'application/vnd.apple.keynote',
        'numbers' => 'application/vnd.apple.numbers',
        'pages' => 'application/vnd.apple.pages',
    ];
}

/**
 * Retrieve the file type from the file name.
 *
 * You can optionally define the mime array, if needed.
 *
 * @param string $filename File name or path.
 * @param array $mimes Optional. Key is the file extension with value as the mime type.
 * @return array Values with extension first and mime type.
 * @since 2.0.4
 *
 */
function wp_check_filetype( $filename, $mimes = null )
{
    if ( empty( $mimes ) ) {
        $mimes = get_allowed_mime_types();
    }
    $type = false;
    $ext = false;

    foreach ( $mimes as $ext_preg => $mime_match ) {
        $ext_preg = '!\.(' . $ext_preg . ')$!i';
        if ( preg_match( $ext_preg, $filename, $ext_matches ) ) {
            $type = $mime_match;
            $ext = $ext_matches[ 1 ];
            break;
        }
    }
    return compact( 'ext', 'type' );
}

/**
 * Retrieve list of allowed mime types and file extensions.
 *
 * @return array Array of mime types keyed by the file extension regex corresponding
 *               to those types.
 * @since 2.8.6
 *
 */
function get_allowed_mime_types()
{
    $t = wp_get_mime_types();

    unset( $t[ 'swf' ], $t[ 'exe' ] );

    if ( empty( $unfiltered ) ) {
        unset( $t[ 'htm|html' ], $t[ 'js' ] );
    }
    return $t;
}

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 * @since 1.2.0
 *
 */
function trailingslashit( $string )
{
    if ( empty( $string ) ) {
        return '';
    }
    return rtrim( $string, '/\\' ) . DIRECTORY_SEPARATOR;
}

/**
 * Build Unique ID for storage and retrieval.
 *
 * The old way to serialize the callback caused issues and this function is the
 * solution. It works by checking for objects and creating a new property in
 * the class to keep track of the object and new objects of the same class that
 * need to be added.
 *
 * It also allows for the removal of actions and filters for objects after they
 * change class properties. It is possible to include the property $wp_filter_id
 * in your class and set it to "null" or a number to bypass the workaround.
 * However this will prevent you from adding new classes and any new classes
 * will overwrite the previous hook by the same class.
 *
 * Functions and static method callbacks are just returned as strings and
 * shouldn't have any speed penalty.
 *
 * @link https://core.trac.wordpress.org/ticket/3875
 *
 * @since 2.2.3
 * @access private
 *
 * @global array $wp_filter Storage for all of the filters and actions.
 * @staticvar int $filter_id_count
 *
 * @param string $tag Used in counting how many hooks were applied
 * @param callable $function Used for creating unique id
 * @param int|bool $priority Used in counting how many hooks were applied. If === false
 *                           and $function is an object reference, we return the unique
 *                           id only if it already has one, false otherwise.
 * @return string|false Unique ID for usage as array key or false if $priority === false
 *                      and $function is an object reference, and it does not already have
 *                      a unique id.
 */
function _wp_filter_build_unique_id( $tag, $function, $priority )
{
    global $wp_filter;
    static $filter_id_count = 0;

    if ( is_string( $function ) ) {
        return $function;
    }

    if ( is_object( $function ) ) {
        // Closures are currently implemented as objects
        $function = array( $function, '' );
    }
    else {
        $function = (array)$function;
    }

    if ( is_object( $function[ 0 ] ) ) {
        // Object Class Calling
        if ( function_exists( 'spl_object_hash' ) ) {
            return spl_object_hash( $function[ 0 ] ) . $function[ 1 ];
        }
        else {
            $obj_idx = get_class( $function[ 0 ] ) . $function[ 1 ];
            if ( !isset( $function[ 0 ]->wp_filter_id ) ) {
                if ( false === $priority ) {
                    return false;
                }
                $obj_idx .= isset( $wp_filter[ $tag ][ $priority ] ) ? count( (array)$wp_filter[ $tag ][ $priority ] ) : $filter_id_count;
                $function[ 0 ]->wp_filter_id = $filter_id_count;
                ++$filter_id_count;
            }
            else {
                $obj_idx .= $function[ 0 ]->wp_filter_id;
            }

            return $obj_idx;
        }
    }
    elseif ( is_string( $function[ 0 ] ) ) {
        // Static Calling
        return $function[ 0 ] . '::' . $function[ 1 ];
    }
    return false;
}
/**
 * Call the 'all' hook, which will process the functions hooked into it.
 *
 * The 'all' hook passes all of the arguments or parameters that were used for
 * the hook, which this function was called for.
 *
 * This function is used internally for apply_filters(), do_action(), and
 * do_action_ref_array() and is not meant to be used from outside those
 * functions. This function does not check for the existence of the all hook, so
 * it will fail unless the all hook exists prior to this function call.
 *
 * @since 2.5.0
 * @access private
 *
 * @global array $wp_filter  Stores all of the filters
 *
 * @param array $args The collected parameters from the hook that was called.
 */
function _wp_call_all_hook( $args ) {
    global $wp_filter;

    $wp_filter['all']->do_all_hook( $args );
}
/*
 * =====================================================================================================================
 * =====================================================================================================================
 */
/**
 * Triggers all events registered to:
 *      app/backend/head
 *      app/frontend/head
 */
function app_head()
{
    global $request;
    if ( $request->isAdmin ) {
//        do_action( 'app/admin/head' );
    }
    else {
        do_action( 'app/frontend/head', 'head' );
    }
}

/**
 * Triggers all events registered to:
 *      app/backend/footer
 *      app/frontend/footer
 */
function app_footer()
{
    global $request;
    if ( $request->isAdmin ) {
//        do_action( 'app/admin/footer' );
    }
    else {
        do_action( 'app/frontend/footer', 'footer' );
    }
}

//#!<editor-fold desc=":: [WP] HOOKS ::">
/**
 * Hook a function or method to a specific filter action.
 *
 * WordPress offers filter hooks to allow plugins to modify
 * various types of internal data at runtime.
 *
 * A plugin can modify data by binding a callback to a filter hook. When the filter
 * is later applied, each bound callback is run in order of priority, and given
 * the opportunity to modify a value by returning a new value.
 *
 * The following example shows how a callback function is bound to a filter hook.
 *
 * Note that `$example` is passed to the callback, (maybe) modified, then returned:
 *
 *     function example_callback( $example ) {
 *         // Maybe modify $example in some way.
 *         return $example;
 *     }
 *     add_filter( 'example_filter', 'example_callback' );
 *
 * Bound callbacks can accept from none to the total number of arguments passed as parameters
 * in the corresponding apply_filters() call.
 *
 * In other words, if an apply_filters() call passes four total arguments, callbacks bound to
 * it can accept none (the same as 1) of the arguments or up to four. The important part is that
 * the `$accepted_args` value must reflect the number of arguments the bound callback *actually*
 * opted to accept. If no arguments were accepted by the callback that is considered to be the
 * same as accepting 1 argument. For example:
 *
 *     // Filter call.
 *     $value = apply_filters( 'hook', $value, $arg2, $arg3 );
 *
 *     // Accepting zero/one arguments.
 *     function example_callback() {
 *         ...
 *         return 'some value';
 *     }
 *     add_filter( 'hook', 'example_callback' ); // Where $priority is default 10, $accepted_args is default 1.
 *
 *     // Accepting two arguments (three possible).
 *     function example_callback( $value, $arg2 ) {
 *         ...
 *         return $maybe_modified_value;
 *     }
 *     add_filter( 'hook', 'example_callback', 10, 2 ); // Where $priority is 10, $accepted_args is 2.
 *
 * *Note:* The function will return true whether or not the callback is valid.
 * It is up to you to take care. This is done for optimization purposes, so
 * everything is as quick as possible.
 *
 * @since 0.71
 *
 * @global array $wp_filter      A multidimensional array of all hooks and the callbacks hooked to them.
 *
 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
 * @param callable $function_to_add The callback to be run when the filter is applied.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true
 */
function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
    global $wp_filter;
    if ( ! isset( $wp_filter[ $tag ] ) ) {
        $wp_filter[ $tag ] = new WP_Hook();
    }
    $wp_filter[ $tag ]->add_filter( $tag, $function_to_add, $priority, $accepted_args );
    return true;
}

/**
 * Check if any filter has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @global array $wp_filter Stores all of the filters.
 *
 * @param string        $tag               The name of the filter hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                   anything registered. When checking a specific function, the priority of that
 *                   hook is returned, or false if the function is not attached. When using the
 *                   $function_to_check argument, this function may return a non-boolean value
 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                   return value.
 */
function has_filter( $tag, $function_to_check = false ) {
    global $wp_filter;

    if ( ! isset( $wp_filter[ $tag ] ) ) {
        return false;
    }

    return $wp_filter[ $tag ]->has_filter( $tag, $function_to_check );
}

/**
 * Call the functions added to a filter hook.
 *
 * The callback functions attached to filter hook $tag are invoked by calling
 * this function. This function can be used to create a new filter hook by
 * simply calling this function with the name of the new hook specified using
 * the $tag parameter.
 *
 * The function allows for additional arguments to be added and passed to hooks.
 *
 *     // Our filter callback function
 *     function example_callback( $string, $arg1, $arg2 ) {
 *         // (maybe) modify $string
 *         return $string;
 *     }
 *     add_filter( 'example_filter', 'example_callback', 10, 3 );
 *
 *     /*
 *      * Apply the filters by calling the 'example_callback' function we
 *      * "hooked" to 'example_filter' using the add_filter() function above.
 *      * - 'example_filter' is the filter hook $tag
 *      * - 'filter me' is the value being filtered
 *      * - $arg1 and $arg2 are the additional arguments passed to the callback.
 *     $value = apply_filters( 'example_filter', 'filter me', $arg1, $arg2 );
 *
 * @since 0.71
 *
 * @global array $wp_filter         Stores all of the filters.
 * @global array $wp_current_filter Stores the list of current filters with the current one last.
 *
 * @param string $tag     The name of the filter hook.
 * @param mixed  $value   The value on which the filters hooked to `$tag` are applied on.
 * @param mixed  $var,... Additional variables passed to the functions hooked to `$tag`.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters( $tag, $value ) {
    global $wp_filter, $wp_current_filter;

    $args = array();

    // Do 'all' actions first.
    if ( isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
        $args                = func_get_args();
        _wp_call_all_hook( $args );
    }

    if ( ! isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter['all'] ) ) {
            array_pop( $wp_current_filter );
        }
        return $value;
    }

    if ( ! isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
    }

    if ( empty( $args ) ) {
        $args = func_get_args();
    }

    // don't pass the tag name to WP_Hook
    array_shift( $args );

    $filtered = $wp_filter[ $tag ]->apply_filters( $value, $args );

    array_pop( $wp_current_filter );

    return $filtered;
}

/**
 * Execute functions hooked on a specific filter hook, specifying arguments in an array.
 *
 * @since 3.0.0
 *
 * @see apply_filters() This function is identical, but the arguments passed to the
 * functions hooked to `$tag` are supplied using an array.
 *
 * @global array $wp_filter         Stores all of the filters
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the filter hook.
 * @param array  $args The arguments supplied to the functions hooked to $tag.
 * @return mixed The filtered value after all hooked functions are applied to it.
 */
function apply_filters_ref_array( $tag, $args ) {
    global $wp_filter, $wp_current_filter;

    // Do 'all' actions first
    if ( isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
        $all_args            = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( ! isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter['all'] ) ) {
            array_pop( $wp_current_filter );
        }
        return $args[0];
    }

    if ( ! isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
    }

    $filtered = $wp_filter[ $tag ]->apply_filters( $args[0], $args );

    array_pop( $wp_current_filter );

    return $filtered;
}

/**
 * Removes a function from a specified filter hook.
 *
 * This function removes a function attached to a specified filter hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * To remove a hook, the $function_to_remove and $priority arguments must match
 * when the hook was added. This goes for both filters and actions. No warning
 * will be given on removal failure.
 *
 * @since 1.2.0
 *
 * @global array $wp_filter         Stores all of the filters
 *
 * @param string   $tag                The filter hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool    Whether the function existed before it was removed.
 */
function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
    global $wp_filter;

    $r = false;
    if ( isset( $wp_filter[ $tag ] ) ) {
        $r = $wp_filter[ $tag ]->remove_filter( $tag, $function_to_remove, $priority );
        if ( ! $wp_filter[ $tag ]->callbacks ) {
            unset( $wp_filter[ $tag ] );
        }
    }

    return $r;
}

/**
 * Remove all of the hooks from a filter.
 *
 * @since 2.7.0
 *
 * @global array $wp_filter  Stores all of the filters
 *
 * @param string   $tag      The filter to remove hooks from.
 * @param int|bool $priority Optional. The priority number to remove. Default false.
 * @return true True when finished.
 */
function remove_all_filters( $tag, $priority = false ) {
    global $wp_filter;

    if ( isset( $wp_filter[ $tag ] ) ) {
        $wp_filter[ $tag ]->remove_all_filters( $priority );
        if ( ! $wp_filter[ $tag ]->has_filters() ) {
            unset( $wp_filter[ $tag ] );
        }
    }

    return true;
}

/**
 * Retrieve the name of the current filter or action.
 *
 * @since 2.5.0
 *
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @return string Hook name of the current filter or action.
 */
function current_filter() {
    global $wp_current_filter;
    return end( $wp_current_filter );
}

/**
 * Retrieve the name of the current action.
 *
 * @since 3.9.0
 *
 * @return string Hook name of the current action.
 */
function current_action() {
    return current_filter();
}

/**
 * Retrieve the name of a filter currently being processed.
 *
 * The function current_filter() only returns the most recent filter or action
 * being executed. did_action() returns true once the action is initially
 * processed.
 *
 * This function allows detection for any filter currently being
 * executed (despite not being the most recent filter to fire, in the case of
 * hooks called from hook callbacks) to be verified.
 *
 * @since 3.9.0
 *
 * @see current_filter()
 * @see did_action()
 * @global array $wp_current_filter Current filter.
 *
 * @param null|string $filter Optional. Filter to check. Defaults to null, which
 *                            checks if any filter is currently being run.
 * @return bool Whether the filter is currently in the stack.
 */
function doing_filter( $filter = null ) {
    global $wp_current_filter;

    if ( null === $filter ) {
        return ! empty( $wp_current_filter );
    }

    return in_array( $filter, $wp_current_filter );
}

/**
 * Retrieve the name of an action currently being processed.
 *
 * @since 3.9.0
 *
 * @param string|null $action Optional. Action to check. Defaults to null, which checks
 *                            if any action is currently being run.
 * @return bool Whether the action is currently in the stack.
 */
function doing_action( $action = null ) {
    return doing_filter( $action );
}

/**
 * Hooks a function on to a specific action.
 *
 * Actions are the hooks that the WordPress core launches at specific points
 * during execution, or when specific events occur. Plugins can specify that
 * one or more of its PHP functions are executed at these points, using the
 * Action API.
 *
 * @since 1.2.0
 *
 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
 * @param callable $function_to_add The name of the function you wish to be called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed. Default 10.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Will always return true.
 */
function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
    return add_filter( $tag, $function_to_add, $priority, $accepted_args );
}

/**
 * Execute functions hooked on a specific action hook.
 *
 * This function invokes all functions attached to action hook `$tag`. It is
 * possible to create new action hooks by simply calling this function,
 * specifying the name of the new hook using the `$tag` parameter.
 *
 * You can pass extra arguments to the hooks, much like you can with apply_filters().
 *
 * @since 1.2.0
 *
 * @global array $wp_filter         Stores all of the filters
 * @global array $wp_actions        Increments the amount of times action was triggered.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag     The name of the action to be executed.
 * @param mixed  $arg,... Optional. Additional arguments which are passed on to the
 *                        functions hooked to the action. Default empty.
 */
function do_action( $tag, $arg = '' ) {
    global $wp_filter, $wp_actions, $wp_current_filter;

    if ( ! isset( $wp_actions[ $tag ] ) ) {
        $wp_actions[ $tag ] = 1;
    } else {
        ++$wp_actions[ $tag ];
    }

    // Do 'all' actions first
    if ( isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
        $all_args            = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( ! isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter['all'] ) ) {
            array_pop( $wp_current_filter );
        }
        return;
    }

    if ( ! isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
    }

    $args = array();
    if ( is_array( $arg ) && 1 == count( $arg ) && isset( $arg[0] ) && is_object( $arg[0] ) ) { // array(&$this)
        $args[] =& $arg[0];
    } else {
        $args[] = $arg;
    }
    for ( $a = 2, $num = func_num_args(); $a < $num; $a++ ) {
        $args[] = func_get_arg( $a );
    }

    $wp_filter[ $tag ]->do_action( $args );

    array_pop( $wp_current_filter );
}

/**
 * Retrieve the number of times an action is fired.
 *
 * @since 2.1.0
 *
 * @global array $wp_actions Increments the amount of times action was triggered.
 *
 * @param string $tag The name of the action hook.
 * @return int The number of times action hook $tag is fired.
 */
function did_action( $tag ) {
    global $wp_actions;

    if ( ! isset( $wp_actions[ $tag ] ) ) {
        return 0;
    }

    return $wp_actions[ $tag ];
}

/**
 * Execute functions hooked on a specific action hook, specifying arguments in an array.
 *
 * @since 2.1.0
 *
 * @see do_action() This function is identical, but the arguments passed to the
 *                  functions hooked to $tag< are supplied using an array.
 * @global array $wp_filter         Stores all of the filters
 * @global array $wp_actions        Increments the amount of times action was triggered.
 * @global array $wp_current_filter Stores the list of current filters with the current one last
 *
 * @param string $tag  The name of the action to be executed.
 * @param array  $args The arguments supplied to the functions hooked to `$tag`.
 */
function do_action_ref_array( $tag, $args ) {
    global $wp_filter, $wp_actions, $wp_current_filter;

    if ( ! isset( $wp_actions[ $tag ] ) ) {
        $wp_actions[ $tag ] = 1;
    } else {
        ++$wp_actions[ $tag ];
    }

    // Do 'all' actions first
    if ( isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
        $all_args            = func_get_args();
        _wp_call_all_hook( $all_args );
    }

    if ( ! isset( $wp_filter[ $tag ] ) ) {
        if ( isset( $wp_filter['all'] ) ) {
            array_pop( $wp_current_filter );
        }
        return;
    }

    if ( ! isset( $wp_filter['all'] ) ) {
        $wp_current_filter[] = $tag;
    }

    $wp_filter[ $tag ]->do_action( $args );

    array_pop( $wp_current_filter );
}

/**
 * Check if any action has been registered for a hook.
 *
 * @since 2.5.0
 *
 * @see has_filter() has_action() is an alias of has_filter().
 *
 * @param string        $tag               The name of the action hook.
 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
 * @return bool|int If $function_to_check is omitted, returns boolean for whether the hook has
 *                  anything registered. When checking a specific function, the priority of that
 *                  hook is returned, or false if the function is not attached. When using the
 *                  $function_to_check argument, this function may return a non-boolean value
 *                  that evaluates to false (e.g.) 0, so use the === operator for testing the
 *                  return value.
 */
function has_action( $tag, $function_to_check = false ) {
    return has_filter( $tag, $function_to_check );
}

/**
 * Removes a function from a specified action hook.
 *
 * This function removes a function attached to a specified action hook. This
 * method can be used to remove default functions attached to a specific filter
 * hook and possibly replace them with a substitute.
 *
 * @since 1.2.0
 *
 * @param string   $tag                The action hook to which the function to be removed is hooked.
 * @param callable $function_to_remove The name of the function which should be removed.
 * @param int      $priority           Optional. The priority of the function. Default 10.
 * @return bool Whether the function is removed.
 */
function remove_action( $tag, $function_to_remove, $priority = 10 ) {
    return remove_filter( $tag, $function_to_remove, $priority );
}

/**
 * Remove all of the hooks from an action.
 *
 * @since 2.7.0
 *
 * @param string   $tag      The action to remove hooks from.
 * @param int|bool $priority The priority number to remove them from. Default false.
 * @return true True when finished.
 */
function remove_all_actions( $tag, $priority = false ) {
    return remove_all_filters( $tag, $priority );
}

/**
 * Fires functions attached to a deprecated filter hook.
 *
 * When a filter hook is deprecated, the apply_filters() call is replaced with
 * apply_filters_deprecated(), which triggers a deprecation notice and then fires
 * the original filter hook.
 *
 * Note: the value and extra arguments passed to the original apply_filters() call
 * must be passed here to `$args` as an array. For example:
 *
 *     // Old filter.
 *     return apply_filters( 'wpdocs_filter', $value, $extra_arg );
 *
 *     // Deprecated.
 *     return apply_filters_deprecated( 'wpdocs_filter', array( $value, $extra_arg ), '4.9', 'wpdocs_new_filter' );
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $tag         The name of the filter hook.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used. Default false.
 * @param string $message     Optional. A message regarding the change. Default null.
 */
function apply_filters_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
    if ( ! has_filter( $tag ) ) {
        return $args[0];
    }
    return apply_filters_ref_array( $tag, $args );
}

/**
 * Fires functions attached to a deprecated action hook.
 *
 * When an action hook is deprecated, the do_action() call is replaced with
 * do_action_deprecated(), which triggers a deprecation notice and then fires
 * the original hook.
 *
 * @since 4.6.0
 *
 * @see _deprecated_hook()
 *
 * @param string $tag         The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of WordPress that deprecated the hook.
 * @param string $replacement Optional. The hook that should have been used.
 * @param string $message     Optional. A message regarding the change.
 */
function do_action_deprecated( $tag, $args, $version, $replacement = false, $message = null ) {
    if ( ! has_action( $tag ) ) {
        return;
    }
    do_action_ref_array( $tag, $args );
}



//#!</editor-fold desc=":: [WP] HOOKS ::">
