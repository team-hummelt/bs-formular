<?php
defined( 'ABSPATH' ) or die();

/**
 * BS-Formular OPTIONEN
 * @package Hummelt & Partner WordPress Plugin
 * Copyright 2021, Jens Wiecker
 * License: Commercial - goto https://www.hummelt-werbeagentur.de/
 * https://www.hummelt-werbeagentur.de/
 */

class BSFormularWidget extends WP_Widget
{

    /**
     * Constructs the new widget.
     *
     * @see WP_Widget::__construct()
     */
    function __construct()
    {
        // Instantiate the parent object.
        parent::__construct(false, __('BS Forms', 'bs-formular'));
    }

    /**
     * The widget's HTML output.
     *
     * @param array $args Display arguments including before_title, after_title,
     *                        before_widget, and after_widget.
     * @param array $instance The settings for the particular instance of the widget.
     *
     * @see WP_Widget::widget()
     *
     */

    function widget( $args, $instance ) {
        $args     = (object) $args;
        $instance = (object) $instance;
        $header   = empty( $instance->header ) ? ' ' : apply_filters( 'widget_title', $instance->header );
        $selectForm = empty( $instance->selectForm ) ? '' : $instance->selectForm;
        $isColor  = empty( $instance->isColor ) ? '' : $instance->isColor;
        $type = empty( $instance->type ) ? '' : $instance->type;

        echo( $args->before_widget ?? '' );
        echo $args->before_title . $header . $args->after_title;
        echo '<div class="pt-2">' . do_shortcode('[bs-formular id="'.$selectForm.'"]').'</div>';

        echo( $args->after_widget ?? '' );
    }

    /**
     * The widget update handler.
     *
     * @param array $new_instance The new instance of the widget.
     * @param array $old_instance The old instance of the widget.
     *
     * @return array The updated instance of the widget.
     * @see WP_Widget::update()
     *
     */
    function update( $new_instance, $old_instance ): array {

        $instance             = $old_instance;
        $instance['header']   = $new_instance['header'];
        $instance['selectForm'] = $new_instance['selectForm'];
        $instance['isColor']  = $new_instance['isColor'];
        $instance['type']     = $new_instance['type'];
        return $instance;
    }

    /**
     * Output the admin widget options form HTML.
     *
     * @param array $instance The current widget settings.
     *
     * @return void The HTML markup for the form.
     */
    function form( $instance ): void {
        $instance = wp_parse_args( (array) $instance, array(
            'title' => __( 'BS Forms', 'bs-formular' ),
        ) );

        $header   = filter_var( $instance['header'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH ) !== null ? esc_attr( $instance['header'] ) : __( 'Social Media', 'bootscore' );
        $selectForm = filter_var( $instance['selectForm'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH ) !== null ? esc_attr( $instance['selectForm'] ) : '';
        $isColor  = ( isset( $instance['isColor'] ) && is_numeric( $instance['isColor'] ) ) ? (int) $instance['isColor'] : '';
        $type     = ( isset( $instance['type'] ) && is_numeric( $instance['type'] ) ) ? (int) $instance['type'] : 1;

        ?>
        <p>
            <label for="<?= $this->get_field_id( 'header' ); ?>"><?= __( 'Title', 'bs-formular' ) ?>
                <input class="widefat" id="<?= $this->get_field_id( 'header' ); ?>"
                       name="<?= $this->get_field_name( 'header' ); ?>" type="text"
                       value="<?= esc_attr( $header ); ?>"/>
            </label>
        </p>
        <div>
            <p>
                <div class="d-flex flex-column">
                <label  for="<?= $this->get_field_id( 'selectForm' ); ?>"><?= __( 'Select form', 'bs-formular' ) ?></label>
                <select id="<?= esc_attr( $this->get_field_id( 'selectForm' ) ); ?>"
                        name="<?=( $this->get_field_name( 'selectForm' ) ) ?>">
                    <option value=""><?= __( 'select', 'bs-formular' ) ?>...</option>
                <?php
                $forms = apply_filters('get_formulare_by_args', false);
                if($forms->status):
                    foreach ($forms->record as $tmp):?>
                     <option value="<?=$tmp->shortcode?>" <?=esc_attr(selected(true, $selectForm == $tmp->shortcode))?>><?=$tmp->bezeichnung?></option>
                 <?php endforeach;  endif;?>
                </select>
            </div>
            </p>
        </div>
        <?php
    }
}


add_action( 'widgets_init', 'bs_formular_register_widget' );
/**
 * Register the new widget.
 *
 * @see 'widgets_init'
 */
function bs_formular_register_widget(): void {
    register_widget( 'BSFormularWidget');
}