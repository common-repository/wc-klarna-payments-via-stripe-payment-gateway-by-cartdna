<?php
function klarna_gateway_wpb_hooks_custom_css()
{
    $plugin_dir_path = plugin_dir_url(__FILE__);
    wp_enqueue_style('payme-bootstrap-css-file', $plugin_dir_path . 'css/option.css');
}
// function klarna_gateway_footer_js_scripts()
// {
//     $plugin_dir_path = plugin_dir_url(__FILE__);
//     print_r($plugin_dir_path);
//     exit();
//     wp_enqueue_script('klarna-admin-main-menu-page-jquery', plugin_dir_url(__FILE__) . '/js/adminSubMenupage.js', array('jquery'));
//     wp_enqueue_style('payme-bootstrap-css-file', $plugin_dir_path . 'css/bootstrap.min.css');
//     wp_localize_script(
//         'klarna-admin-main-menu-page-jquery',
//         'klarna_plugin_ajax_object_verify_client',
//         array('ajax_url' => admin_url('admin-ajax.php'))
//     );
// }
function klarna_enqueue_admin_scripts($hooks)
{

    if ($hooks === 'toplevel_page_klarnaadminmenu') {
        $plugin_dir_path = plugin_dir_url(__FILE__);
        wp_enqueue_script('klarna-admin-main-menu-page-jquery', plugin_dir_url(__FILE__) . '/js/adminMenupage.js', array('jquery'));
        wp_localize_script(
            'klarna-admin-main-menu-page-jquery',
            'klarna_plugin_ajax_object_verify_client',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );
        wp_enqueue_style('klarna-bootstrap-css-file', $plugin_dir_path . 'css/bootstrap.min.css');
        wp_enqueue_style('klanra-input-error-css-file', $plugin_dir_path . 'css/inputError.css');
        // wp_enqueue_script('payme-qr-js-file', $plugin_dir_path . 'js/bootstrap.min.js', array('jquery'));
    }
    if ($hooks === 'klarna_page_klarnaadminsubmenu') {
        $plugin_dir_path = plugin_dir_url(__FILE__);
        wp_enqueue_script('klarna-admin-main-menu-page-jquery', plugin_dir_url(__FILE__) . 'js/adminSubMenupage.js', array('jquery'));
        wp_enqueue_style('payme-bootstrap-css-file', $plugin_dir_path . 'css/bootstrap.min.css');
        wp_localize_script(
            'klarna-admin-main-menu-page-jquery',
            'klarna_plugin_ajax_object_verify_client',
            array('ajax_url' => admin_url('admin-ajax.php'))
        );
    }
}



function verifyKlarnaClientSecurityToken_callback()
{
    $klarna = new klarna();
    $pageUrl = $klarna->verifyUrl;
    $data = array();

    if (!isset($_POST['return_url']) or !isset($_POST['home_url']) or !isset($_POST['gateway_code']) or !isset($_POST['verify_token'])) {
        $data = array('res' => "error");
        echo json_encode($data);
        wp_die();
        exit();
    }
    $returnUrl = sanitize_text_field($_POST['return_url']);
    $homeUrl = sanitize_text_field($_POST['home_url']);
    $gatewayCode = sanitize_text_field($_POST['gateway_code']);
    $verifyToken = sanitize_text_field($_POST['verify_token']);

    $getHostName = klarna::getDomainName($returnUrl);
    $getClientDomain = klarna::getHomeDomainName($homeUrl);

    if ((strpos($getClientDomain, $getHostName) !== 0)) {
        $data = array('res' => "error");
        echo json_encode($data);
        wp_die();
        exit();
    }
    $requestData = array(
        'return_url' => $returnUrl,
        'gateway_code' => $gatewayCode,
        'verify_token' => $verifyToken,
        'host_name' => $getClientDomain
    );

    $args = array(
        'body' => $requestData,
        'headers' => array(
            'Content-Type: application/json'
        ),
    );

    $response = wp_remote_post($pageUrl, $args);
    if ($response['response']['code'] === 200) {
        $responseData = json_decode($response['body'], true);
        if ($responseData['result'] === 'success') {
            $clientDetails = $responseData['detailsData'];
            $formData = $responseData['data'];

            $htmlData = $klarna->constructConfigForm($clientDetails, $formData);
            $data = array(
                'res' => "success",
                'html' => $htmlData
            );
        } elseif ($responseData['result'] === 'failed') {
            $data = array('res' => "error");
        } elseif ($responseData['result'] === 'duplicate') {
            $data = array(
                'res' => "duplicate",
            );
        } elseif ($responseData['result'] === 'reinstall') {
            $data = array(
                'res' => "reinstall",
            );
        }
    }
    echo json_encode($data);
    wp_die();
}

function showKlarnaClientConfigRecords_callback()
{
    global $wpdb;
    $klarna = new klarna();
    $table_name = $wpdb->prefix . 'klarna_payment_gateway_token_oganro';
    $gatewayCode = $klarna->gatewayCode;
    $pageUrl = $klarna->getConfigData;
    if (!isset($_POST['gateway_code'])) {
        $data = array('res' => "error");
        echo json_encode($data);
        wp_die();
        exit();
    }
    $gatewayCode = sanitize_text_field($_POST['gateway_code']);
    $getHostName = $wpdb->get_results("SELECT * FROM $table_name WHERE gateway = '" . $gatewayCode . "'");
    $hostName = $getHostName[0]->domain;
    $requestData = array(
        'host_name' => $hostName,
        'gateway_code' => $gatewayCode
    );

    $args = array(
        'body' => $requestData,
        'headers' => array(
            'Content-Type: application/json'
        ),
    );
    $data = array();
    $response = wp_remote_post($pageUrl, $args);
    if ($response['response']['code'] === 200) {
        $responseData = json_decode($response['body'], true);

        if ($responseData['result'] === 'error') {
            $data = array('res' => "error");
        } elseif ($responseData['result'] === 'success') {
            $htmlData = $klarna->constructUpdateConfigForm($responseData);

            $data = array(
                'res' => "success",
                'html' => $htmlData,
                'provider' => $responseData['provider'],
                'testMode' => $responseData['testMode'],
                'isLiveActivated' => $responseData['isLiveActivated']
            );
        }
    }
    echo json_encode($data);
    wp_die();
}

function storeKlarnaConfigData_callback()
{
    $data = array();
    $klarna = new klarna();
    $pageUrl = $klarna->storeConfig;
    if (!isset($_POST['formData'])) {
        $data = array('res' => "error");
        echo json_encode($data);
        wp_die();
        exit();
    }

    $requestData = klarna::sanitizeInput($_POST['formData']);
    $args = array(
        'body' => $requestData,
        'headers' => array(
            'Content-Type: application/json'
        ),
    );
    $response = wp_remote_post($pageUrl, $args);
    if ($response['response']['code'] === 200) {
        $responseData = json_decode($response['body'], true);
        if ($responseData['result'] === 'success') {
            $data = array(
                'res' => "success",
            );
        } else {
            $data = array(
                'res' => "error",
            );
        }
    } else {
        $data = array(
            'res' => "error",
        );
    }
    echo json_encode($data);
    wp_die();
}

function updateKlarnaClientConfigRecords_callback()
{
    $data = array();
    $klarna = new klarna();
    $pageUrl = $klarna->updateConfigData;
    if (!isset($_POST['formData'])) {
        $data = array('res' => "error");
        echo json_encode($data);
        wp_die();
        exit();
    }
    $requestData = klarna::sanitizeInput($_POST['formData']);

    $args = array(
        'body' => $requestData,
        'headers' => array(
            'Content-Type: application/json'
        ),
    );
    // print_r($args);
    // print_r($pageUrl);
    // exit();
    $response = wp_remote_post($pageUrl, $args);
    // print_r($response);
    // exit();
    if ($response['response']['code'] === 200) {
        $responseData = json_decode($response['body'], true);
        if ($responseData['result'] === 'success') {
            $data = array(
                'res' => "success",
                'msg' => $responseData['message']
            );
            echo json_encode($data);
            wp_die();
        }
    }
}
