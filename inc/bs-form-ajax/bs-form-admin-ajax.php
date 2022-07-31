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
$method = '';
$data = [];
if(isset($_POST['daten'])) {
	$data = $_POST['daten'];
	$method = filter_var($data['method'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
}

if (!$method) {
    $method = $_POST['method'];
}
global $wpdb;
$tb_form = $wpdb->prefix . 'bs_formulare';
$tb_msg = $wpdb->prefix . 'bs_form_message';
$tb_mail = $wpdb->prefix . 'bs_post_eingang';

switch ($method) {
    case'set_form_input':
        $responseJson->status = false;
        $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
        if (!$type) {
            $responseJson->msg = 'Formular FEHLER!';
            return $responseJson;
        }

        $return = '[label] ' . $type . '-Label' . "\r\n";
        $return .= '[type=' . $type . ']  your-' . $type . '] ' . "\r\n\r\n";

        $regEx = '@(textarea)-([\d])@m';
        preg_match_all($regEx, $type, $matches, PREG_SET_ORDER, 0);
        if (isset($matches)) {
            if ($matches[0][1]) {
                $matches[0][2] ? $row = '-' . $matches[0][2] : $row = '';
                $return = '[label] ' . $matches[0][1] . '-Label' . "\r\n";
                $return .= '[type=' . $matches[0][1] . $row . '] your-' . $matches[0][1] . '] ' . "\r\n\r\n";
            }
        } else {
            $responseJson->msg = 'Formular FEHLER!';
            return $responseJson;
        }

        if ($type === 'button') {
            $return = '[label] Senden' . "\r\n";
            $return .= '[type=' . $type . '] submit]' . "\r\n\r\n";
        }

        if ($type === 'dataprotection') {
            $return = '[label] ' . $type . '-Label' . "\r\n";
            $return .= '[type=' . $type . '] your-dataprotection-url] ' . "\r\n\r\n";
        }

        if ($type === 'email-send-select') {
            $return = '[label] ' . $type . '-Label' . "\r\n";
            $return .= '[type=' . $type . '] #Email-Adresse# your-' . $type . '-1, #Email-Adresse# your-' . $type . '-2, #Email-Adresse#  your-' . $type . '-3]' . "\r\n\r\n";
        }

        if ($type === 'select' || $type === 'radio-default' || $type === 'radio-inline' || $type === 'url-select') {
            $return = '[label] ' . $type . '-Label' . "\r\n";
            $return .= '[type=' . $type . ']  your-' . $type . '-1, your-' . $type . '-2,  your-' . $type . '-3]' . "\r\n\r\n";
        }
        $responseJson->status = true;
        $responseJson->record = ($return);
        break;

    case'add_formular':
        $data = apply_filters('bs_array_to_object', $data);
        isset($data->bezeichnung) && is_string($data->bezeichnung) ? $bezeichnung = esc_html($data->bezeichnung) : $bezeichnung = '';
        isset($data->formular) && is_string($data->formular) ? $formular = trim($data->formular) : $formular = '';
	    isset($data->type) && is_string($data->type) ? $dbType = esc_html($data->type) : $dbType = '';
        isset($data->id) && is_numeric($data->id) ? $id = (int)$data->id : $id = '';

        //CSS CLASSES
	    isset($data->form_class) && is_string($data->form_class) ? $form_class = trim($data->form_class) : $form_class = '';
	    isset($data->input_class) && is_string($data->input_class) ? $input_class = trim($data->input_class) : $input_class = '';
	    isset($data->label_class) && is_string($data->label_class) ? $label_class = trim($data->label_class) : $label_class = '';
	    isset($data->class_aktiv) && is_string($data->class_aktiv) ? $class_aktiv = 1 : $class_aktiv = 0;

	    //BTN CLASSES
	    isset($data->button_class) && is_string($data->button_class) ? $button_class = trim($data->button_class) : $button_class = '';
	    isset($data->btn_icon) && is_string($data->btn_icon) ? $btn_icon = trim($data->btn_icon) : $btn_icon = '';

        //Redirect PAGES
        isset($data->redirection_aktiv) && is_string($data->redirection_aktiv) ? $redirection_aktiv = 1 : $redirection_aktiv = 0;
        isset($data->send_redirection_data_aktiv) && is_string($data->send_redirection_data_aktiv) ? $send_redirection_data_aktiv = 1 : $send_redirection_data_aktiv = 0;
        isset($data->redirect_page) && is_numeric($data->redirect_page) ? $redirect_page = (int)$data->redirect_page : $redirect_page = '';

	    $regEx = '@(.+)#@i';
	    preg_match($regEx, $btn_icon, $hit);
	    isset($hit[1]) && $hit[1] ? $faIcon = '<i class="'.$hit[1].'"></i>&nbsp; ' : $faIcon = '';

        $responseJson->status = false;

        if (!$bezeichnung) {
            $responseJson->msg = 'Es wurde keine Bezeichnung eingegeben!';
            return $responseJson;
        }

        if (!$dbType) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        if (!$formular) {
            $responseJson->msg = 'keine E-Mail Settings gespeichert!';
            return $responseJson;
        }

        if ($dbType == 'update' && !$id) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        // ob_start();
        $record = new stdClass();
        $formular = htmlspecialchars_decode($formular);
        $formular = stripslashes_deep($formular);
        $record->user_layout = esc_textarea($formular);
        $formular = preg_replace(array('/<!--(.*)-->/Uis', "/[[:blank:]]+/"), array('', ' '), str_replace(array("\n", "\r", "\t"), '', $formular));

        $regEx = '@\[.*?(label).*?](.*?)\[.*?(type).*?=(.+?)](.*?)]@m';
        preg_match_all($regEx, $formular, $matches, PREG_SET_ORDER, 0);

        $label = [];
        $type = [];
        $val = [];
        foreach ($matches as $tmp) {
            if (!isset($tmp[2]) || !isset($tmp[4]) || !isset($tmp[5])) {
                continue;
            }
            $label[] = trim($tmp[2]);
            $type[] = trim($tmp[4]);
            $val[] = trim($tmp[5]);
        }

        $SelArr = [];
        $RadioArr = [];
        $return_arr = [];

        $formId = apply_filters('bs_get_random_string', false);
        $formId = substr($formId, 0, 12);
        $record->bezeichnung = $bezeichnung;
        $record->form_id = $formId;

        $create = new stdClass();
        for ($i = 0; $i < count($label); $i++) {
            $selType = trim(str_replace('*', '', $type[$i]));

            if ($selType == 'text') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;

                $text = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $text->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $text;
            }

            if ($selType == 'password') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $password = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $password->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $password;
            }

            if ($selType == 'email') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $email = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $email->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $email;
            }

            if ($selType == 'url') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $url = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $url->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $url;
            }

            if ($selType == 'file') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $file = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $file->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $file;
            }

            if ($selType == 'number') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $number = apply_filters('create_formular_fields',$create);
                $search = $matches[$i][0];
                $formular = apply_filters('string_replace_limit', $search, '###' . $number->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $number;
            }

            if ($selType == 'date') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $date = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $date->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $date;
            }

            $areaRegeEx = '@(textarea)|.+(\d)@m';
            preg_match_all($areaRegeEx, $type[$i], $hit, PREG_SET_ORDER, 0);
            if (isset($hit[0][1]) && $hit[0][1] == 'textarea') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i] . '#' . $hit[1][2];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $hit[0][1];
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $textarea = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $textarea->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $textarea;
            }

            if ($selType == 'select') {
                $arr = preg_replace("/\s+/", " ", $val[$i]);

                $SelArr = explode(', ', $arr);

                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $SelArr;
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $select = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $select->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $select;
            }

            if ($selType == 'email-send-select') {
                $arr = trim(preg_replace("/\s+/", " ", $val[$i]));
                $EmailSelArr = explode(', ', $arr);
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $EmailSelArr;
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $email_send_select = apply_filters('create_formular_fields',$create);

                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $email_send_select->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $email_send_select;
            }

            if ($selType == 'checkbox') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $check = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $check->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $check;
            }

            if ($selType == 'radio-inline' || $selType == 'radio-default') {
                $arr = preg_replace("/\s+/", " ", $val[$i]);
                $RadioArr = explode(', ', $arr);
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $RadioArr;
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $radio = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $radio->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $radio;
            }

            if ($selType == 'button') {

                // $button = apply_filters('create_formular_fields',$class_aktiv, $type[$i], $label[$i], $val[$i], $selType, $input_class, $label_class,$faIcon, $button_class);
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->label_class = $label_class;
                $create->faIcon = $faIcon;
                $create->button_class = $button_class;
                $create->form_id =  $data->id;

                $button = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $button->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $button;
            }

            if ($selType == 'dataprotection') {
                $create->class_aktiv = $class_aktiv;
                $create->type = $type[$i];
                $create->label = $label[$i];
                $create->values = $val[$i];
                $create->case = $selType;
                $create->input_class = $input_class;
                $create->label_class = $label_class;
                $create->form_id =  $data->id;
                $dataprotection = apply_filters('create_formular_fields',$create);
                $formular = apply_filters('string_replace_limit', $matches[$i][0], '###' . $dataprotection->inputId . '###', $formular, $limit = 1);
                $return_arr[] = $dataprotection;
            }
        }

        if ($dbType == 'update') {
            apply_filters('update_form_message_email_txt', $return_arr, $id);
        }
		$record->input_class = $input_class;
        $record->label_class = $label_class;
	    $record->form_class = $form_class;
	    $record->class_aktiv = $class_aktiv;

	    $record->btn_class = $button_class;
	    $record->btn_icon = $btn_icon;

        $record->layout = esc_textarea($formular);
        $record->form_inputs = serialize($return_arr);

        if($redirection_aktiv && $redirect_page){
            $record->redirect_page = $redirect_page;
        } else {
            $record->redirect_page = 0;
        }

        $record->redirection_aktiv = $redirection_aktiv;
        $record->send_redirection_data_aktiv = $send_redirection_data_aktiv;


        switch ($dbType) {
            case 'insert':
	            $meldungen = apply_filters('bs_form_get_settings_by_select', 'form_meldungen')->form_meldungen;
	            $record->form_meldungen = json_encode($meldungen);
                $insert = apply_filters('set_bs_formular', $record);
                $user_info = get_userdata(get_current_user_id());
                $record->formId = $insert->id;
                $record->email_at = $user_info->user_email;
                $record->betreff = 'Nachricht von ' . get_bloginfo('name');
                $record->message = 'Diese Nachricht wurde vom <a href="' . get_bloginfo('url') . '">' . get_bloginfo('url') . '</a> Kontaktformular gesendet.';
                apply_filters('set_bs_message_formular', $record);
                $responseJson->id = $insert->id;
                $responseJson->status = $insert->status;
                $responseJson->msg = $insert->msg;
                $responseJson->show_form_edit = true;
                // ob_get_clean();
                break;

            case'update':
                $record->id = $id;
                apply_filters('update_bs_formular', $record);
                $responseJson->id = $record->id;
                $responseJson->reset = false;
                $responseJson->status = true;
                $responseJson->msg = 'Daten gespeichert!';
                //ob_get_clean();
                break;
        }

        break;

	case'get_input_form_msg':
		isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$id = $_POST['id'] : $id = '';
		if (!$id) {
			$responseJson->status = false;
			$responseJson->msg = 'Ein Fehler ist aufgetreten!';
			return $responseJson;
		}
		$args = sprintf('WHERE id=%d', $id);
		$meldung = apply_filters('get_formulare_by_args',$args, false);

		if(!$meldung->status){
			$responseJson->status = false;
			$responseJson->msg = 'keine Daten gefunden!';
			return $responseJson;
		}
		$meldung = $meldung->record;
		unset($meldung->user_layout);
		unset($meldung->layout);
		unset($meldung->created_at);
		$input = json_decode($meldung->form_meldungen);
		unset($meldung->form_meldungen);
		$date = explode(' ', $meldung->created);
		$meldung->date = $date[0];
		$meldung->time = $date[1];
		$responseJson->list = $input;
		unset($meldung->inputs);
		$responseJson->status = true;
		$responseJson->record = $meldung;
		break;

    case'get_edit_formular':
        isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$id = $_POST['id'] : $id = '';
        $responseJson->status = false;
        if (!$id) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        $args = sprintf('WHERE %s.id=%d', $tb_form, $id);
        $form = apply_filters('get_formulare_by_args', $args, false);
        if (!$form->status) {
            $responseJson->msg = 'keine Daten gefunden!';
            return $responseJson;
        }

        $pages = get_pages();
        $retArr = [];
        foreach ($pages as $page) {
            $ret_item = [
                'name' => $page->post_title,
                'id' => $page->ID,
                'type' => 'page'
            ];
            $retArr[] = $ret_item;
        }


	    $regEx = '@(.+)#@i';
	    $form = $form->record;
	    preg_match($regEx, $form->btn_icon, $hit);
	    isset($hit[1]) ? $faIcon = '<i class="'.$hit[1].'"></i>' : $faIcon = '';
        $date = explode(' ', $form->created);
        $responseJson->status = true;
        $responseJson->id = $form->id;
        $responseJson->bezeichnung = $form->bezeichnung;
	    $responseJson->input_class = $form->input_class;
	    $responseJson->label_class = $form->label_class;
	    $responseJson->form_class = $form->form_class;
        $responseJson->redirect_page =  $form->redirect_page;
        $responseJson->redirect_aktiv = $form->redirect_aktiv != '0';
        $responseJson->send_redirection_data_aktiv = $form->send_redirection_data_aktiv != '0';
	    $responseJson->class_aktiv = (bool) $form->class_aktiv;
	    $responseJson->btn_class = $form->btn_class;
	    $responseJson->btn_icon = $form->btn_icon;
	    $responseJson->faIcon = $faIcon;
        $responseJson->date = $date[0];
        $responseJson->shortcode = $form->shortcode;
        $responseJson->redirect_pages = $retArr;
        $responseJson->time = $date[1];
        $responseJson->user_layout = html_entity_decode($form->user_layout);
        break;

    case'get_formular_message':
        isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$id = $_POST['id'] : $id = '';
        $responseJson->status = false;
        if (!$id) {

            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }
        $args = sprintf('WHERE %s.id=%d', $tb_form, $id);
        $form = apply_filters('get_formulare_by_args', $args, false);

        if (!$form->status) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        $inputs = unserialize($form->record->inputs);

        $notResponse = [
           'button',
           'dataprotection',
           'email-send-select',
           'file'
        ];

        $in_arr = [];
        foreach ($inputs as $tmp) {

            if(in_array($tmp->type, $notResponse)) {
                continue;
            }

            if ($tmp->type == 'select' || $tmp->type == 'radio') {
                $value = '[' . $tmp->label . ' - ' . $tmp->type . ']';
            } else {
                $value = '[' . $tmp->values . ']';
            }

            $in_item = array(
                "values" => $value
            );
            $in_arr[] = $in_item;
        }

        $args = sprintf(' WHERE %s.formId=%d', $tb_msg, $id);
        $formMsg = apply_filters('get_formular_message_by_args', $args, false);
        $formMsg->status ? $message = $formMsg->record : $message = false;
        $message->message = html_entity_decode($message->message);
        $message->message = stripslashes_deep($message->message);

        $message->shortcode = $form->record->shortcode;
        $date = explode(' ', $message->created);
        $message->date = $date[0];
        $message->time = $date[1];


        if($message->auto_msg) {
            $message->auto_msg = html_entity_decode($message->auto_msg);
            $message->auto_msg = stripslashes_deep($message->auto_msg);
        }

        $responseJson->select = apply_filters('bs_form_select_email_template', 'all');
        $message->response_aktiv = (bool) $message->response_aktiv;
        $responseJson->values = $in_arr;
        $responseJson->status = true;
        $responseJson->id = $id;
        $responseJson->select_id = (int) $formMsg->record->email_template;
        $responseJson->message = $message;
        break;

    case'update_form_message':
        isset($data['betreff']) && is_string($data['betreff']) ? $record->betreff = esc_html($data['betreff']) : $record->betreff = '';
        isset($data['message_content']) && is_string($data['message_content']) ? $record->message = esc_html(trim($data['message_content'])) : $record->message = '';
        isset($data['sendTo']) && is_string($data['sendTo']) ? $record->email = esc_html($data['sendTo']) : $record->email = '';
        isset($data['id']) && is_numeric($data['id']) ? $record->id = (int)$data['id'] : $record->id = '';
        isset($data['sendCC']) && is_string($data['sendCC']) ? $email_cc = esc_html($data['sendCC']) : $email_cc = '';
        isset($data['email_template']) && is_numeric($data['email_template']) ? $email_template = esc_html($data['email_template']) : $email_template = '';

        $responseJson->status = false;
        $emailCC = [];
        $cc = false;

        if (!$record->id) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        if (filter_var($record->email, FILTER_VALIDATE_EMAIL) === false) {
            $responseJson->msg = 'Die E-Mail-Adresse ist ungültig.';
            return $responseJson;
        }

        if (!$record->message) {
            $responseJson->msg = 'Das Feld Message darf nicht leer sein!';
            return $responseJson;
        }


       if ($email_cc) {
            $email_cc = str_replace(array(',', ';', ' '), array('#', '#', ''), $email_cc);
            $cc = explode("#", $email_cc);
        }

       if($cc){
           foreach ($cc as $tmp) {
               if (filter_var($tmp, FILTER_VALIDATE_EMAIL)) {
                   if($tmp == $record->email) {
                       continue;
                   }
                   $emailCC[] = $tmp;
               }
           }
       }

       $email_template ? $record->email_template = $email_template : $record->email_template = 1;
       $emailCC ? $record->email_cc =  implode(',', $emailCC) : $record->email_cc = '';
        apply_filters('update_bs_msg_formular', $record);
        $responseJson->status = true;
        $responseJson->msg = 'Daten gespeichert!';
        break;

    case'update_auto_message':
        isset($data['id']) && is_numeric($data['id']) ? $record->id = (int)$data['id'] : $record->id = '';
        isset($data['auto_betreff']) && is_string($data['auto_betreff']) ? $record->auto_betreff = esc_html($data['auto_betreff']) : $record->auto_betreff = '';
        isset($data['auto_msg']) && is_string($data['auto_msg']) ? $record->auto_msg = esc_html($data['auto_msg']) : $record->auto_msg = '';
        $data['aktiv'] && is_string($data['aktiv']) ? $record->aktiv = 1 : $record->aktiv = 0;

        $responseJson->status = false;
        if (!$record->id) {
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }

        if ($record->aktiv && !$record->auto_msg) {
            $responseJson->msg = 'Bei aktiven Auto-Responder, darf das Feld Nachricht nicht leer sein!';
            return $responseJson;
        }

        if($record->aktiv && !$record->auto_betreff) {
        	$record->auto_betreff = $record->betreff = 'Nachricht von ' . get_bloginfo('name');
        }

        apply_filters('update_form_auto_message', $record);
        $responseJson->status = true;
        $responseJson->msg = 'Auto-Responder gespeichert!';
        break;

	case'update_meldungen':
		isset($data['id']) && is_numeric($data['id']) ? (int)$id = $data['id'] : $id = '';
		if (!$id) {
			$responseJson->status = false;
			$responseJson->msg = 'Ein Fehler ist aufgetreten!';
			return $responseJson;
		}

		$args = sprintf('WHERE id=%d', $id);
		$meldung = apply_filters('get_formulare_by_args',$args, false);
		if(!$meldung->status){
			$responseJson->status = false;
			$responseJson->msg = 'Daten konnten nicht gespeichert werden!';
			return $responseJson;
		}

		$regEx = '/_(\d{1,2})/i';
		$newMsg = [];
		foreach ($data as $key => $val){
			preg_match($regEx, $key, $hit);
			if(!isset($hit[1])){
				continue;
			}
		$defMsg = apply_filters('bs_form_default_settings','by_id', $hit[1]);
			$sendMsg = filter_var($data["meldungen_$hit[1]"], FILTER_SANITIZE_STRING);
			if(!$sendMsg){
				$sendMsg = $defMsg->msg;
			}
			$defMsg->msg = $sendMsg;
		    $newMsg[] = $defMsg;
		}

		$record->id = $id;
		$record->form_meldungen = json_encode($newMsg);
		apply_filters('update_bs_form_meldungen', $record);
		$responseJson->status = true;
		$responseJson->msg = 'Formular Meldungen gespeichert!';
		break;

    case'delete_bs_formular':
        isset($_POST['id']) && is_numeric($_POST['id']) ? (int)$id = $_POST['id'] : $id = '';
        if (!$id) {
            $responseJson->status = false;
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }
        apply_filters('delete_bs_formular', $id);
        $responseJson->status = true;
        $responseJson->method = $method;
        $responseJson->msg = 'Formular gelöscht!';
        break;
	case'smtp_settings':
		$email_abs_name = filter_var($data['email_abs_name'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);
		$email_adresse = filter_var($data['email_adresse'], FILTER_VALIDATE_EMAIL);
		$smtp_host = filter_var($data['smtp_host'], FILTER_SANITIZE_STRING);
		$smtp_port = filter_var($data['smtp_port'], FILTER_SANITIZE_NUMBER_INT);
		$smtp_secure = filter_var($data['smtp_secure'], FILTER_SANITIZE_STRING);
		$email_benutzer = filter_var($data['email_benutzer'], FILTER_SANITIZE_STRING);
		$email_passwort = filter_var($data['email_passwort'], FILTER_SANITIZE_STRING);

        $email_reply_to = filter_var($data['email_reply_to'], FILTER_VALIDATE_EMAIL);

		filter_var($data['smtp_auth_check'], FILTER_SANITIZE_STRING) ? $smtp_auth_check = 1 : $smtp_auth_check = 0;
		filter_var($data['email_aktiv'], FILTER_SANITIZE_STRING) ? $email_aktiv = 1 : $email_aktiv = 0;

        filter_var($data['multi_upload'], FILTER_SANITIZE_STRING) ? $multi_upload = 1 : $multi_upload = 0;
        $file_max_size = filter_var($data['file_max_size'], FILTER_SANITIZE_NUMBER_INT);
        $mime_type = filter_var($data['mime_type'], FILTER_SANITIZE_STRING);
        $upload_max_files = filter_var($data['upload_max_files'], FILTER_SANITIZE_NUMBER_INT);

        $file_max_all_size = filter_var($data['file_max_all_size'], FILTER_SANITIZE_NUMBER_INT);

		if(!$email_passwort) {
			$email_passwort = get_option('bs_form_email_passwort');
		}

		$msg = false;

		if(!$email_adresse) {
			$msg .= 'ungültige E-Mail Adresse | ';
		}

		if(!$smtp_host) {
			$msg .= 'ungültiger SMTP Host | ';
		}

		if(!$email_benutzer) {
			$msg .= 'kein Benutzername eingeben | ';
		}

		if(!$email_passwort) {
			$msg .= 'kein Passwort eingeben | ';
		}

		if(!$smtp_port) {
			$smtp_port = 587;
		}

		if(!$smtp_secure){
			$smtp_secure = 'tls';
		}

		if($msg) {
			$msg = substr($msg, 0 , strrpos($msg, '|'));
			$responseJson->status = false;
			$responseJson->spinner = true;
			$responseJson->msg = $msg;
			return $responseJson;
		}

        $file_max_size ? update_option('file_max_size', $file_max_size) : update_option('file_max_size', 3);
        $mime_type ? update_option('upload_mime_types', $mime_type) : update_option('upload_mime_types', 'pdf');
        $file_max_all_size ? update_option('file_max_all_size', $file_max_all_size) : update_option('file_max_all_size', 6);
        $email_reply_to ? update_option('email_reply_to', $email_reply_to) : update_option('email_reply_to', $email_adresse);

        update_option('multi_upload', $multi_upload);
        update_option('upload_max_files', $upload_max_files);
		update_option('email_empfang_aktiv', $email_aktiv);
		update_option('email_abs_name', $email_abs_name);
		update_option('bs_abs_email', $email_adresse);
		update_option('bs_form_smtp_host', $smtp_host);
		update_option('bs_form_smtp_auth_check', $smtp_auth_check);
		update_option('bs_form_smtp_port', $smtp_port);
		update_option('bs_form_email_benutzer', $email_benutzer);
		update_option('bs_form_email_passwort', $email_passwort);
		update_option('bs_form_smtp_secure', $smtp_secure);

		$responseJson->status = true;
		$responseJson->spinner = true;
		$responseJson->msg = date('H:i:s', current_time('timestamp'));

		break;

    case'update_bs_formular_settings':
        $user_role = filter_var($data['user_role'], FILTER_SANITIZE_STRING);
        if (!$user_role) {
            $responseJson->status = false;
            $responseJson->msg = 'Ein Fehler ist aufgetreten!';
            return $responseJson;
        }
        update_option('bs_formular_user_role', $user_role);
        $responseJson->status = true;
        $responseJson->spinner = true;
        $responseJson->msg = date('H:i:s', current_time('timestamp'));
        break;

	case 'smtp_check':
		$smtpCheck = apply_filters('bs_form_get_smtp_test', false);
		$responseJson->status = $smtpCheck['status'];
		$responseJson->msg = $smtpCheck['msg'];

		break;

	case 'get_fa_icons':
		$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
		$formId = filter_input(INPUT_POST, 'formId', FILTER_SANITIZE_STRING);
		$status = false;
		$responseJson->type = $type;
		switch ($type) {
			case'slider':
				$responseJson->formId = $formId;
				break;
		}

		$cheatSet = file_get_contents('tools/FontAwesomeCheats.txt', true);
		$regEx = '/fa.*?\s/m';
		preg_match_all($regEx, $cheatSet, $matches, PREG_SET_ORDER, 0);
		if (!isset($matches)) {
			$responseJson->status = $status;
			return $responseJson;
		}

		$ico_arr = [];
		foreach ($matches as $tmp) {
			$icon = trim($tmp[0]);
			$regExp = sprintf('/%s.+?\[?x(.*?);\]/m', $icon);
			preg_match_all($regExp, $cheatSet, $matches1, PREG_SET_ORDER, 0);
			$ico_item = array(
				'icon' => 'fa ' . $icon,
				'title' => substr($icon, strpos($icon, '-') + 1),
				'code' => $matches1[0][1]
			);
			$ico_arr[] = $ico_item;
		}
		$responseJson->method = $method;
		$responseJson->status = true;
		$responseJson->record = $ico_arr;
		break;

	case'get_email_template':
		$id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

		$args = sprintf('WHERE %s.id=%d',$tb_mail, $id);
		$message = apply_filters('get_email_empfang_data',$args, false);
		$message = html_entity_decode($message->record->message);
		$message = stripslashes_deep($message);

		$file = BS_FORMULAR_INC . 'optionen/Mailer/email-template.html';
		$email = file_get_contents($file, true);
		$email = str_replace('###EMAIL###', $message, $email);
		$responseJson->message = $email;
		$responseJson->method = $method;
		$responseJson->status = true;
		break;

	case'delete_email':
		 $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
		 $type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);

		 if($type == 'all'){
		 	$allMail = apply_filters('get_email_empfang_data', false);
		 	if($allMail->status){
		 		foreach ($allMail->record as $tmp){
				    apply_filters('delete_bs_formular_email', $tmp->id);
			    }
		    }

			 $responseJson->msg = 'E-Mails gelöscht!';
			 $responseJson->method = $method;
			 $responseJson->status = true;
			 return $responseJson;
		 }
		 apply_filters('delete_bs_formular_email', $id);
		$responseJson->msg = 'E-Mail gelöscht!';
		$responseJson->method = $method;
		$responseJson->status = true;
		break;

    case'get_pages_select':

        $pages = get_pages();
        $retArr = [];
        foreach ($pages as $page) {
            $ret_item = [
                'name' => $page->post_title,
                'id' => $page->ID,
                'type' => 'page'
            ];
            $retArr[] = $ret_item;
        }
        $responseJson->status = true;
        $responseJson->record = $retArr;

        break;

    case'formular_data_table':

        if(SET_EMAIL_DEFAULT_MELDUNGEN){
           $defMessage = apply_filters('bs_form_default_settings','');
           apply_filters('bs_update_default_settings','form_meldungen', $defMessage->meldungen);
        }

        $tableData = new stdClass();
        $query = '';
        $columns = array(
            "bezeichnung",
            "shortcode",
            "created_at",
            "",
            ""
        );

        if (isset($_POST['search']['value'])) {
            $query = ' WHERE shortcode LIKE "%' . $_POST['search']['value'] . '%"
             OR bezeichnung LIKE "%' . $_POST['search']['value'] . '%"
             OR created_at LIKE "%' . $_POST['search']['value'] . '%"
            ';
        }

        if (isset($_POST['order'])) {
            $query .= ' ORDER BY ' . $columns[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
        } else {
            $query .= ' ORDER BY created_at DESC';
        }

        $limit = '';
        if ($_POST["length"] != -1) {
            $limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
        }

        $table = apply_filters('get_formulare_by_args', $query . $limit);
        $data_arr = array();
        if (!$table->status) {
            return $responseJson = array(
                "draw" => $_POST['draw'],
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => $data_arr
            );
        }

        foreach ($table->record as $tmp) {
            $date = explode(' ', $tmp->created);
            $data_item = array();
            $data_item[] = $tmp->bezeichnung;
            $data_item[] = '<span class="d-none">' . $tmp->shortcode . '</span><b class="strong-font-weight">[bs-formular id="' . $tmp->shortcode . '"]</b>';
            $data_item[] = '<span class="d-none">' . $tmp->created_at . '</span><b class="strong-font-weight">' . $date[0] . '</b><small style="font-size: .9rem" class="d-block">' . $date[1] . ' Uhr</small>';
            $data_item[] = '<button data-bs-toggle="collapse" data-bs-target="#collapseCreateFormularSite" data-id="' . $tmp->id . '" class="btn-edit-bs-formular btn btn-blue-outline"><i class="fa fa-cogs"></i></button>';
            $data_item[] = '<button type="button" data-bs-method="delete_bs_formular" data-bs-toggle="modal" data-bs-target="#formDeleteModal" data-bs-id="' . $tmp->id . '" class="btn btn-outline-danger"><i class="fa fa-trash"></i></button>';
            $data_arr[] = $data_item;
        }

        $tbCount = apply_filters('get_formulare_by_args', false);
        $responseJson = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $tbCount->count,
            "recordsFiltered" => $tbCount->count,
            "data" => $data_arr,
        );
        break;

	case'formular_post_table':
		$tb_form = $wpdb->prefix . 'bs_formulare';
		$tb_msg = $wpdb->prefix . 'bs_form_message';
		$tb_mail = $wpdb->prefix . 'bs_post_eingang';
		$tableData = new stdClass();
		$query = '';
		$columns = array(
			"$tb_mail.created_at",
			"$tb_form.bezeichnung",
			"$tb_mail.betreff",
			"$tb_form.shortcode",
			"",
			"",
			"",
			""
		);

		if (isset($_POST['search']['value'])) {
			$query = ' WHERE '.$tb_form.'.bezeichnung LIKE "%' . $_POST['search']['value'] . '%"
			 OR '.$tb_mail.'.betreff LIKE "%' . $_POST['search']['value'] . '%"
             OR '.$tb_mail.'.created_at LIKE "%' . $_POST['search']['value'] . '%"
             OR '.$tb_form.'.shortcode LIKE "%' . $_POST['search']['value'] . '%"
            ';
		}

		if (isset($_POST['order'])) {
			$query .= ' ORDER BY ' . $columns[$_POST['order']['0']['column']] . ' ' . $_POST['order']['0']['dir'] . ' ';
		} else {
			$query .= ' ORDER BY '.$tb_mail.'.created_at DESC';
		}

		$limit = '';
		if ($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}

		$table = apply_filters('get_email_empfang_data', $query . $limit);
		$data_arr = array();
		if (!$table->status) {
			return $responseJson = array(
				"draw" => $_POST['draw'],
				"recordsTotal" => 0,
				"recordsFiltered" => 0,
				"data" => $data_arr
			);
		}

		foreach ($table->record as $tmp) {
			$date = explode(' ', $tmp->created);
			$data_item = array();
			$data_item[] = '<span class="d-none">' . $tmp->created_at . '</span><b class="strong-font-weight">' . $date[0] . '</b><small style="font-size: .9rem" class="d-block">' . $date[1] . ' Uhr</small>';
			$data_item[] = $tmp->bezeichnung;
			$data_item[] = $tmp->betreff;
			$data_item[] = '<span class="d-none">' . $tmp->shortcode . '</span><b class="strong-font-weight">[bs-formular id="' . $tmp->shortcode . '"]</b>';
			$data_item[] = $tmp->email_at;
			$data_item[] = $tmp->abs_ip;
			$data_item[] = '<button data-bs-toggle="modal"  data-bs-method="get_email_template" data-bs-target="#btnIconModal" data-bs-id="' . $tmp->id . '" class="btn btn-blue-outline"><i class="fa fa-envelope-open"></i></button>';
			$data_item[] = '<button type="button" data-bs-method="delete_email" data-bs-toggle="modal" data-bs-target="#formDeleteModal" data-bs-id="' . $tmp->id . '" class="btn btn-outline-danger"><i class="fa fa-trash"></i></button>';
			$data_arr[] = $data_item;
		}

		$tbCount = apply_filters('get_email_empfang_data', false);
		$responseJson = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $tbCount->count,
			"recordsFiltered" => $tbCount->count,
			"data" => $data_arr,
		);

		break;
}
