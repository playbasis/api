#!/usr/bin/env php
<?php

// Maximize error reporting
error_reporting(E_ALL | E_STRICT);

// TODO: if we could get rid of this and have composer figure things out it'd make it
// a bit more sane
require(dirname(__file__) . '/vendor/sentry/sentry/lib/Raven/Autoloader.php');
Raven_Autoloader::register();

function raven_cli_test($command, $args)
{
    // Do something silly
    try {
        throw new Exception('This is a test exception sent from the Raven CLI.');
    } catch (Exception $ex) {
        return $ex;
    }
}

function cmd_test($dsn)
{
    if (empty($dsn)) {
        exit('ERROR: Missing DSN value');
    }

    // Parse DSN as a test
    try {
        $parsed = Raven_Client::parseDSN($dsn);
    } catch (InvalidArgumentException $ex) {
        exit("ERROR: There was an error parsing your DSN:\n  " . $ex->getMessage());
    }

    $client = new Raven_Client($dsn, array(
        'trace' => true,
        'curl_method' => 'sync',
        'app_path' => realpath(__DIR__ . '/..'),
        'base_path' => realpath(__DIR__ . '/..'),
    ));

    $config = get_object_vars($client);
    $required_keys = array('server', 'project', 'public_key', 'secret_key');

    echo "Client configuration:\n";
    foreach ($required_keys as $key) {
        if (empty($config[$key])) {
            exit("ERROR: Missing configuration for $key");
        }
        if (is_array($config[$key])) {
            echo "-> $key: [".implode(", ", $config[$key])."]\n";
        } else {
            echo "-> $key: $config[$key]\n";
        }

    }
    echo "\n";

    echo "Sending a test event:\n";

    $ex = raven_cli_test("command name", array("foo" => "bar"));
    $event_id = $client->captureException($ex);

    echo "-> event ID: $event_id\n";

    $last_error = $client->getLastError();
    if (!empty($last_error)) {
        exit("ERROR: There was an error sending the test event:\n  " . $last_error);
    }

    echo "\n";
    echo "Done!";
}


function main() {
    global $argv;

    if (!isset($argv[1])) {
        exit('Usage: sentry test <dsn>');
    }

    $cmd = $argv[1];

    switch ($cmd) {
        case 'test':
            cmd_test(@$argv[2]);
            break;
        default:
            exit('Usage: sentry test <dsn>');
    }
}

main();
