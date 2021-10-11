<?php

defined('ABSPATH') or die();
/**
 * BS-FORMULAR SMTP Chack
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

require 'vendor/autoload.php';

use JetBrains\PhpStorm\ArrayShape;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

#[ArrayShape( [ "status" => "bool", "msg" => "string" ] )] function bs_formular_load_smtp_test():array
{
    $status = false;
    $msg = '';
    $smtp = new SMTP;
    try {
        //Connect to an SMTP server
        if (!$smtp->connect(get_option('bs_form_smtp_host'), get_option('bs_form_smtp_port'))) {
            throw new Exception('Connect failed');
        }
        //Say hello
        if (!$smtp->hello(gethostname())) {
            throw new Exception('EHLO failed: ' . $smtp->getError()['error']);
        }
        $e = $smtp->getServerExtList();
        if (is_array($e) && array_key_exists('STARTTLS', $e)) {
            $tlsok = $smtp->startTLS();
            if (!$tlsok) {
                throw new Exception('Failed to start encryption: ' . $smtp->getError()['error']);
            }
            if (!$smtp->hello(gethostname())) {
                throw new Exception('EHLO (2) failed: ' . $smtp->getError()['error']);
            }
            $e = $smtp->getServerExtList();
        }
        if (is_array($e) && array_key_exists('AUTH', $e)) {
            if ($smtp->authenticate(get_option('bs_form_email_benutzer'), get_option('bs_form_email_passwort'))) {
                $msg .= "Connected ok!";
                $status = true;
            } else {
                throw new Exception('Authentication failed: ' . $smtp->getError()['error']);
            }
        }
    } catch (exception $e) {
        $msg .= 'SMTP error: ' . $e->getMessage() . "\n";
    }
    $smtp->quit(true);
    return array("status" => $status, "msg" => $msg);
}
