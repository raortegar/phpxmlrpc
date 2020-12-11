<?php

/**
 * Common parameter parsing for benchmark and tests scripts.
 *
 * @param integer DEBUG
 * @param string  LOCALSERVER
 * @param string  URI
 * @param string  HTTPSSERVER
 * @param string  HTTPSURI
 * @param bool    HTTPSIGNOREPEER
 * @param int     HTTPSVERIFYHOST
 * @param int     SSLVERSION
 * @param string  PROXYSERVER
 *
 * @copyright (C) 2007-2020 G. Giunta
 * @license code licensed under the BSD License: see file license.txt
 **/
class argParser
{
    public static function getArgs()
    {
        $args = array(
            'DEBUG' => 0,
            'LOCALSERVER' => 'localhost',
            'HTTPSSERVER' => 'gggeek.altervista.org',
            'HTTPSURI' => '/sw/xmlrpc/demo/server/server.php',
            'HTTPSIGNOREPEER' => false,
            'HTTPSVERIFYHOST' => 2,
            'SSLVERSION' => 0,
            'PROXYSERVER' => null,
            'LOCALPATH' => __DIR__,
        );

        // check for command line (env vars) vs. web page input params
        if (!isset($_SERVER['REQUEST_METHOD'])) {
            foreach($_SERVER as $key => $val) {
                if (array_key_exists($key, $args)) {
                    $$key = $val;
                }
            }
        } else {
            // NB: we might as well consider using $_GET stuff later on...
            extract($_GET);
            extract($_POST);
        }

        if (isset($DEBUG)) {
            $args['DEBUG'] = intval($DEBUG);
        }
        if (isset($LOCALSERVER)) {
            $args['LOCALSERVER'] = $LOCALSERVER;
        } else {
            if (isset($HTTP_HOST)) {
                $args['LOCALSERVER'] = $HTTP_HOST;
            } elseif (isset($_SERVER['HTTP_HOST'])) {
                $args['LOCALSERVER'] = $_SERVER['HTTP_HOST'];
            }
        }
        if (isset($HTTPSSERVER)) {
            $args['HTTPSSERVER'] = $HTTPSSERVER;
        }
        if (isset($HTTPSURI)) {
            $args['HTTPSURI'] = $HTTPSURI;
        }
        if (isset($HTTPSIGNOREPEER)) {
            $args['HTTPSIGNOREPEER'] = (bool)$HTTPSIGNOREPEER;
        }
        if (isset($HTTPSVERIFYHOST)) {
            $args['HTTPSVERIFYHOST'] = (int)$HTTPSVERIFYHOST;
        }
        if (isset($SSLVERSION)) {
            $args['SSLVERSION'] = (int)$SSLVERSION;
        }
        if (isset($PROXYSERVER)) {
            $arr = explode(':', $PROXYSERVER);
            $args['PROXYSERVER'] = $arr[0];
            if (count($arr) > 1) {
                $args['PROXYPORT'] = $arr[1];
            } else {
                $args['PROXYPORT'] = 8080;
            }
        }
        if (!isset($URI)) {
            // GUESTIMATE the url of local demo server
            // play nice to php 3 and 4-5 in retrieving URL of server.php
            /// @todo filter out query string from REQUEST_URI
            if (isset($REQUEST_URI)) {
                $URI = str_replace('/tests/testsuite.php', '/demo/server/server.php', $REQUEST_URI);
                $URI = str_replace('/testsuite.php', '/server.php', $URI);
                $URI = str_replace('/tests/benchmark.php', '/demo/server/server.php', $URI);
                $URI = str_replace('/benchmark.php', '/server.php', $URI);
            } elseif (isset($_SERVER['PHP_SELF']) && isset($_SERVER['REQUEST_METHOD'])) {
                $URI = str_replace('/tests/testsuite.php', '/demo/server/server.php', $_SERVER['PHP_SELF']);
                $URI = str_replace('/testsuite.php', '/server.php', $URI);
                $URI = str_replace('/tests/benchmark.php', '/demo/server/server.php', $URI);
                $URI = str_replace('/benchmark.php', '/server.php', $URI);
            } else {
                $URI = '/demo/server/server.php';
            }
        }
        if ($URI[0] != '/') {
            $URI = '/' . $URI;
        }
        $args['URI'] = $URI;
        if (isset($LOCALPATH)) {
            $args['LOCALPATH'] = $LOCALPATH;
        }

        return $args;
    }
}
