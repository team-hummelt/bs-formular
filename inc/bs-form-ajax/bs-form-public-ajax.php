<?php
defined('ABSPATH') or die();
/**
 * ADMIN AJAX
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

$responseJson = new stdClass();
$record = new stdClass();

$record->id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
$record->formId = filter_input(INPUT_POST, 'formId', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
isset( $_POST['terms'] ) && is_string( $_POST['terms'] ) ? $record->terms = 1 : $record->terms = 0;

isset($_POST['dscheck']) && is_string($_POST['dscheck']) ? $record->dscheck = 1 : $record->dscheck = 0;
$_POST['repeat_email'] ? $record->repeat_email = $_POST['repeat_email'] : $record->repeat_email = false;

if (!$record->id) {
    $msg = apply_filters('bs_form_default_settings', 'by_field', 'error_message');
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

if ($record->terms || $record->repeat_email) {
    $msg = apply_filters('bs_form_default_settings', 'by_field', 'spam');
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->spam;

    return $responseJson;
}

global $wpdb;

$table = $wpdb->prefix . 'bs_formulare';
$args = sprintf('WHERE %s.shortcode="%s"', $table, $record->id);

$formular = apply_filters('get_formulare_by_args', $args, false, 'id');
if (!$formular->status) {
    $msg = apply_filters('bs_form_default_settings', 'by_field', 'error_message');
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

$args = sprintf('WHERE %s.shortcode="%s"', $table, $record->id);

$form = apply_filters('bs_form_formular_data_by_join', $args, false);

if (!$form->status) {
    $msg = apply_filters('bs_form_default_settings', 'by_field', 'error_message');
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}


$argsData = new stdClass();
$argsData->shortcode = $record->id;
$argsData->where = sprintf('WHERE shortcode="%s"', $record->id);
$send_arr = array();

foreach ($_POST as $key => $val) {
    $argsData->id = $key;
    $result = apply_filters('get_formular_inputs_by_id', $argsData);

    if (!$result->status) {
        continue;
    }
    $validate = apply_filters('bs_formular_validate_message_inputs', $result->record, $val, $form);

    if (!$validate->status) {
        $responseJson->status = false;
        $responseJson->msg = $validate->msg;
        $responseJson->show_error = true;
        $responseJson->formId = $record->formId;

        return $responseJson;
    }
    $send_arr[] = $validate;
}

$form = $form->record;
$inputs = unserialize($form->inputs);

foreach ($inputs as $tmp) {
    if ($tmp->type == 'checkbox' || $tmp->type == 'radio') {
        $valCR = apply_filters('validate_formular_radio_checkbox', $_POST, $tmp, $tmp->type);
        if ($valCR->is_check) {
            continue;
        }

        if (!$valCR->status) {
            $responseJson->status = false;
            $responseJson->msg = $valCR->msg;
            $responseJson->show_error = true;
            $responseJson->formId = $record->formId;

            return $responseJson;
        }
        $send_arr[] = $valCR;
    }
}

$message = htmlspecialchars_decode($form->message);
$message = stripslashes_deep($message);
$message = str_replace('<span class="remove">&nbsp;</span>', ' ', $message);
$sendSelectMail = false;
foreach ($send_arr as $tmp) {
    if ($tmp->type == 'email-send-select') {
        $sendSelectMail = $tmp->eingabe;
        continue;
    }
    $errMsg = apply_filters('bs_form_default_settings', 'by_field', 'error_message');
    $tmp->eingabe ? $eingabe = $tmp->eingabe : $eingabe = $errMsg->error_message;
    $ausgabe = '<b style="font-size: 16px;">Eingabe:</b><br /> <b>' . $tmp->label . ':</b> ' . $eingabe . '<br /><br /><hr />';
    $message = apply_filters('string_replace_limit', $tmp->user_value, $ausgabe, $message, $limit = 1);
}

$sendMsg = '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px;color:#5b5b5b">';
$sendMsg .= $message;
$sendMsg .= '</div>';

$regExp = '@\[.*?]@m';
preg_match_all($regExp, $sendMsg, $matches, PREG_SET_ORDER, 0);
if ($matches) {
    foreach ($matches as $tmp) {
        if (isset($tmp[0])) {
            $sendMsg = str_replace($tmp[0], '', $sendMsg);
        }
    }
}

$absenderName = get_option('email_abs_name');
$dbAbsenderEmail = get_option('bs_abs_email');
if (!$dbAbsenderEmail) {
    $dbAbsenderEmail = get_bloginfo('admin_email');
}

$subject = $form->betreff;
$sendSelectMail ? $to = $sendSelectMail : $to = $form->email_at;
//$to      = $form->email_at;

$headers[] = 'From: ' . $absenderName . '  <' . $dbAbsenderEmail . '>';

$email_cc = $form->email_cc;
$cc = '';
if ($email_cc) {
    $email_cc = str_replace(array(',', ';', ' '), array('#', '#', ''), $email_cc);
    $cc = explode("#", $email_cc);
}

$mailToDb = [];
if ($cc) {
    foreach ($cc as $tmp) {
        if (filter_var($tmp, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Cc: ' . $tmp . ' <' . $tmp . '>';
            $mailToDb[] = $tmp;
        }
    }
}


$files = filter_input(INPUT_POST, 'files_id', FILTER_SANITIZE_STRING);
$attachments = [];
if($files) {
    $dir = BS_FILE_UPLOAD_DIR . $files . DIRECTORY_SEPARATOR;
    $scanned = array_diff(scandir($dir), array('..', '.'));
    if (is_dir($dir)) {
        foreach ($scanned as $tmp) {
            if (is_file($dir . $tmp)) {
                  $attachments[] = $dir . $tmp;
            }
        }
    }
}

//print_r($attachments);


$send = wp_mail( $to, $subject ?: get_bloginfo( 'title' ), $sendMsg, array_unique( $headers ), $attachments );

if ( ! $send ) {
    $msg  = apply_filters( 'bs_form_default_settings', 'by_field', 'error_message' );
    $responseJson->status     = false;
    $responseJson->msg        = $msg->error_message;
    $responseJson->show_error = true;
    $responseJson->formId     = $record->formId;

    return $responseJson;
}

do_action('bs_form_destroy_dir', BS_FILE_UPLOAD_DIR . $files . DIRECTORY_SEPARATOR);

if (get_option('email_empfang_aktiv')) {
    $mailToDb[] = $to;
    $safeDb = new stdClass();
    $safeDb->email_at = implode(' ,', array_unique($mailToDb));
    $safeDb->betreff = $form->betreff;
    $safeDb->form_id = $form->id;
    $safeDb->abs_ip = $_SERVER['REMOTE_ADDR'];
    $safeDb->message = esc_html($sendMsg);
    apply_filters('set_email_empfang_table', $safeDb);
}


$msg = apply_filters('bs_form_default_settings', 'by_field', 'success_message');
$responseJson->status = true;
$responseJson->show_success = true;
$responseJson->msg = $msg->success_message;
$responseJson->formId = $record->formId;