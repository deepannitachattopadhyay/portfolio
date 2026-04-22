// JavaScript contact form Document
$(document).ready(function () {
  // Use the form id `contact-form` (matches index.html)
  $("form#contact-form").submit(function (e) {
    e.preventDefault();
    $("form#contact-form .error").remove();
    var hasError = false;

    // Basic client-side required field check for inputs with class 'requiredField'
    $(".requiredField").each(function () {
      if (jQuery.trim($(this).val()) === "") {
        var labelText = $(this).prev("label").text() || "field";
        $(this)
          .parent()
          .append(
            '<span class="error">You forgot to enter your ' +
              labelText +
              "</span>",
          );
        $(this).addClass("inputError");
        hasError = true;
      } else if ($(this).hasClass("email")) {
        var emailReg = /^([\w-.]+@([\w-]+\.)+[\w-]{2,4})?$/;
        if (!emailReg.test(jQuery.trim($(this).val()))) {
          var labelText = $(this).prev("label").text() || "email";
          $(this)
            .parent()
            .append(
              '<span class="error">You entered an invalid ' +
                labelText +
                "</span>",
            );
          $(this).addClass("inputError");
          hasError = true;
        }
      }
    });

    if (!hasError) {
      // Disable submit button to prevent double submits
      var $submitBtn = $('form#contact-form button[type="submit"]');
      $submitBtn.prop("disabled", true);

      $("#loader").show();
      $.ajax({
        url: "contact.php",
        type: "POST",
        data: new FormData(this),
        contentType: false,
        cache: false,
        processData: false,
        success: function (data) {
          $("form#contact-form").slideUp("fast", function () {
            $(this).before(
              '<div class="success">Thank you. Your Email was sent successfully.</div>',
            );
            $("#loader").hide();
          });
        },
        error: function (xhr, status, err) {
          $("#success_fail_info").html(
            '<div class="error">There was an error sending your message. Please try again later.</div>',
          );
          $("#loader").hide();
          $submitBtn.prop("disabled", false);
        },
      });

      return false;
    }
  });
});
