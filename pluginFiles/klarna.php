<?php



class klarna

{

    public $gatewayCode;

    public $hostName;

    public $klarnaIndex;

    public $unInstallUrl;

    public $verifyUrl;

    public $getConfigData;

    public $updateConfigData;

    public $adminUrl;

    public $storeConfig;


    //http://carttest.test/public/api
    function __construct()

    {

        $this->gatewayCode = "klarna_xm97H8wUW";

        $this->hostName = $_SERVER['HTTP_HOST'];

        $this->klarnaIndex = "https://wp.cartdna.com/app/api/klarnaVtwo/index";

        $this->unInstallUrl = "https://wp.cartdna.com/app/api/activate/delete-config";

        $this->verifyUrl = "https://wp.cartdna.com/app/api/activate/verify";

        $this->storeConfig = "https://wp.cartdna.com/app/api/activate/create-config";

        $this->getConfigData = "https://wp.cartdna.com/app/api/activate/get-config-data";

        $this->updateConfigData = "https://wp.cartdna.com/app/api/activate/update-config";

        $this->adminUrl = "https://wp.cartdna.com/";
    }



    public static function getDomainName($url)

    {

        $splitUrl = explode('://', $url);

        $getDomain = explode('/wp-admin', $splitUrl[1]);

        $result = $getDomain[0];

        return $result;
    }



    public static function getHomeDomainName($url)

    {



        $splitUrl = explode('://', $url);

        if (count($splitUrl) < 2 or count($splitUrl) > 2) {

            return 'wrongDomain';
        }



        $damainFullName = $splitUrl[1];

        $domainName = '';

        $checkLastChar = substr($damainFullName, -1);

        if ($checkLastChar === '/') {

            $domainName = substr($damainFullName, 0, -1);
        } else {

            $domainName = $damainFullName;
        }



        return $domainName;
    }

    public function constructConfigForm($details, $data)
    {
        $formData = '  
    
    <input type="hidden" class="form-control" id="validationCustom03" name="config_id" value="' . esc_attr($details['config_id']) . '">
                    <input type="hidden" class="form-control" id="validationCustom044" name="hostname" value="' . esc_attr($details['hostname']) . '">
                    <input type="hidden" class="form-control" id="validationCustom045" name="providerId" value="' . esc_attr($details['providerId']) . '">
                    
    <div class="row  mb-2">
    ' . $this->constructInput('First Name', $details['firt_name']) . '
    ' . $this->constructInput('First Name', $details['last_name']) . '
    ' . $this->constructInput('First Name', $details['provider']) . '
   
    </div>';

        $formData .= $this->getCredentialsCard('Test Credentials', $data);

        $formData .= '
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 ">
        <button class="btn btn-primary" type="submit"><span id="submitConfigFormButton">Submit</span></button>
    </div>
    </div>
    </div>
    </div>
    ';


        return $formData;
    }

    public static function sanitizeInput($data)
    {
        $array = array();
        for ($i = 0; $i < count($data); $i++) {

            $array[$data[$i]['name']] = sanitize_text_field($data[$i]['value']);
        }
        return $array;
    }

    public function constructUpdateConfigForm($data)
    {
        $formData = '
        <input type="hidden" class="form-control" id="config_id" name="config_id" value="' . esc_attr($data['config_id']) . '">
        <div class="row  mb-2">
        ' . $this->constructInput('First Name', $data['firt_name']) . '
        ' . $this->constructInput('Last Name', $data['last_name']) . '
        ' . $this->constructInput('Provider', $data['provider']) . '
        </div>
        ';
        $formData .= $this->getCredentialsCard('Test Credentials', $data['formData'], $data['configTestData'], '_test');
        $formData .= $this->getCredentialsCard('Live Credentials', $data['formData'], $data['configLiveData'], '_live', "klarna_live_data");
        return $formData;
    }

    public function constructInput($label, $value)
    {
        return ' <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-2">
        <label for="validationCustom04">' . esc_html($label) . '</label>
        <input type="text" class="form-control" id="validationCustom04" value="' . esc_attr($value) . '" readonly>
    </div>';
    }

    public function getValue($value, $key)
    {
        if (isset($value)) {
            return $value[$key];
        } else {
            return "";
        }
    }

    public function getCredentialsCard($heading, $data, $value = "", $type = "", $class = "")
    {
        $formData =  '
        <div class="card ' . esc_attr($class) . '" style="max-width: 100%;">
        <h5 class="card-header">' . esc_html($heading) . '</h5>
        <div class="card-body">
        <div class="row ">
        ';
        for ($i = 0; $i < count($data); $i++) {
            foreach ($data[$i] as $label => $key) {

                $formData .= '
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 mb-3">
        
            <label for="' . esc_attr($key) . '">' . esc_html($label) . '</label>
            <input type="text" class="form-control" name="' . esc_attr($key) . esc_html($type) . '" id="' . esc_attr($key) . esc_html($type) . '" style="border-color:black" value="' .  esc_attr($this->getValue($value, $key)) . '">
        
    </div>
            ';
            }
        }

        $formData .= '
        </div>
        </div>
        </div>
        </div>
        ';
        return $formData;
    }
}
