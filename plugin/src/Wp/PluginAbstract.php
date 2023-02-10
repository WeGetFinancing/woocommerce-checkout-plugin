<?php

namespace WeGetFinancing\WCP\Wp;

abstract class PluginAbstract
{

    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );
    }

    abstract public function init();

    public function addAjaxAction( $action, $functionName ) {

        add_action(
            "wp_ajax_$action",
            [ $this, "$functionName" ]
        );

        add_action(
            'wp_ajax_nopriv_$action',
            [ $this, "$functionName" ]
        );

    }

    public function addAction( $action, $functionName ) {
        add_action( $action, [ $this, $functionName ] );
    }

    public function ajaxRespondJson( array $responseArray ) {

        echo json_encode( $responseArray );

        wp_die();

    }

    public function ajaxRespondString ( $str ) {

        echo $str;

        wp_die();

    }
}
