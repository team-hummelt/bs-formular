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
global $bs_formular_filter;
$record->id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
$record->formId = filter_input(INPUT_POST, 'formId', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
isset($_POST['terms']) && is_string($_POST['terms']) ? $record->terms = 1 : $record->terms = 0;

isset($_POST['dscheck']) && is_string($_POST['dscheck']) ? $record->dscheck = 1 : $record->dscheck = 0;
$_POST['repeat_email'] ? $record->repeat_email = $_POST['repeat_email'] : $record->repeat_email = false;

if (!$record->id) {
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'error_message', true);
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

if ($record->terms || $record->repeat_email) {
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'spam', true);
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
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'error_message', true);
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

$args = sprintf('WHERE %s.shortcode="%s"', $table, $record->id);
$form = apply_filters('bs_form_formular_data_by_join', $args, false);

if (!$form->status) {
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'error_message', true);
    $responseJson->status = false;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;
    $responseJson->msg = $msg->error_message;

    return $responseJson;
}

$form->record->redirect_page && $form->record->send_redirection_data_aktiv && $form->record->redirect_aktiv ? $redirect = true : $redirect = false;

$argsData = new stdClass();
$argsData->shortcode = $record->id;
$argsData->where = sprintf('WHERE shortcode="%s"', $record->id);
$send_arr = array();
$attachments = [];

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
$message = str_replace(['<span class="remove">&nbsp;</span>'], ' ', $message);

if ($form->response_aktiv) {
    $autoResponder = htmlspecialchars_decode($form->auto_msg);
    $autoResponder = stripslashes_deep($autoResponder);
    $autoResponder = str_replace(['<span class="remove">&nbsp;</span>'], ' ', $autoResponder);
} else {
    $autoResponder = false;
}

$sendSelectMail = false;
$eingabe = '';
$reDataArr = [];

$emailResponder = '';
foreach ($send_arr as $tmp) {
    if ($tmp->type == 'email-send-select') {
        $sendSelectMail = $tmp->eingabe;
        continue;
    }
    if ($tmp->type == 'email') {
        $emailResponder = strip_tags($tmp->eingabe);
    }
    if ($tmp->type == 'file') {
        $attachments = $tmp->eingabe;
        continue;
    }
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'error_message', true);
    $tmp->eingabe ? $eingabe = $tmp->eingabe : $eingabe = '';

    if ($redirect) {
        if ($tmp->type != 'password'):
            $lbl = str_replace(['[', ']'], '', $tmp->user_value);
            $re_data_item = [
                $eingabe
            ];
            $reDataArr[] = $eingabe;
        endif;
    }
    switch ($form->email_template) {
        case '1':
            $ausgabe = '<b>' . $tmp->label . ':</b> ' . $eingabe . '<br /><br /><hr />';
            $message = apply_filters('string_replace_limit', $tmp->user_value, $ausgabe, $message, $limit = 1);
            break;
        case '2':
            $message = str_replace($tmp->user_value, $eingabe, $message);
            if ($autoResponder) {
                $autoResponder = str_replace($tmp->user_value, $eingabe, $autoResponder);
            }
            break;
        default:
    }
}

if ($reDataArr) {
    $saveRedirectData = [
        $record->id => $reDataArr
    ];
    $saveRedirectData = json_encode($saveRedirectData);
    $updData = [
        'shortcode' => $record->id,
        'redirect_data' => $saveRedirectData
    ];
    apply_filters('bs_form_update_redirect_data', (object)$updData);
}

switch ($form->email_template) {
    case '1':
        $sendMsg = '<div style="font-family: Arial, Helvetica, sans-serif; font-size: 14px;color:#5b5b5b">';
        $sendMsg .= $message;
        $sendMsg .= '</div>';
        break;
    case '2':
        $sendMsg = $message;
        break;
    default:
        $sendMsg = '';
}

$tempDir = EMAIL_TEMPLATES_DIR . 'email-default-template.html';
$htmlBody = file_get_contents($tempDir, true);
$htmlBody = str_replace('###EMAILMESSAGE###', $sendMsg, $htmlBody);
$htmlBody = str_replace('###EMAILTITLE###', $form->betreff, $htmlBody);

$regExp = '@\[.*?]@m';

if ($autoResponder) {
    $responderBody = file_get_contents($tempDir, true);
    $responderBody = str_replace('###EMAILMESSAGE###', $sendMsg, $responderBody);
    $responderBody = str_replace('###EMAILTITLE###', $form->auto_betreff, $responderBody);

    preg_match_all($regExp, $responderBody, $matches, PREG_SET_ORDER, 0);
    if ($matches) {
        foreach ($matches as $tmp) {
            if (isset($tmp[0])) {
                $responderBody = str_replace($tmp[0], '', $responderBody);
            }
        }
    }
}

preg_match_all($regExp, $htmlBody, $matches, PREG_SET_ORDER, 0);
if ($matches) {
    foreach ($matches as $tmp) {
        if (isset($tmp[0])) {
            $htmlBody = str_replace($tmp[0], '', $htmlBody);
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
$headers[] = 'Reply-To: '.$absenderName.' <'.get_option('email_reply_to').'>';

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

$send = wp_mail($to, $subject ?: get_bloginfo('title'), $htmlBody, array_unique($headers), $attachments);

$emailResponder = filter_var($emailResponder, FILTER_VALIDATE_EMAIL);


if (!$send) {
    $msg = $bs_formular_filter->bs_formular_message($record->id, 'error_message', true);
    $responseJson->status = false;
    $responseJson->msg = $msg->error_message;
    $responseJson->show_error = true;
    $responseJson->formId = $record->formId;

    return $responseJson;
}

if (get_option('email_empfang_aktiv')) {
    $mailToDb[] = $to;
    $safeDb = new stdClass();
    $safeDb->email_at = implode(' ,', array_unique($mailToDb));
    $safeDb->betreff = $form->betreff;
    $safeDb->form_id = $form->id;
    $safeDb->abs_ip = $_SERVER['REMOTE_ADDR'];
    $safeDb->message = esc_html($htmlBody);
    apply_filters('set_email_empfang_table', $safeDb);
}

if ($autoResponder && $emailResponder) {
    $headersResponder[] = 'Reply-To: '.$absenderName.' <'.get_option('email_reply_to').'>';
    $headersResponder[] = 'From: ' . $absenderName . '  <' . $dbAbsenderEmail . '>';
    $senden = wp_mail($emailResponder, $form->auto_betreff, $responderBody, $headersResponder);
}


$msg = $bs_formular_filter->bs_formular_message($record->id, 'success_message', true);

$responseJson->redirect = false;
if ($form->redirect_aktiv && $form->redirect_page) {
    $responseJson->redirect = true;
    global $post;
    $post = get_post($form->redirect_page);
    if (isset($post)) {
        $responseJson->redirect_uri = get_the_permalink();
    }
}

$responseJson->status = true;
$responseJson->show_success = true;
$responseJson->if_file = true;
$responseJson->msg = $msg->success_message;
$responseJson->formId = $record->formId;

do_action('bs_form_delete_file_folder');