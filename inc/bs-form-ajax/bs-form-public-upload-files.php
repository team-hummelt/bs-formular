<?php
defined('ABSPATH') or die();
/**
 * ADMIN AJAX
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 */

$responseJson = new stdClass();
$record = new stdClass();
$record->id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);

if (!$record->id) {
    $msg = apply_filters('bs_form_default_settings', 'by_field', 'error_message');
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

if ($_POST) {
   $upload_file = BsFormularUploadHandle::instance();
   $responseJson = $upload_file->initial();
}