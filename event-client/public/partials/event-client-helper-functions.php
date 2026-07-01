<?php

// This will allow Cross Origin
if ( ! function_exists( 'enable_cors' ) ) {
    function enable_cors(){
        header("Access-Control-Allow-Origin: *");
    }
    add_action('init','enable_cors');    
}