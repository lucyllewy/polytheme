<?php

add_action('init', 'polytheme_register_polymer');
function polytheme_register_polymer() {
	wp_register_script( 'web-components', get_template_directory_uri().'/bower_components/webcomponentsjs/webcomponents.min.js' );
}

add_action('wp_enqueue_scripts', 'polytheme_enqueue_polymer');
function polytheme_enqueue_polymer() {
	wp_enqueue_script( 'web-components' );
}