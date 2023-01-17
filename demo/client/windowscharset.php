<?php
require_once __DIR__ . "/_prepend.php";

/**
 * Demoing the charset conversion of the library: create a client class which uses data in the CP-1252 character set,
 * regardless of the character set used by the server
 */

use PhpXmlRpc\Client;
use PhpXmlRpc\PhpXmlRpc;
use PhpXmlRpc\Request;
use PhpXmlRpc\Value;

if (!function_exists('mb_convert_encoding')) {
    die('This demo requires mbstring support');
}

PhpXmlRpc::$xmlrpc_internalencoding = 'Windows-1252';

// this is a very contrived way of creating a CP-1252 string...
$input = 'Euro sign is €, per mille is ‰, trademark is ™, copyright is ©, smart quotes are “these”';
//var_dump(mb_list_encodings());
echo "This is the value we start with (in UTF-8): ";
var_dump($input);

$input = mb_convert_encoding($input, 'Windows-1252', 'UTF-8');

echo "In CP-1252, it looks like this: ";
var_dump($input);

$c = new Client(XMLRPCSERVER);

// allow the full request and response to be seen on screen
$c->setDebug(2);
// tell the server not to compress the response - this is not necessary btw, it is only done to make the debug look nicer
$c->accepted_compression = array();
// tell the server not to encode everything as ASCII - this is not necessary btw, it is only done to make the debug look nicer
$c->accepted_charset_encodings = array('UTF-8');
// force the client not to encode everything as ASCII - this is not necessary btw, it is only done to make the debug nicer
$c->request_charset_encoding = 'UTF-8';

$r = $c->send(new Request('examples.stringecho', array(new Value($input))));
$output = $r->value()->scalarval();

echo "This is the value we got back from the server (in CP-1252): ";
var_dump($output);

echo "In UTF-8, it looks like this: ";
var_dump(mb_convert_encoding($output, 'UTF-8', 'Windows-1252'));
