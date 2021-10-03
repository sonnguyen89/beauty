<?php
if ( ! function_exists( 'gymx_custom_styles' ) ) {
    add_action( 'wp_enqueue_scripts', 'gymx_custom_styles');
    function gymx_custom_styles() {
        wp_enqueue_style( 'gymx-custom-style', get_stylesheet_directory_uri() . '/style.css', array() );
    }
}
if ( ! function_exists( 'gymx_custom_script' ) ) {
    add_action( 'wp_enqueue_scripts', 'gymx_custom_script');
    function gymx_custom_script() {
        wp_enqueue_script( 'gymx-custom-script', get_template_directory_uri() . '/js/custom.js',array('jquery') );
    }
}