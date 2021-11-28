<?php


namespace Form\BsFormular;

use Exception;
use stdClass;

defined('ABSPATH') or die();

/**
 * ADMIN BS-FORMULAR HANDLE
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

if (!class_exists('BootstrapFormularFilter')) {
    add_action('plugin_loaded', array('Form\\BsFormular\\BootstrapFormularFilter', 'init'), 0);

    class BootstrapFormularFilter
    {
        //STATIC INSTANCE
        private static $instance;
        private string $table_formulare = 'bs_formulare';
        private string $table_form_message = 'bs_form_message';
        private string $table_settings = 'bs_form_settings';
        private string $table_email = 'bs_post_eingang';


        /**
         * @return static
         */
        public static function init(): self
        {
            if (is_null(self::$instance)) {
                self::$instance = new self;
            }

            return self::$instance;
        }

        public function __construct()
        {
            //SMTP FILTER
            //add_filter('wp_mail_smtp_custom_options', array($this, 'bs_formular_mailer_smtp_options'));
            //TODO CREATE FORMULAR FELDER
            add_filter('create_formular_fields', array($this, 'bs_form_create_formular_fields'), 10, 9);
            add_filter('string_replace_limit', array($this, 'bs_form_string_replace_limit'), 10, 4);
            //GET Formulare
            add_filter('get_formulare_by_args', array($this, 'bsFormGetFormulareByArgs'), 10, 3);
            //GET Formular Message
            add_filter('get_formular_message_by_args', array($this, 'bsFormGetFormularMessageByArgs'), 10, 3);
            // Formular Speichern
            add_filter('set_bs_formular', array($this, 'bsFormSetFormular'));
            // Formular Message Speichern
            add_filter('set_bs_message_formular', array($this, 'setMessageFormular'));
            // Formular Update
            add_filter('update_bs_formular', array($this, 'updateBsFormular'));
            // Formular Send Message Update
            add_filter('update_bs_msg_formular', array($this, 'updateFormMessage'));
            // Formular löschen
            add_filter('delete_bs_formular', array($this, 'deleteBsFormular'));
            // Formular E-Mail Message Update
            add_filter('update_form_message_email_txt', array($this, 'updateMessageEmailTxt'), 10, 2);
            // Formular Auto Send Message Update
            add_filter('update_form_auto_message', array($this, 'updateFormAutoMessage'), 10, 2);
            // Helper ArrayToObject
            add_filter('bs_array_to_object', array($this, 'bsArrayToObject'));
            // Helper Random String
            add_filter('bs_get_random_string', array($this, 'bs_load_random_string'));
            // Random Generator
            add_filter('get_bs_form_generate_random', array($this, 'getBSFormGenerateRandomId'), 10, 4);
            // Set Default Settings
            add_filter('bs_form_set_default_settings', array($this, 'bsFormSetDefaultSettings'));
            // Get Settings by Select
            add_filter('bs_form_get_settings_by_select', array($this, 'bsFormGetFormularSettingsByArgs'));
            // Get Default Settings (JSon String)
            add_filter('bs_form_default_settings', array($this, 'bsFormDefaultSettings'), 10, 2);
            // UPDATE Default Settings
            add_filter('bs_update_default_settings', array($this, 'updateDefaultSettings'), 10, 2);
            // Update Formular Meldungen
            add_filter('update_bs_form_meldungen', array($this, 'updateFormMeldungen'));
            // Get Formulardaten by JOIN
            add_filter('bs_form_formular_data_by_join', array($this, 'bsFormFormularDataByJoin'), 10, 2);
            //TODO VALIDATE FORMULAR SEND MESSAGE
            add_filter('bs_formular_validate_message_inputs', array($this, 'bsFormularValidateMessageInputs'), 10, 3);
            //TODO Get Input By ID
            add_filter('get_formular_inputs_by_id', array($this, 'bsFormGetFormulareInputsById'));
            // TODO VALIDATE formular Input Cgeckbox / Radio
            add_filter('validate_formular_radio_checkbox', array($this, 'validateFormularRadioCheckbox'), 10, 3);
            //Todo Create File multi Upload
            add_filter('re_array_files', array($this, 'reArrayFiles'));
            // TODO DESTROY DIR
            add_action('bs_form_destroy_dir', array($this, 'bsFormDestroyDir'));
            // TODO Delete File Input Folders
            add_action('bs_form_delete_file_folder', array($this, 'bsFormDeleteFileFolder'));
            //E-Mail Template auswahl
            add_filter('bs_form_select_email_template', array($this, 'bsFormSelectEmailTemplate'), 10, 2);


            // TODO JOB EMAIL DATEN
            //Set E-Mail Data
            add_filter('set_email_empfang_table', array($this, 'bsFormSetEmailEmpfang'));
            // Get E-Mail Data
            add_filter('get_email_empfang_data', array($this, 'getEmailEmpfangData'), 10, 2);
            // Delete E-Mail
            add_filter('delete_bs_formular_email', array($this, 'deleteFormularEmail'));
        }

        /**
         * @throws Exception
         */
        public function bs_form_create_formular_fields($class_aktiv, $type, $label, $values, $case, $input_class, $label_class, $faIcon = false, $button_class = false): object
        {
            $record = new stdClass();
            $record->status = true;
            $html = '';

            $id = $this->bs_load_random_string();
            $id = substr($id, 0, 12);
            if ($input_class) {
                $inputStart = '<div class="' . $input_class . '">';
                $inputEnd = '</div>';
            } else {
                $inputStart = '';
                $inputEnd = '';
            }

            switch ($case) {
                case'select':
                    if (strpos($type, '*')) {
                        $stern = '<span class="text-danger"> *</span>';
                        $require = 'required';
                        $field = trim(str_replace('*', '', $type));
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback">' . $invalidMsg->$field . '</div>';
                    } else {
                        $require = false;
                        $invDiv = '';
                        $stern = '';
                    }

                    $valArr = array();
                    $html = $inputStart;
                    if (!$class_aktiv) {
                        $html .= '<label class="form-label ' . $label_class . '" for="' . $id . '">' . $label . ' ' . $stern . '</label>';
                    }

                    $html .= '<select onchange="this.blur()" name="' . $id . '" class="form-control" id="' . $id . '" ' . $require . '>';
                    $html .= '<option value="">' . __('auswählen', 'bs-formular') . '...</option>';
                    foreach ($values as $tmp) {
                        $random = $this->bs_load_random_string();
                        $random = substr($random, 0, 12);
                        $valItem = array(
                            "id" => $random,
                            "bezeichnung" => $tmp
                        );
                        $valArr[] = $valItem;
                        if (strpos($tmp, '*')) {
                            $sel = 'selected';
                            $tmp = str_replace('*', '', $tmp);
                        } else {
                            $sel = '';
                        }
                        $html .= '<option value="' . $random . '" ' . $sel . '> ' . $tmp . '</option>';
                    }
                    $html .= '</select>' . $invDiv . $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->values = serialize($valArr);
                    $record->inputId = $id;
                    $record->label = $id;
                    $record->type = $case;
                    $record->label = $label;
                    $record->required = $require;

                    return $record;

                case'email-send-select':
                    if (strpos($type, '*')) {
                        $stern = '<span class="text-danger"> *</span>';
                        $require = 'required';
                        $field = trim(str_replace('*', '', $type));
                        //echo $field;
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback">' . $invalidMsg->$field . '</div>';
                    } else {
                        $require = false;
                        $invDiv = '';
                        $stern = '';
                    }

                    $valArr = array();
                    $html = $inputStart;
                    if (!$class_aktiv) {
                        $html .= '<label class="form-label ' . $label_class . '" for="' . $id . '">' . $label . ' ' . $stern . '</label>';
                    }

                    $html .= '<select onchange="this.blur()" name="' . $id . '" class="form-control email-send-select" id="' . $id . '" ' . $require . '>';
                    $html .= '<option value="">' . __('auswählen', 'bs-formular') . '...</option>';
                    foreach ($values as $tmp) {

                        $random = $this->bs_load_random_string();
                        $random = substr($random, 0, 12);
                        $valItem = array(
                            "id" => $random,
                            "bezeichnung" => $tmp
                        );

                        $tmp = trim($tmp);
                        $valArr[] = $valItem;
                        if (strpos($tmp, '*')) {
                            $sel = 'selected';
                            $tmp = str_replace('*', '', $tmp);
                        } else {
                            $sel = '';
                        }

                        $regEx = '@#(.+)#@i';
                        preg_match($regEx, $tmp, $matches);
                        if ($matches) {
                            $sendData = [
                                'status' => true,
                                'id' => $random,
                                'email' => $matches[1]
                            ];

                        } else {
                            $sendData = [
                                'status' => false
                            ];
                        }

                        $value = base64_encode(json_encode($sendData));
                        $tmp = str_replace($matches[0], '', $tmp);
                        $html .= '<option value="' . $value . '" ' . $sel . '> ' . $tmp . '</option>';
                    }
                    $html .= '</select>' . $invDiv . $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->values = serialize($valArr);
                    $record->inputId = $id;
                    $record->label = $id;
                    $record->type = $case;
                    $record->label = $label;
                    $record->required = $require;

                    return $record;

                case'radio-inline':
                case'radio-default':
                    $valArr = array();
                    $inpType = substr($type, 0, strpos($type, '-'));
                    $format = substr($type, strpos($type, '-') + 1);
                    $html = '';
                    foreach ($values as $tmp) {
                        $random = $this->bs_load_random_string();
                        $random = substr($random, 0, 12);
                        $valItem = array(
                            "id" => $random,
                            "bezeichnung" => $tmp
                        );
                        $valArr[] = $valItem;
                        $record->required = false;
                        if (strpos($tmp, '*')) {
                            $check = 'checked';
                            $record->required = $id;
                            $tmp = str_replace('*', '', $tmp);
                        } else {
                            $check = false;
                        }

                        $format == 'default' ? $formType = '' : $formType = 'form-check-inline';
                        $html .= $inputStart;
                        $html .= '<div class="form-check ' . $formType . '">';
                        $html .= '<input onclick="this.blur()" class="form-check-input" type="radio" name="' . $id . '" id="' . $random . '" value="' . $random . '" ' . $check . '>';
                        $html .= '<label class="form-check-label" for="' . $random . '">';
                        $html .= $tmp;
                        $html .= '</label>';
                        $html .= '</div>';
                        $html .= $inputEnd;
                    }
                    $record->html = esc_textarea($html);
                    $record->values = serialize($valArr);
                    $record->inputId = $id;
                    $record->label = $label;
                    $record->type = $inpType;

                    return $record;

                case'text':
                case'email':
                case'url':
                case'number':
                case'date':
                case'password':
                    if (strpos($type, '*')) {
                        $require = 'required';
                        $stern = '<span class="text-danger"> *</span>';
                        $field = trim(str_replace('*', '', $type));
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback">' . $invalidMsg->$field . '</div>';
                    } else {
                        $require = false;
                        $stern = '';
                        $invDiv = '';
                    }


                    $case == 'password' ? $autocomplete = 'autocomplete="cc-number"' : $autocomplete = '';
                    $html .= $inputStart;
                    if ($class_aktiv) {
                        $stern = strip_tags($stern);
                        $placeholder = 'placeholder="' . $label . ' ' . $stern . '"';
                    } else {
                        $placeholder = '';
                        $html .= '<label class="form-label ' . $label_class . '" for="' . $id . '">' . $label . ' ' . $stern . '</label>';
                    }
                    $html .= '<input type="' . $case . '" class="form-control" ' . $placeholder . ' name="' . $id . '" id="' . $id . '"  ' . $require . ' ' . $autocomplete . '/>' . $invDiv;
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->required = $require;
                    $record->label = $label;
                    $record->values = $values;
                    $record->type = $case;

                    return $record;
                case 'file':
                    if (strpos($type, '*')) {
                        $require = 'required';
                        $stern = '<span class="text-danger"> *</span>';
                        $field = trim(str_replace('*', '', $type));
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback mt-n2 mb-2">' . $invalidMsg->$field . '</div>';
                    } else {
                        $require = false;
                        $stern = '';
                        $invDiv = '';
                    }

                    $mimeTypes = '';
                    $regEx = '@#(.+?)#@i';

                    preg_match($regEx, $label, $matches);
                    if ($matches) {
                        $types = preg_replace("/\s+/", "", $matches[1]);
                        $label = str_replace($matches[0], '', $label);
                    } else {
                        $types = preg_replace("/\s+/", "", get_option('upload_mime_types'));
                    }
                    $html .= $inputStart;
                    if ($class_aktiv) {
                        $stern = strip_tags($stern);
                        $placeholder = 'placeholder="' . $label . ' ' . $stern . '"';
                    } else {
                        $placeholder = '';
                        $html .= '<label class="form-label ' . $label_class . '" for="' . $id . '">' . $label . ' ' . $stern . '</label>';
                    }

                    $fileType = str_replace([',', ';'], '#', $types);
                    $mimes = explode('#', $fileType);
                    if ($mimes) {
                        $x = count($mimes);
                        for ($i = 0; $i < count($mimes); $i++) {
                            $i == $x - 1 ? $dot = '' : $dot = ',';
                            $mimeTypes .= '.' . $mimes[$i] . $dot;
                        }
                    }

                    get_option('multi_upload') ? $multi = ' multiple' : $multi = '';
                    $html .= '<div class="filePondWrapper">';
                    $html .= '<input data-id="' . $id . '" type="' . $case . '"class="bsFiles files' . $id . '" ' . $placeholder . ' name="' . $id . '" id="' . $id . '" accept="' . $mimeTypes . '"  ' . $require . ' ' . $multi . '/>' . $invDiv;
                    $html .= '</div>';
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->required = $require;
                    $record->label = $label;
                    $record->values = $values;
                    $record->type = $case;

                    return $record;

                case'textarea':
                    $rowLines = substr($type, strrpos($type, '#') + 1);

                    $rowLines ? $row = 'rows="' . $rowLines . '"' : $row = '';

                    if (strpos($type, '*')) {
                        $stern = '<span class="text-danger"> *</span>';
                        $require = 'required';
                        $field = trim(str_replace('*', '', $type));
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback">' . $invalidMsg->$field . '</div>';
                    } else {
                        $require = false;
                        $stern = '';
                        $invDiv = '';
                    }

                    $html .= $inputStart;
                    if ($class_aktiv) {
                        $stern = strip_tags($stern);
                        $placeholder = 'placeholder="' . $label . ' ' . $stern . '"';
                    } else {
                        $placeholder = '';
                        $html .= '<label class="form-label ' . $label_class . '" for="' . $id . '">' . $label . ' ' . $stern . '</label>';
                    }

                    $html .= '<textarea ' . $placeholder . ' name="' . $id . '" class="form-control" id="' . $id . '" ' . $row . ' ' . $require . '></textarea>' . $invDiv;
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->required = $require;
                    $record->label = $label;
                    $record->values = $values;
                    $record->type = $case;

                    return $record;

                case'checkbox':
                    if (strpos($label, '*')) {
                        $required = 'required';
                        $stern = '<span class="text-danger"> *</span>';
                        $label = str_replace('*', '', $label);
                        $field = trim(str_replace('*', '', $label));
                        $invalidMsg = $this->bsFormDefaultSettings('by_field', $field);
                        $invDiv = '<div class="invalid-feedback">' . $invalidMsg->$field . '</div>';
                    } else {
                        $required = false;
                        $stern = '';
                        $invDiv = '';
                    }
                    if (strpos($type, '*')) {
                        $checked = 'checked';
                    } else {
                        $checked = false;
                    }

                    $html = $inputStart;
                    $html .= '<div class="form-check">';
                    $html .= '<input onclick="this.blur()" class="form-check-input" name="' . $id . '" type="checkbox" id="' . $id . '" ' . $checked . ' ' . $required . '>';
                    $html .= '<label class="form-check-label" for="' . $id . '">';
                    $html .= $label;
                    $html .= $stern . '</label>';
                    $html .= $invDiv;
                    $html .= '</div>';
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->values = $values;
                    $record->checked = $checked;
                    $record->required = $required;
                    $record->label = $label;
                    $record->type = $case;

                    return $record;

                case'button':
                    $button_class ? $btn = $button_class : $btn = 'btn-outline-secondary';
                    $html = $inputStart;
                    $html .= '<div class="bs-btn-wrapper">';
                    $html .= ' <button id="' . $id . '" name="' . $id . '" type="' . $values . '" class="btn ' . $btn . '">' . $faIcon . $label . '</button>';
                    $html .= '<div class="bs-form-sending"><span class="sending-text">Daten werden gesendet </span><span class="dot-pulse"></span></div>';
                    $html .= '</div>';
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->values = $values;
                    $record->bezeichnung = $label;
                    $record->type = $case;

                    return $record;

                case'dataprotection':
                    strpos($type, '*') ? $checked = 'checked' : $checked = false;

                    $invalidMsg = $this->bsFormDefaultSettings('by_field', 'dataprotection');
                    $invDiv = '<div class="invalid-feedback">' . $invalidMsg->dataprotection . '</div>';
                    $regEx = '@#(.+)#@i';
                    preg_match($regEx, $label, $matches);
                    if ($matches) {
                        $labelUrl = '<a href="' . $values . '" target="_blank">' . $matches[1] . '</a>';
                        $dataProtectLabel = str_replace($matches[0], $labelUrl, $label);
                    } else {
                        $dataProtectLabel = $label;
                    }

                    $html = $inputStart;
                    $html .= '<div class="form-check dscheck">';
                    $html .= '<input class="form-check-input" data-id="' . $id . '" name="dscheck" type="checkbox" id="' . $id . '" ' . $checked . ' required>';
                    $html .= '<label class="form-check-label" for="' . $id . '">';
                    $html .= $dataProtectLabel;
                    $html .= '<span class="text-danger"> *</span> </label>';
                    $html .= $invDiv;
                    $html .= '</div>';
                    $html .= $inputEnd;
                    $record->html = esc_textarea($html);
                    $record->inputId = $id;
                    $record->url = $values;
                    $record->label = $label;
                    $record->checked = $checked;
                    $record->type = $case;

                    return $record;

                default:
                    $record->status = false;

                    return $record;
            }
        }

        public function bs_form_string_replace_limit($search, $replace, $string, $limit = 1)
        {
            $pos = strpos($string, $search);
            if ($pos === false) {
                return $string;
            }
            $searchLen = strlen($search);
            for ($i = 0; $i < $limit; $i++) {
                $string = substr_replace($string, $replace, $pos, $searchLen);
                $pos = strpos($string, $search);
                if ($pos === false) {
                    break;
                }
            }

            return $string;
        }

        public function updateMessageEmailTxt($input, $id)
        {

            $args = sprintf(' WHERE formId=%d', $id);
            $formMsg = $this->bsFormGetFormularMessageByArgs($args, false);
            if (!$formMsg->status) {
                return;
            }

            $regExp = '@\[.*?]@m';
            $message = $formMsg->record->message;
            preg_match_all($regExp, $message, $matches, PREG_SET_ORDER, 0);
            $MessArr = [];
            foreach ($matches as $tmp) {
                if ($tmp[0]) {
                    $MessArr[] = $tmp[0];
                }
            }
            $auto_msg = $formMsg->record->auto_msg;
            preg_match_all($regExp, $auto_msg, $matches, PREG_SET_ORDER, 0);
            $AutoMessArr = [];
            foreach ($matches as $tmp) {
                if ($tmp[0]) {
                    $AutoMessArr[] = $tmp[0];
                }
            }

            $inArr = [];
            foreach ($input as $tmp) {
                if ($tmp->type == 'button' || $tmp->type == 'dataprotection') {
                    continue;
                }
                if ($tmp->type == 'select' || $tmp->type == 'radio') {
                    $userVal = $tmp->label . ' - ' . $tmp->type;
                } else {
                    $userVal = $tmp->values;
                }
                $inArr[] = '[' . $userVal . ']';
            }

            foreach ($MessArr as $tmp) {
                if (!in_array($tmp, $inArr)) {
                    $message = str_replace($tmp, '', $message);
                }
            }

            foreach ($AutoMessArr as $tmp) {
                if (!in_array($tmp, $inArr)) {
                    $auto_msg = str_replace($tmp, '', $auto_msg);
                }
            }

            $record = new stdClass();
            $record->message = $message;
            $record->auto_msg = $auto_msg;
            $record->id = $id;
            $this->update_db_message_text($record);
        }

        public function update_db_message_text($record)
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_form_message;
            $wpdb->update(
                $table,
                array(
                    'message' => $record->message,
                    'auto_msg' => $record->auto_msg
                ),
                array('id' => $record->id),
                array(
                    '%s',
                    '%s'
                ),
                array('%d')
            );
        }

        public function bsFormGetFormularMessageByArgs($args, $fetchMethod = true, $col = false): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;
            $return->count = 0;
            $fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
            $table = $wpdb->prefix . $this->table_form_message;
            $col ? $select = $col : $select = '*';
            $result = $wpdb->$fetch("SELECT {$select} ,DATE_FORMAT(created_at, '%d.%m.%Y %H:%i:%s') AS created  FROM {$table} {$args}");
            if (!$result) {
                return $return;
            }
            $fetchMethod ? $count = count($result) : $count = 1;
            $return->count = $count;
            $return->status = true;
            $return->record = $result;

            return $return;
        }


        public function bsFormGetFormulareByArgs($args, $fetchMethod = true, $col = false): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;
            $return->count = 0;
            $fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
            $table = $wpdb->prefix . $this->table_formulare;
            $col ? $select = $col : $select = '*';
            $result = $wpdb->$fetch("SELECT {$select} ,DATE_FORMAT(created_at, '%d.%m.%Y %H:%i:%s') AS created  FROM {$table} {$args}");
            if (!$result) {
                return $return;
            }
            $fetchMethod ? $count = count($result) : $count = 1;
            $return->count = $count;
            $return->status = true;
            $return->record = $result;

            return $return;
        }

        public function bsFormGetFormulareInputsById($args): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;

            $table = $wpdb->prefix . $this->table_formulare;
            $result = $wpdb->get_row("SELECT inputs  FROM {$table} {$args->where}");
            if (!$result) {
                return $return;
            }

            $inputs = unserialize($result->inputs);
            foreach ($inputs as $tmp) {
                if ($tmp->inputId == $args->id) {
                    $return->status = true;
                    $return->record = $tmp;

                    return $return;
                }
            }

            return $return;
        }


        public function bsFormFormularDataByJoin($args, $fetchMethod = true): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;
            $return->count = 0;
            $fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
            $f = $wpdb->prefix . $this->table_formulare;
            $fm = $wpdb->prefix . $this->table_form_message;
            $result = $wpdb->$fetch("SELECT {$f}.* ,
									  DATE_FORMAT({$f}.created_at, '%d.%m.%Y %H:%i:%s') AS created,
       								  {$fm}.betreff, {$fm}.email_at, {$fm}.email_cc, {$fm}.message,{$fm}.response_aktiv,
									  {$fm}.auto_betreff, {$fm}.auto_msg, {$fm}.email_template
									  FROM {$f} 
									  LEFT JOIN {$fm} ON {$f}.id = {$fm}.formId {$args}");
            if (!$result) {
                return $return;
            }
            $fetchMethod ? $count = count($result) : $count = 1;
            $return->count = $count;
            $return->status = true;
            $return->record = $result;

            return $return;
        }

        public function bsFormSetFormular($record): object
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_formulare;
            $wpdb->insert(
                $table,
                array(
                    'shortcode' => $record->form_id,
                    'bezeichnung' => $record->bezeichnung,
                    'layout' => $record->layout,
                    'inputs' => $record->form_inputs,
                    'user_layout' => $record->user_layout,
                    'form_meldungen' => $record->form_meldungen,
                    'input_class' => $record->input_class,
                    'form_class' => $record->form_class,
                    'label_class' => $record->label_class,
                    'class_aktiv' => $record->class_aktiv,
                    'btn_class' => $record->btn_class,
                    'btn_icon' => $record->btn_icon,
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s')
            );
            $return = new stdClass();
            if (!$wpdb->insert_id) {
                $return->status = false;
                $return->msg = 'Daten konnten nicht gespeichert werden!';
                $return->id = false;

                return $return;
            }
            $return->status = true;
            $return->msg = 'Daten gespeichert!';
            $return->id = $wpdb->insert_id;

            return $return;
        }

        public function setMessageFormular($record): object
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_form_message;
            $wpdb->insert(
                $table,
                array(
                    'formId' => $record->formId,
                    'betreff' => $record->betreff,
                    'email_at' => $record->email_at,
                    'message' => $record->message,
                ),
                array('%d', '%s', '%s', '%s')
            );

            $return = new stdClass();
            if (!$wpdb->insert_id) {
                $return->status = false;
                $return->msg = 'Daten konnten nicht gespeichert werden!';
                $return->id = false;

                return $return;
            }
            $return->status = true;
            $return->msg = 'Daten gespeichert!';
            $return->id = $wpdb->insert_id;

            return $return;
        }

        public function updateBsFormular($record): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_formulare;
            $wpdb->update(
                $table,
                array(
                    'bezeichnung' => $record->bezeichnung,
                    'layout' => $record->layout,
                    'inputs' => $record->form_inputs,
                    'user_layout' => $record->user_layout,
                    'input_class' => $record->input_class,
                    'form_class' => $record->form_class,
                    'label_class' => $record->label_class,
                    'class_aktiv' => $record->class_aktiv,
                    'btn_class' => $record->btn_class,
                    'btn_icon' => $record->btn_icon
                ),
                array('id' => $record->id),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d',
                    '%s',
                    '%s'
                ),
                array('%d')
            );
        }

        public function updateFormMessage($record): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_form_message;
            $wpdb->update(
                $table,
                array(
                    'email_cc' => $record->email_cc,
                    'betreff' => $record->betreff,
                    'email_at' => $record->email,
                    'message' => $record->message,
                    'email_template' => $record->email_template
                ),
                array('id' => $record->id),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%d'
                ),
                array('%d')
            );
        }

        public function updateFormMeldungen($record): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_formulare;
            $wpdb->update(
                $table,
                array(
                    'form_meldungen' => $record->form_meldungen,
                ),
                array('id' => $record->id),
                array(
                    '%s'
                ),
                array('%d')
            );
        }

        public function updateFormAutoMessage($record): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_form_message;
            if ($record->auto_save) {
                $wpdb->update(
                    $table,
                    array(
                        'response_aktiv' => $record->aktiv,
                    ),
                    array('id' => $record->id),
                    array('%d'),
                    array('%d')
                );

                return;
            }

            $wpdb->update(
                $table,
                array(
                    'response_aktiv' => $record->aktiv,
                    'auto_betreff' => $record->auto_betreff,
                    'auto_msg' => $record->auto_msg,
                ),
                array('id' => $record->id),
                array(
                    '%d',
                    '%s',
                    '%s'
                ),
                array('%d')
            );
        }

        public function deleteBsFormular($id): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_formulare;
            $wpdb->delete(
                $table,
                array(
                    'id' => $id
                ),
                array('%d')
            );

            $table = $wpdb->prefix . $this->table_form_message;
            $wpdb->delete(
                $table,
                array(
                    'formId' => $id
                ),
                array('%d')
            );
        }

        public function deleteFormularEmail($id): void
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_email;
            $wpdb->delete(
                $table,
                array(
                    'id' => $id
                ),
                array('%d')
            );
        }

        /**
         * @throws Exception
         */
        public function bs_load_random_string($args = null): string
        {
            if (function_exists('random_bytes')) {
                $bytes = random_bytes(16);
                $str = bin2hex($bytes);
            } elseif (function_exists('openssl_random_pseudo_bytes')) {
                $bytes = openssl_random_pseudo_bytes(16);
                $str = bin2hex($bytes);
            } else {
                $str = md5(uniqid('wp_bs_formulare', true));
            }

            return $str;
        }

        public function getBSFormGenerateRandomId($passwordlength = 12, $numNonAlpha = 1, $numNumberChars = 4, $useCapitalLetter = true): string
        {
            $numberChars = '123456789';
            //$specialChars = '!$&?*-:.,+@_';
            $specialChars = '!$%&=?*-;.,+~@_';
            $secureChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
            $stack = $secureChars;
            if ($useCapitalLetter) {
                $stack .= strtoupper($secureChars);
            }
            $count = $passwordlength - $numNonAlpha - $numNumberChars;
            $temp = str_shuffle($stack);
            $stack = substr($temp, 0, $count);
            if ($numNonAlpha > 0) {
                $temp = str_shuffle($specialChars);
                $stack .= substr($temp, 0, $numNonAlpha);
            }
            if ($numNumberChars > 0) {
                $temp = str_shuffle($numberChars);
                $stack .= substr($temp, 0, $numNumberChars);
            }

            return str_shuffle($stack);
        }

        /**
         * @param $array
         *
         * @return object
         */
        final public function bsArrayToObject($array): object
        {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $array[$key] = self::bsArrayToObject($value);
                }
            }

            return (object)$array;
        }


        public function bsFormGetFormularSettingsByArgs($select): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;
            $table = $wpdb->prefix . $this->table_settings;
            $where = sprintf('WHERE id=%s', BS_FORMULAR_SETTINGS_ID);
            $result = $wpdb->get_row("SELECT {$select} FROM {$table} {$where}");
            if (!$result) {
                return $return;
            }
            $data = json_decode($result->$select);
            $return->status = true;
            $return->$select = $data;

            return $return;
        }

        public function getEmailEmpfangData($args, $fetchMethod = true): object
        {
            global $wpdb;
            $return = new stdClass();
            $return->status = false;
            $return->count = 0;
            $fetchMethod ? $fetch = 'get_results' : $fetch = 'get_row';
            $tm = $wpdb->prefix . $this->table_email;
            $f = $wpdb->prefix . $this->table_formulare;
            $fm = $wpdb->prefix . $this->table_form_message;
            $result = $wpdb->$fetch("SELECT {$tm}.*,DATE_FORMAT({$tm}.created_at, '%d.%m.%Y %H:%i:%s') AS created,
       								  $f.bezeichnung, $f.shortcode								
									  FROM {$tm} 
									  LEFT JOIN {$f} ON {$tm}.form_id = {$f}.id   
									   {$args}");
            if (!$result) {
                return $return;
            }
            $fetchMethod ? $count = count($result) : $count = 1;
            $return->count = $count;
            $return->status = true;
            $return->record = $result;

            return $return;
        }

        public function bsFormSetEmailEmpfang($record): object
        {
            global $wpdb;
            $table = $wpdb->prefix . $this->table_email;
            $wpdb->insert(
                $table,
                array(
                    'form_id' => $record->form_id,
                    'betreff' => $record->betreff,
                    'email_at' => $record->email_at,
                    'abs_ip' => $record->abs_ip,
                    'message' => $record->message,
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
            $return = new stdClass();
            if (!$wpdb->insert_id) {
                $return->status = false;
                $return->msg = 'Daten konnten nicht gespeichert werden!';
                $return->id = false;

                return $return;
            }
            $return->status = true;
            $return->msg = 'Daten gespeichert!';
            $return->id = $wpdb->insert_id;

            return $return;
        }

        public function bsFormSetDefaultSettings($args)
        {
            $this->bsFormDefaultSettings('set');
        }

        private function setDefaultSettings($key, $value)
        {
            global $wpdb;
            $id = BS_FORMULAR_SETTINGS_ID;
            $table = $wpdb->prefix . $this->table_settings;
            $wpdb->insert(
                $table,
                array(
                    'id' => $id,
                    $key => $value,
                ),
                array('%s')
            );
        }

        public function updateDefaultSettings($key, $value)
        {
            $id = BS_FORMULAR_SETTINGS_ID;
            global $wpdb;
            $table = $wpdb->prefix . $this->table_settings;
            $wpdb->update(
                $table,
                array(
                    $key => $value,
                ),
                array('id' => $id),
                array('%s')
            );
        }

        final public function bsFormDefaultSettings($args, $id = false): object
        {
            $defaults = new stdClass();
            $defaults->status = true;
            $meldungen = [
                '0' => [
                    'id' => 1,
                    'type' => 'success_message',
                    'format' => 'success_message',
                    'label' => 'Die Nachricht des Absenders wurde erfolgreich gesendet',
                    'msg' => 'Die Nachricht wurde erfolgreich gesendet.'
                ],
                '1' => [
                    'id' => 2,
                    'format' => 'error_message',
                    'type' => 'senden_error',
                    'label' => 'Die Nachricht des Absenders konnte nicht gesendet werden',
                    'msg' => 'Beim Versuch, Ihre Nachricht zu senden, ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal.'
                ],
                '2' => [
                    'id' => 3,
                    'format' => 'form-message',
                    'type' => 'form_required_fehler',
                    'label' => 'Fehler beim Ausfüllen des Formulars',
                    'msg' => 'Ein oder mehrere Felder haben einen Fehler. Bitte überprüfen Sie es und versuchen Sie es erneut.'
                ],
                '3' => [
                    'id' => 4,
                    'format' => 'spam',
                    'type' => 'mail_spam',
                    'label' => 'Eingabe wurde als Spam erkannt',
                    'msg' => 'Beim Versuch, Ihre Nachricht zu senden, ist ein Fehler aufgetreten. Bitte versuchen Sie es später noch einmal.'
                ],
                '4' => [
                    'id' => 5,
                    'format' => 'dataprotection',
                    'type' => 'akzept_check',
                    'label' => 'Es gibt Bedingungen, die der Absender akzeptieren muss',
                    'msg' => 'Sie müssen die Bedingungen akzeptieren, bevor Sie Ihre Nachricht senden.'
                ],
                '5' => [
                    'id' => 6,
                    'format' => 'required',
                    'type' => 'input_required_fehler',
                    'label' => 'Es gibt ein Feld, das der Absender ausfüllen muss',
                    'msg' => 'Dieses Feld muss ausgefüllt werden.'
                ],
                '6' => [
                    'id' => 7,
                    'format' => 'email',
                    'type' => 'email_format_error',
                    'label' => 'Die eingegebene E-Mail-Adresse des Absenders ist ungültig',
                    'msg' => 'Die eingegebene E-Mail-Adresse ist ungültig.'
                ],
                '7' => [
                    'id' => 8,
                    'format' => 'url',
                    'type' => 'url_format_error',
                    'label' => 'Die eingegebene URL des Absenders ist ungültig',
                    'msg' => 'Die URL ist unzulässig.'
                ],
                '8' => [
                    'id' => 9,
                    'format' => 'date',
                    'type' => 'date_format_error',
                    'label' => 'Das eingegebene Datumsformat ist ungültig',
                    'msg' => 'Das Datumsformat ist falsch.'
                ],
                '9' => [
                    'id' => 10,
                    'format' => 'number',
                    'type' => 'number_format_error',
                    'label' => 'Die eingegebene Zahlenformat ist ungültig',
                    'msg' => 'Das Zahlenformat ist ungültig.'
                ],
                '10' => [
                    'id' => 11,
                    'format' => 'select',
                    'type' => 'select_format_error',
                    'label' => 'Ein Feld aus einer Auswahlliste muss ausgewählt werden.',
                    'msg' => 'Es muss ein Feld ausgewählt werden.'
                ],
                '11' => [
                    'id' => 12,
                    'format' => 'checkbox',
                    'type' => 'checkbox_format_error',
                    'label' => 'Eine Checkbox muss ausgewählt sein.',
                    'msg' => 'Sie müssen dieser Bedingung zustimmen.'
                ],
                '12' => [
                    'id' => 13,
                    'format' => 'email-send-select',
                    'type' => 'email_select_format_error',
                    'label' => 'Eine E-Mail (E-Mail Select) muss aus einer Auswahlliste ausgewählt werden.',
                    'msg' => 'Die ausgewählte E-Mail-Adresse ist ungültig.'
                ],
                '13' => [
                    'id' => 14,
                    'format' => 'file',
                    'type' => 'file_upload_format_error',
                    'label' => 'Ein Dateianhang (File-Upload) muss ausgewählt sein.',
                    'msg' => 'Die ausgewählte Datei ist ungültig.'
                ]

            ];
            switch ($args) {
                case 'set':
                    $dbMeldungen = $this->bsFormGetFormularSettingsByArgs('form_meldungen');
                    if (!$dbMeldungen->status) {
                        $this->setDefaultSettings('form_meldungen', json_encode($meldungen));
                    }

                    return $defaults;
                case'by_id':
                    foreach ($meldungen as $tmp) {
                        if ($id == $tmp['id']) {
                            return (object)$tmp;
                        }
                    }
                    break;
                case 'by_field':
                    $msg = [];
                    foreach ($meldungen as $tmp) {
                        if ($id == $tmp['format']) {
                            $msg[$id] = $tmp['msg'];
                            break;
                        }
                    }
                    if (!$msg) {
                        $msg[$id] = $meldungen[5]['msg'];
                    }

                    return (object)$msg;
                    break;
                default:
                    $defaults->meldungen = json_encode($meldungen);

                    return $defaults;
            }

            return (object)[];
        }

        public function bsFormSelectEmailTemplate($args = null, $id = null):object
        {
            $return = [];
            $select = [
                '0' => [
                    'id' => 1,
                    'bezeichnung' => 'Tabelle'
                ],
                '1' => [
                    'id' => 2,
                    'bezeichnung' => 'individuell'
                ]
            ];

            switch ($args) {
                case 'all':
                    $return = $select;
                    break;
                case'by_id':
                    foreach ($select as $tmp) {
                        if ($tmp['id'] == $id) {
                          $return = $tmp;
                          break;
                        }
                    }
                    break;
            }

            return $this->bsArrayToObject($return);
        }

        public function bsFormularValidateMessageInputs($record, $input, $form = null): object
        {
            $return = new stdClass();
            $type = $record->type;
            switch ($record->type) {
                case'text':
                case'password':
                case'textarea':
                    isset($input) && is_string($input) ? $postValue = sanitize_text_field($input) : $postValue = '';
                    if ($record->required && !$postValue) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->label = $record->label;
                    $return->type = $record->type;
                    $return->eingabe = $input;

                    return $return;
                case'number':
                    if ($record->required && !$input) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    if ($input && !filter_var($input, FILTER_VALIDATE_INT)) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);
                    $return->eingabe = $input;

                    return $return;
                case'email':
                    $email = sanitize_text_field($input);
                    if ($record->required && !$email) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);
                    $input ? $return->eingabe = '<a href=mailto:' . $input . '>' . $input . '</a>' : $return->eingabe = false;

                    return $return;
                case'date':
                    if ($record->required && !$input) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    $regEx = '@^(19|20)\d\d[- /.](0[1-9]|1[012])[- /.](0[1-9]|[12][0-9]|3[01])$@m';
                    $date = filter_var($input, FILTER_VALIDATE_REGEXP,
                        array("options" => array("regexp" => $regEx)));
                    if ($input && !$date) {
                        $return->status = false;
                        //$this->lang = sanitize_text_field($record->label);
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);
                    $return->eingabe = $date;

                    return $return;
                case'select':
                    $input = sanitize_text_field($input);
                    $select = unserialize($record->values);
                    $eingabe = '';
                    if ($select) {
                        foreach ($select as $tmp) {
                            if ($tmp['id'] == $input) {
                                $eingabe = $tmp['bezeichnung'];
                                break;
                            } else {
                                $eingabe = false;
                            }
                        }
                    }

                    if ($record->required && !$eingabe) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    if ($record->required) {
                        $return->eingabe = str_replace('*', '', $eingabe);
                    } else {
                        $return->eingabe = $eingabe;
                    }
                    if (!$return->eingabe) {
                        $return->eingabe = 'nichts ausgewählt';
                    }
                    $return->status = true;

                    $return->user_value = '[' . $record->label . ' - select]';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);

                    return $return;
                case 'email-send-select':

                    $selInput = json_decode(base64_decode($input));
                    $select = unserialize($record->values);

                    isset($selInput->email) ? $eingabe = $selInput->email : $eingabe = false;

                    if ($record->required && !$eingabe) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    if (isset($selInput->email) && !filter_var($selInput->email, FILTER_VALIDATE_EMAIL)) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    if ($record->required) {
                        $return->eingabe = str_replace('*', '', $eingabe);
                    } else {
                        $return->eingabe = $eingabe;
                    }
                    if (!$return->eingabe) {
                        $return->eingabe = 'nichts ausgewählt';
                    }

                    $return->status = true;

                    $return->user_value = '[' . $record->label . ' - select]';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);

                    return $return;
                case 'file':
                    $dir = BS_FILE_UPLOAD_DIR . $record->inputId . DIRECTORY_SEPARATOR;
                    $fileArr = [];
                    foreach (scandir($dir) as $file) {
                        if ($file == "." || $file == "..")
                            continue;
                        $regEx = '/.{9}(.*)$/i';
                        preg_match($regEx, $file, $matches);
                        if ($matches) {
                            $oldName = $dir . $file;
                            $newName = $dir . $matches[1];
                            if (rename($oldName, $newName)) {
                                $name = $newName;
                            } else {
                                $name = $oldName;
                            }
                            $fileArr[] = $name;
                        }
                    }

                    if ($record->required && !$fileArr) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    $fileArr ? $return->eingabe = $fileArr : $return->eingabe = [];
                    $return->status = true;
                    $return->user_value = '';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);

                    return $return;

                case'url':
                    $url = sanitize_text_field($input);
                    if ($record->required && !$url) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    if ($input && !filter_var($url, FILTER_VALIDATE_URL)) {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);
                    $url ? $return->eingabe = '<a href="' . $url . '">' . $url . '</a>' : $return->eingabe = false;

                    return $return;
                case'checkbox':
                    $checked = sanitize_text_field($input);
                    $return->status = true;
                    $return->user_value = '[' . $record->values . ']';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);
                    $return->eingabe = 'ausgewählt';

                    return $return;
                case'radio':
                    $input = sanitize_text_field($input);
                    $select = unserialize($record->values);
                    $eingabe = '';
                    foreach ($select as $tmp) {
                        if ($tmp['id'] == $input) {
                            $eingabe = $tmp['bezeichnung'];
                            break;
                        } else {
                            $eingabe = false;
                        }
                    }
                    $return->eingabe = str_replace('*', '', $eingabe);
                    $return->status = true;
                    $return->user_value = '[' . $record->label . ' - radio]';
                    $return->inputId = $record->inputId;
                    $return->type = $record->type;
                    $return->label = sanitize_text_field($record->label);

                    return $return;
            }

            return (object)[];
        }


        public function validateFormularRadioCheckbox($post, $inputArr, $type): object
        {
            $return = new stdClass();
            switch ($type) {
                case 'checkbox':
                    $postArr = array_keys($post);
                    if (in_array($inputArr->inputId, $postArr)) {
                        $return->is_check = true;

                        return $return;
                    }
                    $return->is_check = false;
                    if ($inputArr->required == 'required') {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }
                    $return->user_value = '[' . $inputArr->values . ']';
                    $return->inputId = $inputArr->inputId;
                    $return->type = $inputArr->type;
                    $return->status = true;
                    $return->label = sanitize_text_field($inputArr->label);
                    $return->eingabe = 'nicht ausgewählt';

                    return $return;
                case'radio':
                    $postArr = array_keys($post);
                    if (in_array($inputArr->inputId, $postArr)) {
                        $return->is_check = true;

                        return $return;
                    }

                    $return->is_check = false;
                    if ($inputArr->required == 'required') {
                        $return->status = false;
                        $msg = apply_filters('bs_form_default_settings', 'by_field', $type);
                        $return->msg = $msg->$type;

                        return $return;
                    }

                    $label = sanitize_text_field($inputArr->label);
                    $return->user_value = '[' . $label . ' - radio]';
                    $return->inputId = $inputArr->inputId;
                    $return->type = $inputArr->type;
                    $return->status = true;
                    $return->label = $label;
                    $return->eingabe = 'nicht ausgewählt';

                    return $return;
            }

            return (object)[];
        }

        /**
         * =====================================================
         * =========== BS-FORMULAR PHP-MAILER CONFIG ===========
         * =====================================================
         */
        public function bs_formular_mailer_smtp_options($phpmailer)
        {
            $phpmailer->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            return $phpmailer;
        }

        public function reArrayFiles($file_post): array
        {
            $file_ary = array();
            $file_count = count($file_post['name']);
            $file_keys = array_keys($file_post);

            for ($i = 0; $i < $file_count; $i++) {
                foreach ($file_keys as $key) {
                    $file_ary[$i][$key] = $file_post[$key][$i];
                }
            }
            return $file_ary;
        }

        public function bsFormDestroyDir($dir): bool
        {
            if (!is_dir($dir) || is_link($dir))
                return unlink($dir);

            foreach (scandir($dir) as $file) {
                if ($file == "." || $file == "..")
                    continue;
                if (!$this->bsFormDestroyDir($dir . "/" . $file)) {
                    chmod($dir . "/" . $file, 0777);
                    if (!$this->bsFormDestroyDir($dir . "/" . $file)) return false;
                }
            }
            return rmdir($dir);
        }

        public function bsFormDeleteFileFolder()
        {
            foreach (scandir(BS_FILE_UPLOAD_DIR) as $dir) {
                if ($dir == "." || $dir == "..")
                    continue;
                if (is_dir(BS_FILE_UPLOAD_DIR . $dir)) {
                    $this->bsFormDestroyDir(BS_FILE_UPLOAD_DIR . $dir);
                }
            }
        }
    }//endClass
}