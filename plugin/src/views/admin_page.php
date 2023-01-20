
<h1>
    TEST ADMIN AREA
</h1>

<form action="" id="settings-form">
    <input type="text" id="username" name="username" placeholder="Username" value="<?php echo get_option('wgf_username', ''); ?>">

    <button type="submit" class="btn btn-primary">
        Save
    </button>
</form>


<div id="responseDiv">

</div>

<script type="text/javascript">

    function serializeForm( formID ) {

        var form = document.getElementById( formID );

        var inputs = form.querySelectorAll( 'input, select, textarea' );

        var d = {};

        for( let i = 0; i < inputs.length; i++ ) {

            //t = jQuery( inputs[i] ).data();
            d[ jQuery( inputs[i] ).attr('name') ] = jQuery( inputs[i] ).val();

        }

        return JSON.stringify(d);

    }

    function ajaxSubmit(){

        var settingsForm = serializeForm( 'settings-form' );

        console.log("PASSED HERE: ");
        console.log(settingsForm);

        var data = {
            'action': '{{saveSettingsFormAction}}',
            'data': btoa(settingsForm)
        };

        jQuery.post(ajaxurl, data, function( response ) {

            console.log("RESPONSE:\n" + response);

            document.getElementById("responseDiv").innerHTML = response;

            //location.reload();

        });
        //return false;
    }

    function saveMenu() {

        document.getElementById("responseDiv").innerHTML = "<img src='{{ajaxLoaderGifUrl}}' width='32' height='32' />";

        ajaxSubmit();

    }

    jQuery(document).ready(
        function() {

            jQuery("#settings-form").submit(function (e) {
                e.preventDefault();

                ajaxSubmit();
            });

        }
    );

</script>
