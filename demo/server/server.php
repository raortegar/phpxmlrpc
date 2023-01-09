<?php
/**
 * Demo server for phpxmlrpc library.
 *
 * Implements a lot of webservices, including a suite of services used for interoperability testing (validator1 and
 * interopEchoTests methods), and some whose only purpose is to be used for testing the library.
 * It also allows the caller to configure specific server features by using "out of band" query string parameters when
 * in test mode.
 *
 * Please _do not_ copy this file verbatim into your production server.
 */

require_once __DIR__ . "/_prepend.php";

use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Response;
use PhpXmlRpc\Server;
use PhpXmlRpc\Value;

// Most of the code used to implement the webservices, and their signatures, are stowed away in neatly organized files,
// each demoing a different topic

// The simplest way of implementing webservices: as xml-rpc-aware global functions
$signatures1 = include(__DIR__.'/methodProviders/functions.php');

// Definitions of webservices used for interoperability testing
$signatures2 = include(__DIR__.'/methodProviders/interop.php');
$signatures3 = include(__DIR__.'/methodProviders/validator1.php');

// And finally a few examples inline
/// @todo bring back a few, basic examples here
$signatures = array();

$signatures = array_merge($signatures, $signatures1, $signatures2, $signatures3);

if (defined('TESTMODE')) {
    // Webservices used only by the testsuite - do not use them in production
    $signatures4 = include(__DIR__.'/methodProviders/testsuite.php');
    $signatures5 = include(__DIR__.'/methodProviders/wrapper.php');

    $signatures = array_merge($signatures, $signatures4, $signatures5);
}

// Enable support for the NULL extension
PhpXmlRpc::$xmlrpc_null_extension = true;

$s = new Server($signatures, false);
$s->setDebug(3);

// Out-of-band information: let the client manipulate the server operations.
// We do this to help the testsuite script: do not reproduce in production!
if (defined('TESTMODE')) {
    if (isset($_GET['RESPONSE_ENCODING'])) {
        $s->response_charset_encoding = $_GET['RESPONSE_ENCODING'];
    }
    if (isset($_GET['DETECT_ENCODINGS'])) {
        PhpXmlRpc::$xmlrpc_detectencodings = $_GET['DETECT_ENCODINGS'];
    }
    if (isset($_GET['EXCEPTION_HANDLING'])) {
        $s->exception_handling = $_GET['EXCEPTION_HANDLING'];
    }
    if (isset($_GET['FORCE_AUTH'])) {
        // We implement both  Basic and Digest auth in php to avoid having to set it up in a vhost.
        // Code taken from php.net
        // NB: we do NOT check for valid credentials!
        if ($_GET['FORCE_AUTH'] == 'Basic') {
            if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['REMOTE_USER']) && !isset($_SERVER['REDIRECT_REMOTE_USER'])) {
                header('HTTP/1.0 401 Unauthorized');
                header('WWW-Authenticate: Basic realm="Phpxmlrpc Basic Realm"');
                die('Text visible if user hits Cancel button');
            }
        } elseif ($_GET['FORCE_AUTH'] == 'Digest') {
            if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
                header('HTTP/1.1 401 Unauthorized');
                header('WWW-Authenticate: Digest realm="Phpxmlrpc Digest Realm",qop="auth",nonce="' . uniqid() . '",opaque="' . md5('Phpxmlrpc Digest Realm') . '"');
                die('Text visible if user hits Cancel button');
            }
        }
    }
}

$s->service();
// That should do all we need!
