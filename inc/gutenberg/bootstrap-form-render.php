<?php
defined( 'ABSPATH' ) or die();
/**
 * Gutenberg BS FORMULAR REST API CALLBACK
 * @package Hummelt & Partner WordPress Theme
 * Copyright 2021, Jens Wiecker
 * https://www.hummelt-werbeagentur.de/
 */

function callback_bootstrap_formular_block( $attributes ) {

	return apply_filters( 'gutenberg_block_bs_formular_render', $attributes);
}

function gutenberg_block_bs_formular_render_filter($attributes) {

	if ($attributes ) {
		ob_start(); ?>
        <div class="custom-form-bs-wrapper <?= isset($attributes['className']) &&  $attributes['className'] ? $attributes['className'] : ''?>">
       <?php
		echo do_shortcode('[bs-formular id="'.$attributes['selectedFormular'].'"]');
       ?>
        </div>
        <?php
		return ob_get_clean();
	}
}