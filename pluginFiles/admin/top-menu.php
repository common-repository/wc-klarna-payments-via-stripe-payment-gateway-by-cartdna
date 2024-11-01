<?php

function sd_display_top_level_menu_page_klarna()

{



    $klarna = new klarna();

    $menuUrl = menu_page_url('klarnaadminmenu', $echo = false);

    // wp_redirect($menuUrl);

    // exit;



    $gatewayCode = $klarna->gatewayCode;

    $adminUrl = $klarna->adminUrl;

?>

    <div class="m-5 wrap">

        <form action="" method="post" id="verifyClientForm">

            <input type="hidden" name="verify_client_return_url" id="verify_client_return_url" value="<?php echo esc_url($menuUrl) ?>" />

            <input type="hidden" name="verify_client_gateway_code" id="verify_client_gateway_code" value="<?php echo esc_attr($gatewayCode) ?>" />

            <div class="form-group col-md-6">

                <label for="verify_client_domain_url">Home page url</label>

                <input type="text" class="form-control" name="verify_client_domain_url" id="verify_client_domain_url">

                <small id="verify_client_domain_url_help" class="form-text text-muted">please provide your complete website home url( https://example.com)</small>

            </div>

            <div class="form-group col-md-6">

                <label for="verify_client_verify_token">Security Token</label>

                <input type="text" class="form-control" name="verify_client_verify_token" id="verify_client_verify_token">



            </div>

            <div class="form-group col-md-6 mt-4">

                <button type="submit" class="btn btn-primary" id="clientVerifyButton">Verify</button>

            </div>

        </form>
        <div>
            please <a href="<?php echo esc_url($adminUrl) ?>" target="_blank">login</a> here to get your verify token
        </div>

        <div class="mt-3 alert alert-primary" style="display:none" id="showAlertKlarna">

            <span id="showClientVerifyFailedMessage"></span>

        </div>
        <div class="mb-3"></div>
        <div class="row" id="showMerchantFormData" style="display:none">
            <div class="col-xl-1 col-lg-1 col-md-12"></div>
            <div class="col-xl-9 col-lg-9 col-md-12 ">
                <div class="card" style="max-width: 100%;">
                    <h5 class="card-header">Merchant Information</h5>
                    <div class="card-body">
                        <form name="create-config-form" class="needs-validation" method="post" action="" id="createMerchantConfigForm">
                            <div id="merchantDetails"></div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-xl-1 col-lg-1 col-md-12"></div>
        </div>
    </div>




<?php

}

?>