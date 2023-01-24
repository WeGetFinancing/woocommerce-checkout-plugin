<?php

namespace WeGetFinancing\WCP\Wp;

class ViewHelper {

    public static function includeWithVariables(
        $filePath, array $variables = [], $print = true, array $subs = null) {

        $htmlPositions = [

            "::after_begin_head::" => "<head>",
            "::before_end_head::" => "</head>",
            "::after_begin_body::" => "<body>",
            "::before_end_body::" => "</body>"

        ];

        $output = NULL;

        if( file_exists( $filePath ) ){

            // Extract the variables to a local namespace
            extract( $variables );

            // Start output buffering
            ob_start();

            // Include the template file
            include( $filePath );

            // End buffering and return its contents
            $output = ob_get_clean();

            if( $subs !== null ) {

                foreach ($subs as $pattern => $replacement) {

                    if( array_key_exists( $pattern, $htmlPositions ) ) {

                        if( is_callable( $replacement) ) {

                            $replacement = $replacement( $variables );

                        }

                        if( strpos( $pattern, "after" ) !== FALSE ) {

                            $replacement = "\n" . $htmlPositions[ $pattern ] . "\n" . $replacement;

                        } else {

                            $replacement .= "\n" . $htmlPositions[ $pattern ] . "\n\n";

                        }

                        $output = preg_replace( "#".$htmlPositions[ $pattern ]."#", $replacement, $output );

                    }

                    if( is_callable( $replacement) ) {

                        $replacement = $replacement( $variables );

                    }

                    $output = preg_replace( "#$pattern#", $replacement, $output );

                }

            }

        } else {

            die( "$filePath does not exists" );

        }

        if ( $print ) {

            print $output;

        }

        return $output;

    }

}
