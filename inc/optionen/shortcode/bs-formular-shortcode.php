<?php


namespace Form\BsFormular;

use stdClass;

defined( 'ABSPATH' ) or die();

/**
 * BS-FORMULAR SHORTCODE
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

if ( ! class_exists( 'BsFormularShortCode' ) ) {
    add_action( 'after_setup_theme', array( 'Form\\BsFormular\\BsFormularShortCode', 'init' ), 0 );

    class BsFormularShortCode {

        //INSTANCE
        private static $instance;

        /**
         * @return static
         */
        public static function init(): self {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        public function __construct() {
            add_shortcode( 'bs-formular', array( $this, 'bs_formular_shortcode' ) );

        }

        public function bs_formular_shortcode( $atts, $content, $tag ): string {

            $a = shortcode_atts( array(
                'id'       => ''

            ), $atts );

            ob_start();
            if ( ! $a['id'] ) {
                return '';
            }

            $shortcode = $a['id'];
            $args = sprintf( 'WHERE shortcode="%s"', $shortcode );
            $data = apply_filters('get_formulare_by_args', $args, false);
            if(!$data->status){
                return '';
            }

            $data = $data->record;
            $inputs = unserialize($data->inputs);
            $layout = html_entity_decode($data->layout);

            foreach($inputs as $tmp){
                $input = html_entity_decode($tmp->html);
                $layout = str_replace('###'.$tmp->inputId.'###', $input, $layout);
            }
            $wrapper = $this->formular_wrapper($shortcode, $data->form_class);
            $html = $wrapper->top;
            $html .= $layout;
            $html .= $wrapper->bottom;
            $html = preg_replace(array('/<!--(.*)-->/Uis',"/[[:blank:]]+/"),array('',' '),str_replace(array("\n","\r","\t"),'', $html));
            echo $html;
            return ob_get_clean();
        }//endFormularOut


        private function formular_wrapper($id, $formClass):object
        {
        	if($formClass){
        		$formStart = '<div class="' . $formClass . '">';
        		$formEnd = '</div>';
	        } else {
		        $formStart = '';
		        $formEnd = '';
	        }
        	$randomId = apply_filters('bs_get_random_string', false);
            $record = [];
            $record['top'] = '
				  <div class="bs-formular-wrapper">
				  <form class="send-bs-formular needs-validation" action="#" method="post" novalidate>
                  <input type="hidden" name="id" value="'.$id.'" />
                  <input type="hidden" name="formId" value="'.$randomId.'" />
                  <input class="terms" type="checkbox" name="terms">
                  '.$formStart.'';
            $record['bottom'] = $formEnd . '
			<div id="error'.$randomId.'" class="alert alert-danger mt-3 d-none" role="alert"></div>
			<div id="success'.$randomId.'" class="alert alert-success mt-3 d-none" role="alert"></div>
			</form></div>';
            return (object) $record;
        }

    }//endClass
}