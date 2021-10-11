<?php

defined( 'ABSPATH' ) or die();

/**
 * ADMIN BS-FORMULAR HANDLE
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

if ( current_user_can( 'manage_options')) {
	$id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
	global $wpdb;
	$table = $wpdb->prefix . 'bs_post_eingang';
	$args = sprintf('WHERE %s.id=%d',$table, $id);
	$message = apply_filters('get_email_empfang_data',$args, false);

	if(!$message->status){
		die('File not found');
	}

	/*$message = html_entity_decode($message->record->message);
	$message = stripslashes_deep($message);
	$file = BS_FORMULAR_INC . 'optionen/Mailer/email-template.html';
	file_put_contents($file, $message, LOCK_EX);

	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mimeType = $finfo->file($file);
	header("Content-Type: $mimeType");
	readfile($file);
	$message = '';
	file_put_contents($file, $message, LOCK_EX);*/
}