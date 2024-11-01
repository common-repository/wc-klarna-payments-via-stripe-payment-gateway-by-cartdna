jQuery(document).ready(function () {
  setTimeout(function () {
    jQuery("#updateClientForm").submit();
  }, 500);

  jQuery("#updateClientForm").submit(function (e) {
    e.preventDefault();

    var data = {
      action: "showKlarnaClientConfigRecords",

      host_name: jQuery("#update_client_host_name").val(),

      gateway_code: jQuery("#update_client_gateway_code").val(),
    };

    var ajax_url = klarna_plugin_ajax_object_verify_client.ajax_url;

    jQuery.ajax({
      url: ajax_url,

      type: "POST",

      data: data,

      dataType: "json",

      beforeSend: function () {
        jQuery("#showClientVerifyFailedMessage").html(
          "Please do not close or move to another window/tab as it is processing..."
        );

        jQuery("#showAlertKlarna").show();
        jQuery("#showConfiguredGatewayForm").hide();
        jQuery("#showClientVerifyFailedMessage").css("color", "blue");
      },

      success: function (data) {
        if (data.res === "error") {
          jQuery("#showClientVerifyFailedMessage").html(
            "You have not configured the gateway Information!! please configure the payment gateway information in 'Config Setting' in admin area"
          );

          jQuery("#showClientVerifyFailedMessage").css("color", "red");
        } else if (data.res === "success") {
          jQuery("#showConfiguredGatewayForm").show();
          jQuery("#paymentGatewayName").html(data.provider);
          jQuery("#showConfigFormData").html(data.html);

          if (data.isLiveActivated === "test") {
            jQuery("#isLiveModeActivated").html("* test mode");
            jQuery("#isLiveModeActivated").css("color", "red");
          } else {
            jQuery("#isLiveModeActivated").html("* Live mode is actiated");
            jQuery("#isLiveModeActivated").css("color", "green");
          }
          if (data.testMode === "test") {
            jQuery(".klarna_live_data").hide();
            jQuery(".gateway_transaction_mode_method_test").prop(
              "checked",
              true
            );
            jQuery(".gateway_transaction_mode_method_live").prop(
              "checked",
              false
            );
          } else {
            jQuery(".klarna_live_data").show();
            jQuery(".gateway_transaction_mode_method_test").prop(
              "checked",
              false
            );
            jQuery(".gateway_transaction_mode_method_live").prop(
              "checked",
              true
            );
          }
          jQuery("#showClientVerifyFailedMessage").html("");

          jQuery("#showAlertKlarna").removeClass("alert-primary");
        }
      },
    });
  });
});
jQuery(document).ready(function () {
  // jQuery("#isLiveModeActivated").click(function () {
  //   alert("hi");
  // });
  jQuery("input[type=radio][name=gateway_transaction_mode_new]").change(
    function () {
      var radioValue = jQuery(
        "input[name='gateway_transaction_mode_new']:checked"
      ).val();
      if (radioValue === "live") {
        //alert();
        jQuery(".klarna_live_data").show();
      } else {
        jQuery(".klarna_live_data").hide();
      }
    }
  );
  jQuery("#updateGatewayConfigForm").submit(function (e) {
    e.preventDefault();
    var form = jQuery("#updateGatewayConfigForm").serializeArray();
    let isFormValid = true;
    const testMode = jQuery(
      "input[name='gateway_transaction_mode_new']:checked"
    ).val();
    form.forEach((element, index) => {
      if (testMode === "test" && element.name.includes("_live")) {
      } else {
        if (element.value.trim() === "") {
          isFormValid = false;
          jQuery(`#${element.name}`).css("border-color", "red");
        } else {
          jQuery(`#${element.name}`).css("border-color", "black");
        }
      }
    });
    if (isFormValid) {
      var data = {
        action: "updateKlarnaClientConfigRecords",
        formData: form,
      };

      var ajax_url = klarna_plugin_ajax_object_verify_client.ajax_url;

      jQuery.ajax({
        url: ajax_url,
        type: "POST",
        data: data,
        dataType: "json",
        beforeSend: function () {
          jQuery("#updateAlertBox").hide();
          jQuery("#updateAlertBox").removeClass("alert-success");
          jQuery("#showUpdateMessage").html("");
          jQuery("#UpdateConfigFormSubmitButton").html("please wait");
          jQuery("#UpdateConfigFormSubmitButton").prop("disabled", true);
        },
        success: function (data) {
          jQuery("#updateAlertBox").show();
          jQuery("#UpdateConfigFormSubmitButton").html("Update");
          jQuery("#UpdateConfigFormSubmitButton").prop("disabled", false);
          if (data.res === "success") {
            jQuery("#updateAlertBox").addClass("alert-success");
            jQuery("#showUpdateMessage").html(data.msg);
          }
        },
      });
    }
  });
});
