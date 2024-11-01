jQuery(document).ready(function () {
  // jQuery.ajax({ headers: { "Access-Control-Allow-Origin": "*" } });

  jQuery("#verifyClientForm").submit(function (e) {
    e.preventDefault();

    var data = {
      action: "verifyKlarnaClientSecurityToken",

      return_url: jQuery("#verify_client_return_url").val(),

      gateway_code: jQuery("#verify_client_gateway_code").val(),

      verify_token: jQuery("#verify_client_verify_token").val().trim(),

      home_url: jQuery("#verify_client_domain_url").val().trim(),
    };

    if (!data.home_url.includes("http")) {
      jQuery("#verify_client_domain_url_help").removeClass("text-muted");

      jQuery("#verify_client_domain_url_help").addClass("text-danger");
    } else {
      jQuery("#verify_client_domain_url_help").removeClass("text-danger");

      jQuery("#verify_client_domain_url_help").addClass("text-muted");

      var ajax_url = klarna_plugin_ajax_object_verify_client.ajax_url;

      jQuery.ajax({
        url: ajax_url,

        type: "POST",

        data: data,

        dataType: "json",

        beforeSend: function () {
          jQuery("#showAlertKlarna").show();

          jQuery("#showClientVerifyFailedMessage").html(
            "Please do not close or move to another window/tab as it is processing..."
          );

          jQuery("#showClientVerifyFailedMessage").css("color", "blue");
          jQuery("#showMerchantFormData").hide();
        },

        success: function (data) {
          if (data.res === "error") {
            jQuery("#showClientVerifyFailedMessage").html(
              "* we are unable to verify your identity! for further details please contact our support team"
            );

            jQuery("#showClientVerifyFailedMessage").addClass("alert-danger");
          } else if (data.res === "success") {
            jQuery("#showClientVerifyFailedMessage").html(
              "You have been successfully verified!! please wait until we load the configuration page"
            );

            jQuery("#showClientVerifyFailedMessage").css("color", "green");
            jQuery("#merchantDetails").html(data.html);
            jQuery("#clientVerifyButton").prop("disabled", true);
            jQuery("#showAlertKlarna").hide();
            jQuery("#showMerchantFormData").show();
            jQuery("#showClientVerifyFailedMessage").html("");

            // jQuery("#showClientConfigPage").attr("src", data.url);
          } else if (data.res === "duplicate") {
            jQuery("#showClientVerifyFailedMessage").html(
              "You have already successfully verified and  configured the gateway configuration. if you like to update the defails please visit 'Configuration' in the payment admin section!!!"
            );

            jQuery("#showClientVerifyFailedMessage").css("color", "red");
          } else if (data.res === "reinstall") {
            jQuery("#showClientVerifyFailedMessage").html(
              "You have successfully verified and  configured the gateway configuration. if you like to update the defails please visit 'Configuration' in the payment admin section!!!"
            );

            jQuery("#showClientVerifyFailedMessage").css("color", "green");
          }
        },
      });
    }
  });

  jQuery("#createMerchantConfigForm").submit(function (e) {
    e.preventDefault();
    // var queryString = jQuery("#createMerchantConfigForm").serialize();
    var formArray = jQuery("#createMerchantConfigForm").serializeArray();
    let isFormValid = true;
    formArray.forEach((element, index) => {
      if (element.value.trim() === "") {
        isFormValid = false;
        jQuery(`#${element.name}`).css("border-color", "red");
      } else {
        jQuery(`#${element.name}`).css("border-color", "black");
      }
    });
    if (isFormValid) {
      var data = {
        action: "storeKlarnaConfigData",
        formData: formArray,
      };

      var ajax_url = klarna_plugin_ajax_object_verify_client.ajax_url;

      jQuery.ajax({
        url: ajax_url,

        type: "POST",

        data: data,

        dataType: "json",

        beforeSend: function () {
          jQuery("#showAlertKlarna").hide();
          jQuery("#submitConfigFormButton").html("please wait");
          jQuery("#submitConfigFormButton").prop("disabled", true);
        },
        success: function (data) {
          jQuery("#showAlertKlarna").show();
          if (data.res === "error") {
            jQuery("#showClientVerifyFailedMessage").css("color", "red");
            jQuery("#showClientVerifyFailedMessage").html(
              "* Something Went Wrong!! for further details please contact our support team"
            );
            jQuery("#submitConfigFormButton").html("Submit");
            jQuery("#submitConfigFormButton").prop("disabled", false);
          } else if (data.res === "success") {
            jQuery("#showClientVerifyFailedMessage").css("color", "green");
            jQuery("#showMerchantFormData").hide();
            jQuery("#showClientVerifyFailedMessage").html(
              "You have Successfully configured the Gateway Configuration!!!! lets begin testing"
            );
          }
        },
      });
    }
  });
});
