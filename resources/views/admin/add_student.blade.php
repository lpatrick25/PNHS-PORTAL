@extends('layout.master')
@section('title')
    | Add Student
@endsection
@section('active-student-list')
    active
@endsection
@section('app-title')
    Add Student
@endsection
@section('content')
    <form id="addForm" class="row" role="form" enctype="multipart/form-data">
        <div id="show-msg"></div>
        <div class="col-lg-12">
            <p class="text-left text-danger"><strong>STUDENT PROFILE</strong></p>
        </div>
        <div class="col-lg-12">
            @include('form.student_profile')
        </div>
        <div class="col-lg-12">
            <p class="text-left text-danger"><strong>Family/Guardian Background</strong></p>
        </div>
        <div class="col-lg-12">
            @include('form.family_guard')
        </div>
        <div class="col-lg-12 text-right">
            <button type="submit" class="btn btn-lg btn-primary"><i class="fa fa-save"></i> Save</button>
            <a href="{{ route('admin.students') }}" type="button" class="btn btn-danger btn-lg">CANCEL</a>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript">
        let selectedMunicipality, selectedBrgy;

        function add_another() {
            Swal.fire({
                title: 'Do you want to another student?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'PROCEED',
                cancelButtonText: 'CANCEL',
                reverseButtons: true,
                allowOutsideClick: false,
                showClass: {
                    popup: 'animated fadeInDown'
                },
                hideClass: {
                    popup: 'animated fadeOutUp'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#addForm').trigger('reset');
                } else {
                    window.location.href = '{{ route('admin.students') }}';
                }
            });
        }

        $(document).ready(function() {

            $('#addForm').trigger('reset');

            // Helper function to handle zip code update asynchronously
            function updateZipCode(municipalityCode, zipField) {
                $.ajax({
                    method: 'GET',
                    url: `/address/getZipCode/${municipalityCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        $(zipField).val(data.zip_code);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.'
                        });
                    }
                });
            }

            $.ajax({
                method: 'GET',
                url: '/address/getProvinces/8',
                dataType: 'JSON',
                cache: false,
                success: function(data) {
                    for (var i = 0; i < data.length; i++) {
                        $('#province_code').append('<option value="' + data[i].province_code +
                            '">' + data[i].province_name + '</option>');
                        $('#present_province_code').append('<option value="' + data[i]
                            .province_code +
                            '">' + data[i].province_name + '</option>');
                    }
                    $('#province_code').trigger('chosen:updated');
                    $('#present_province_code').trigger('chosen:updated');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again later.'
                    });
                }
            })

            $('#province_code').change(function() {
                var provinceCode = $('#province_code').val();

                $('#municipality_code').empty();
                $('#municipality_code').append(
                    '<option selected="true" value="NONE">-- Select Municipality --</option>');

                $.ajax({
                    method: 'GET',
                    url: `/address/getMunicipalities/${provinceCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            $('#municipality_code').append('<option value="' + data[i]
                                .municipality_code + '">' + data[i].municipality_name +
                                '</option>');
                        }
                        $('#municipality_code').trigger('chosen:updated');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.'
                        });
                    }
                });
            });

            $('#municipality_code').change(function() {
                var municipalityCode = $('#municipality_code').val();

                $('#barangay_code').empty();
                $('#barangay_code').append(
                    '<option selected="true" value="NONE">-- Select Barangay --</option>');

                $.ajax({
                    method: 'GET',
                    url: `/address/getBrgys/${municipalityCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            $('#barangay_code').append('<option value="' + data[i]
                                .barangay_code +
                                '">' + data[i].barangay_name + '</option>');
                        }
                        $('#barangay_code').trigger('chosen:updated');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.'
                        });
                    }
                });

                // Update zip code using asynchronous handling
                updateZipCode(municipalityCode, '#zip_code');
            });

            $('#present_province_code').change(function() {
                var provinceCode = $('#present_province_code').val();

                $('#present_municipality_code').empty();
                $('#present_municipality_code').append(
                    '<option selected="true" value="NONE">-- Select Municipality --</option>'
                );

                $.ajax({
                    method: 'GET',
                    url: `/address/getMunicipalities/${provinceCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            if (String(selectedMunicipality) === String(data[i]
                                    .municipality_code)) {
                                $('#present_municipality_code').append(
                                    `<option value="${data[i].municipality_code}" selected>${data[i].municipality_name}</option>`
                                );
                            } else {
                                $('#present_municipality_code').append(
                                    `<option value="${data[i].municipality_code}">${data[i].municipality_name}</option>`
                                );
                            }
                        }

                        $('#present_municipality_code').trigger('chosen:updated');
                        $('#present_municipality_code').change();
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.',
                        });
                    },
                });
            });

            $('#present_municipality_code').change(function() {
                var municipalityCode = $('#present_municipality_code').val();

                $('#present_barangay_code').empty();
                $('#present_barangay_code').append(
                    '<option selected="true" value="NONE">-- Select Barangay --</option>');

                $.ajax({
                    method: 'GET',
                    url: `/address/getBrgys/${municipalityCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            if (String(selectedBrgy) === String(data[i].barangay_code)) {
                                $('#present_barangay_code').append(
                                    `<option value="${data[i].barangay_code}" selected>${data[i].barangay_name}</option>`
                                );
                            } else {
                                $('#present_barangay_code').append(
                                    `<option value="${data[i].barangay_code}">${data[i].barangay_name}</option>`
                                );
                            }
                        }
                        $('#present_barangay_code').trigger('chosen:updated');
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.'
                        });
                    }
                });
            });

            $('#user_permanent_address').click(function() {
                if ($(this).prop('checked')) {
                    // Get values from permanent address fields
                    var brgyCode = $('#barangay_code').val();
                    var provinceCode = $('#province_code').val();
                    var municipalityCode = $('#municipality_code').val();

                    if (provinceCode && municipalityCode && brgyCode) {
                        // Set selected values
                        selectedBrgy = brgyCode;
                        selectedMunicipality = municipalityCode;

                        // Update zip code asynchronously
                        updateZipCode(municipalityCode, '#present_zip_code');

                        // Make present address fields readonly-like
                        $('#present_province_code').val(provinceCode).trigger('chosen:updated');
                        $('#present_province_code').attr('data-readonly', 'true');
                        $('#present_province_code')
                            .change(); // Trigger change to update municipalities and barangays

                        $('#present_municipality_code').attr('data-readonly', 'true');
                        $('#present_barangay_code').attr('data-readonly', 'true');

                    } else {
                        // If any required field is missing, uncheck the checkbox and alert the user
                        $(this).prop('checked', false);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Missing Address Information',
                            text: 'Please fill in all permanent address fields before copying to the present address.',
                        });
                    }
                } else {
                    // Clear present address fields when checkbox is unchecked
                    $('#present_province_code').val('').trigger('chosen:updated');
                    $('#present_province_code').removeAttr('data-readonly');

                    $('#present_municipality_code').empty().append(
                        '<option value="NONE">-- Select Municipality --</option>'
                    ).trigger('chosen:updated').removeAttr('data-readonly');

                    $('#present_barangay_code').empty().append(
                        '<option value="NONE">-- Select Barangay --</option>'
                    ).trigger('chosen:updated').removeAttr('data-readonly');

                    $('#present_zip_code').val('');
                }
            });

            $('#addForm').validate({
                rules: {
                    student_lrn: {
                        required: true,
                        minlength: 12,
                        maxlength: 12,
                        digits: true
                    },
                    rfid_no: {
                        required: true
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 50
                    },
                    birthday: {
                        required: true,
                        validAge: true
                    },
                    first_name: {
                        required: true,
                        maxlength: 255
                    },
                    last_name: {
                        required: true,
                        maxlength: 255
                    },
                    province_code: {
                        required: true
                    },
                    municipality_code: {
                        required: true
                    },
                    brgy_code: {
                        required: true
                    },
                    zip_code: {
                        required: true,
                        digits: true,
                        maxlength: 5
                    },
                    religion: {
                        required: true,
                        maxlength: 50
                    },
                    sex: {
                        required: true,
                        maxlength: 6
                    },
                    disability: {
                        maxlength: 50
                    },
                    parent_contact: {
                        required: true,
                        maxlength: 20
                    },
                    contact: {
                        required: true,
                        maxlength: 20
                    },
                    mother_first_name: {
                        required: true,
                        maxlength: 255
                    },
                    mother_last_name: {
                        required: true,
                        maxlength: 255
                    },
                    father_first_name: {
                        required: true,
                        maxlength: 255
                    },
                    father_last_name: {
                        required: true,
                        maxlength: 255
                    },
                },
                messages: {
                    student_lrn: {
                        required: "The Student LRN is required.",
                        minlength: "The Student LRN must be 12 digits.",
                        maxlength: "The Student LRN must be 12 digits.",
                        digits: "The Student LRN must be numeric."
                    },
                    email: {
                        required: "The email address is required.",
                        email: "Please enter a valid email address.",
                        maxlength: "The email address must not exceed 50 characters."
                    },
                    birthday: {
                        required: "The birthday is required."
                    },
                    first_name: {
                        required: "The first name is required.",
                        maxlength: "The first name must not exceed 255 characters."
                    },
                    last_name: {
                        required: "The last name is required.",
                        maxlength: "The last name must not exceed 255 characters."
                    },
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });

            $.validator.addMethod("validAge", function(value, element) {
                if (!value) return false;
                var birthDate = new Date(value);
                var today = new Date();
                var age = today.getFullYear() - birthDate.getFullYear();
                var monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age >= 11 || (today.getFullYear() - birthDate.getFullYear() === 11);
            }, "Student must be at least 11 years old or turning 11 this year.");

            $("#addForm").submit(function(event) {
                event.preventDefault();
                if ($("#addForm").valid()) {
                    $('#show-msg').html('');
                    let isEmptyField = false;
                    $(this).find('input[required], select[required]').each(function() {
                        if ($(this).val() === '' || $(this).val() === 'REQUIRED') {
                            isEmptyField = true;
                        }
                    });

                    if (isEmptyField) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Some fields are empty'
                        });
                    } else {
                        $('html, body').animate({
                            scrollTop: 0
                        }, 800);
                        $('#addForm button[type=submit]').prop('disabled', true);
                        let formData = new FormData(this);
                        // Clean phone numbers to store only digits
                        formData.set('parent_contact', $('#parent_contact').val().replace(/[^\d]/g, ''));
                        formData.set('contact', $('#contact').val().replace(/[^\d]/g, ''));
                        Swal.fire({
                            title: 'Are you sure?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'SUBMIT',
                            cancelButtonText: 'CANCEL',
                            reverseButtons: false,
                            allowOutsideClick: false,
                            showClass: {
                                popup: 'animated fadeInDown'
                            },
                            hideClass: {
                                popup: 'animated fadeOutUp'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    method: 'POST',
                                    url: '{{ route('students.store') }}',
                                    data: formData,
                                    processData: false,
                                    contentType: false,
                                    dataType: 'JSON',
                                    cache: false,
                                    success: function(response) {
                                        if (response.valid) {
                                            $('#show-msg').html(
                                                '<div class="alert alert-success">' +
                                                response.msg + '</div>');
                                            setTimeout(function() {
                                                $('#show-msg').html('');
                                            }, 5000);
                                            add_another();
                                        } else {
                                            $('html, body').animate({
                                                scrollTop: 0
                                            }, 800);
                                            $('#show-msg').html(
                                                '<div class="alert alert-danger">' +
                                                response.msg + '</div>');
                                        }
                                    },
                                    error: function(jqXHR) {
                                        $('#show-msg').html(
                                            '<div class="alert alert-danger">Something went wrong! Please try again later.</div>'
                                            );
                                    }
                                });
                            }
                            $('#addForm button[type=submit]').prop('disabled', false);
                        });
                    }
                }
            });
        });
    </script>
@endsection
