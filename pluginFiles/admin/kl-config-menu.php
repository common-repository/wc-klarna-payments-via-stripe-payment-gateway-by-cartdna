<?php

function sd_display_sub_menu_page_klarna()

{

    $klarna = new klarna();

    $gatewayCode = $klarna->gatewayCode;

?>



    <div class="m-5">

        <form action="" method="post" id="updateClientForm" name="updateClientForm" style="display:none">

            <input type="hidden" name="update_client_gateway_code" id="update_client_gateway_code" value="<?php echo esc_attr($gatewayCode) ?>" />

        </form>

        <div class="mt-3 alert alert-primary" id="showAlertKlarna">

            <span id="showClientVerifyFailedMessage"></span>

        </div>
        <div class="row" id="showConfiguredGatewayForm" style="display:none">
            <div class="col-xl-2 col-lg-2 col-md-12"></div>
            <div class="col-xl-8 col-lg-8 col-md-12 ">
                <div class="section-block text-center" id="basicform">
                    <h3 class="section-title"><span id="paymentGatewayName"></span> Payment Gateway IPG Configuration</h3>
                </div>
                <div class="card" style="max-width: 100%;">

                    <form class="needs-validation" method="post" action="" id="updateGatewayConfigForm">
                        <h5 class="card-header">Merchant Configuration Information</h5>
                        <div class="card-body">
                            <p class=" ml-5" id="isLiveModeActivated"></p>
                            <div class="alert" role="alert" id="updateAlertBox" style="display:none">
                                <span id="showUpdateMessage"></span>
                            </div>
                            <div id="showConfigFormData"></div>

                            <div class="row ">
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mb-3">

                                    <h5>Test Mode</h5>
                                    <label class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="gateway_transaction_mode_new" class="custom-control-input gateway_transaction_mode_method_test" value="test"><span class="custom-control-label">Test</span>
                                    </label>
                                    <label class="custom-control custom-radio custom-control-inline">
                                        <input type="radio" name="gateway_transaction_mode_new" class="custom-control-input gateway_transaction_mode_method_live" value="live"><span class="custom-control-label">Live</span>
                                    </label>
                                </div>
                                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
                                    <button class="btn btn-primary" type="submit"><span id="UpdateConfigFormSubmitButton">Update</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-12"></div>
        </div>

    </div>




<?php

}

?>