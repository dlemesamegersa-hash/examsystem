/**
 * Custom JavaScript & Client-side Validation
 * Student Registration System
 */

$(document).ready(function () {

    // Auto-dismiss standard alerts after 6 seconds
    setTimeout(function () {
        $(".alert.alert-dismissible").not(".alert-permanent").slideUp(400, function () {
            $(this).remove();
        });
    }, 6000);

    // Dynamic Image Preview on Photo File Input
    $('#photo, #student_photo').on('change', function () {
        var input = this;
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                $('#photoPreview').attr('src', e.target.result).show();
                $('#photoPlaceholder').hide();
            };
            reader.readAsDataURL(input.files[0]);
            // Update custom file label
            var fileName = input.files[0].name;
            $(input).next('.custom-file-label').html(fileName);
        }
    });

    // Password Toggle Visibility
    $('.toggle-password-btn').on('click', function () {
        var targetInput = $(this).closest('.input-group').find('input');
        var icon = $(this).find('i');
        if (targetInput.attr('type') === 'password') {
            targetInput.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            targetInput.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password & Confirm Password Match Validation
    $('form.validate-password-match').on('submit', function (e) {
        var pass = $(this).find('input[name="password"]').val();
        var confirm = $(this).find('input[name="confirm_password"]').val();
        
        if (confirm !== undefined && pass !== confirm) {
            e.preventDefault();
            alert('Password and Confirm Password do not match. Please verify.');
            $(this).find('input[name="confirm_password"]').focus();
            return false;
        }
    });

    // Delete Confirmation Modal Handling
    $(document).on('click', '.btn-delete-confirm', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var action = $(this).data('action');
        var message = $(this).data('message') || 'Are you sure you want to permanently delete this record?';
        
        $('#deleteConfirmForm').attr('action', action);
        $('#deleteTargetId').val(id);
        $('#deleteConfirmMessage').text(message);
        $('#deleteConfirmModal').modal('show');
    });

    // General Form Validation Feedback
    $('form.needs-validation').on('submit', function (event) {
        if (this.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

});
