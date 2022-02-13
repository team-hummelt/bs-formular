<?php
defined( 'ABSPATH' ) or die();

/**
 * BS-Formular OPTIONEN
 * @package Hummelt & Partner WordPress Plugin
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */


if(!get_option('bs_formular_user_role')){
    update_option('bs_formular_user_role', 'manage_options');
}

//TODO INSTALL THEME DATABASE
require  'filter/bs-formular-filter.php';
require 'shortcode/bs-formular-shortcode.php';
require 'metabox/classic/bs-formular-widget.php';
//TODO SMTP TEST
require 'Mailer/smtp-test.php';
//TODO GET PAGE META DATA
add_filter('bs_form_get_smtp_test', 'bs_formular_load_smtp_test');
//TODO FILE UPLOADER
require 'filter/bs-email-upload-handle.php';