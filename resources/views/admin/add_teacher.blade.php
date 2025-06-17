@extends('layout.master')
@section('title')
    | Add Teacher
@endsection
@section('active-teacher-list')
    active
@endsection
@section('app-title')
    Add Teacher
@endsection
@section('content')
    <form id="addForm" class="row" role="form" enctype="multipart/form-data">
        <div id="show-msg"></div>
        <div class="col-lg-12">
            <p class="text-left text-danger"><strong>TEACHER PROFILE</strong></p>
        </div>
        <div class="col-lg-12">
            @include('form.teacher_profile')
        </div>
        <div class="col-lg-12 text-right">
            <button type="submit" class="btn btn-lg btn-primary"><i class="fa fa-save"></i> Save</button>
            <a href="{{ route('admin.teachers') }}" type="button" class="btn btn-danger btn-lg">CANCEL</a>
        </div>
    </form>
@endsection
@section('scripts')
    <script type="text/javascript">
        let selectedMunicipality, selectedBrgy;

        function add_another() {
            Swal.fire({
                title: 'Do you want to add another teacher?',
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
                    window.location.href = '{{ route('admin.teachers') }}';
                }
            });
        }

        $(document).ready(function() {
            $('#addForm').trigger('reset');

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
                        $('#province_code').append('<option value="' + data[i].province_code + '">' +
                            data[i].province_name + '</option>');
                    }
                    $('#province_code').trigger('chosen:updated');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong! Please try again later.'
                    });
                }
            });

            $('#province_code').change(function() {
                var provinceCode = $('#province_code').val();
                $('#municipality_code').empty().append(
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
                $('#barangay_code').empty().append(
                    '<option selected="true" value="NONE">-- Select Barangay --</option>');
                $.ajax({
                    method: 'GET',
                    url: `/address/getBrgys/${municipalityCode}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(data) {
                        for (var i = 0; i < data.length; i++) {
                            $('#barangay_code').append('<option value="' + data[i]
                                .barangay_code + '">' + data[i].barangay_name + '</option>');
                        }
                        $('#barangay_code').trigger('chosen:updated');
                        updateZipCode(municipalityCode, '#zip_code');
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

            $('#addForm').validate({
                rules: {
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
                    barangay_code: {
                        required: true
                    },
                    zip_code: {
                        required: true,
                        digits: true,
                        maxlength: 10
                    },
                    religion: {
                        required: true,
                        maxlength: 50
                    },
                    birthday: {
                        required: true,
                        validAge: true
                    },
                    sex: {
                        required: true,
                        maxlength: 6
                    },
                    civil_status: {
                        required: true,
                        maxlength: 15
                    },
                    email: {
                        required: true,
                        email: true,
                        maxlength: 50
                    },
                    contact: {
                        required: true,
                        maxlength: 20
                    }
                },
                messages: {
                    first_name: {
                        required: "The first name is required.",
                        maxlength: "The first name must not exceed 255 characters."
                    },
                    last_name: {
                        required: "The last name is required.",
                        maxlength: "The last name must not exceed 255 characters."
                    },
                    email: {
                        required: "The email address is required.",
                        email: "Please enter a valid email address.",
                        maxlength: "The email address must not exceed 50 characters."
                    }
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
                return age >= 21;
            }, "Teacher must be at least 21 years old.");

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
                                    url: '/teachers',
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
