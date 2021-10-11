<?php
defined( 'ABSPATH' ) or die();
/**
 * Gutenberg POST SELECTOR REST API ENDPOINT
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */

//TODO JOB REST API ENDPOINT
add_action( 'rest_api_init', 'bs_formular_rest_endpoint_api_handle' );

function bs_formular_rest_endpoint_api_handle() {
	register_rest_route( 'bs-formular-endpoint/v1', '/method/(?P<method>[\S]+)', [
		'method'              => WP_REST_Server::EDITABLE,
		'permission_callback' => function () {
			return current_user_can( 'edit_posts' );
		},
		'callback'            => 'bootstrap_formular_rest_endpoint_get_response',
	] );
}

function bootstrap_formular_rest_endpoint_get_response( $request ): WP_REST_Response {
	$method = $request->get_param( 'method' );

	if ( empty( $method ) ) {
		return new WP_REST_Response( [
			'message' => 'Method not found',
		], 400 );
	}
	$response = new stdClass();
	$forms = [];
	switch ($method) {
		case 'get_bs_formular':
			$form = apply_filters('get_formulare_by_args', false);
			if($form->status){
				foreach ($form->record as $tmp){
					$form_items = [
						'id' => $tmp->shortcode,
						'name' => $tmp->bezeichnung
					];
					$forms[] =  $form_items;
				}
			}
			break;
	}
	$response->forms  = $forms;

	return new WP_REST_Response( $response, 200 );
}