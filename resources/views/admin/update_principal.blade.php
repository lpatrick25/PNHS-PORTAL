@extends('layout.master')
@section('title')
    | Principal Update
@endsection
@section('active-principal-list')
    active
@endsection
@section('app-title')
    Principals
@endsection
@section('active-principal-open')
    menu-is-opening menu-open
@endsection
@section('content')
    <form id="updateForm" class="card-content" enctype="multipart/form-data">
        <div class="card-body">
            <div class="row">
                <div class="col-lg-12" id="show-msg"></div>
                <div class="col-lg-3 my-body mg-b-20 mg-lr-20" style="display: block;">
                    <p class="text-bold text-center">
                        <span id="user-position" class="text-danger" style="text-decoration: underline;">PRINCIPAL</span>
                    </p>
                    <img src="{{ $principal->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar4.png') }}"
                        alt="Avatar Image" style="width: 100%; height: 350px; object-fit: cover;" id="user_picture">
                    <hr>
                    <div class="form-group">
                        <label>Principal ID:</label>
                        <input type="text" class="form-control" id="principal_id" name="principal_id"
                            value="{{ $principal->id }}" readonly>
                    </div>
                    <hr>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary btn-lg btn-block" id="pictureBtn"><i
                                class="fa fa-image"></i> PICTURE</button>
                    </div>
                </div>
                <div class="col-lg-9 my-body mg-lr-20">
                    <div class="row mg-t-10">
                        <div class="col-lg-12 col-sm-12">
                            <h3 class="text-center">Personal Information</h3>
                            <hr>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="first_name">First Name: <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="{{ old('first_name', $principal->first_name) }}" placeholder="First Name" required>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="middle_name">Middle Name: <span style="color:red;"></span></label>
                            <input type="text" class="form-control" id="middle_name" name="middle_name"
                                value="{{ old('middle_name', $principal->middle_name) }}" placeholder="Middle Name">
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="last_name">Last Name: <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="{{ old('last_name', $principal->last_name) }}" placeholder="Last Name" required>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="extension_name">Extension Name: <span style="color:red;"></span></label>
                            <input type="text" class="form-control" id="extension_name" name="extension_name"
                                value="{{ old('extension_name', $principal->extension_name) }}" placeholder="Extension Name">
                        </div>
                        <div class="col-lg-12">
                            <p class="text-left text-danger"><strong>ADDRESS</strong></p>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="province_code">Province: <span style="color:red;">*</span></label>
                            <select class="form-control" id="province_code" name="province_code" required>
                                <option value="REQUIRED" disabled>-- Select Province --</option>
                                @foreach ($provinces as $province)
                                    <option value="{{ $province->province_code }}"
                                        {{ old('province_code', $principal->province_code) == $province->province_code ? 'selected' : '' }}>
                                        {{ $province->province_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="municipality_code">Municipality: <span style="color:red;">*</span></label>
                            <select class="form-control" id="municipality_code" name="municipality_code" required>
                                <option value="REQUIRED" disabled>-- Select Municipality --</option>
                                @foreach ($municipalities as $municipality)
                                    <option value="{{ $municipality->municipality_code }}"
                                        {{ old('municipality_code', $principal->municipality_code) == $municipality->municipality_code ? 'selected' : '' }}>
                                        {{ $municipality->municipality_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="barangay_code">Barangay: <span style="color:red;">*</span></label>
                            <select class="form-control" id="barangay_code" name="barangay_code" required>
                                <option value="REQUIRED" disabled>-- Select Barangay --</option>
                                @foreach ($barangays as $barangay)
                                    <option value="{{ $barangay->barangay_code }}"
                                        {{ old('barangay_code', $principal->barangay_code) == $barangay->barangay_code ? 'selected' : '' }}>
                                        {{ $barangay->barangay_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="zip_code">Zip Code: <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="zip_code" name="zip_code"
                                value="{{ old('zip_code', $principal->zip_code) }}" placeholder="Zip Code" required
                                readonly>
                        </div>
                        <div class="col-lg-12">
                            <p class="text-left text-danger"><strong>PRINCIPAL INFORMATION</strong></p>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="religion">Religion: <span style="color:red;">*</span></label>
                            <select id="religion" name="religion" class="form-control">
                                <option value="REQUIRED" disabled>-- Select Religion --</option>
                                @foreach (['Roman Catholic', 'Seventh-Day Adventist', 'Iglesia ni Cristo', 'Jehovah Witnesses', 'Pentecostal', 'Church of Christ', 'Christian', 'Baptist', 'Protestant', 'Born Again', 'Muslim'] as $religion)
                                    <option value="{{ $religion }}"
                                        {{ old('religion', $principal->religion) == $religion ? 'selected' : '' }}>
                                        {{ $religion }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="birthday">Birthday: <span style="color:red;">*</span></label>
                            <input type="date" class="form-control" id="birthday" name="birthday"
                                value="{{ old('birthday', $principal->birthday) }}" required>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="sex">Sex: <span style="color:red;">*</span></label>
                            <select type="text" class="form-control" id="sex" name="sex">
                                <option value="REQUIRED" disabled>-- Select Sex --</option>
                                <option value="Male" {{ old('sex', $principal->sex) == 'Male' ? 'selected' : '' }}>Male
                                </option>
                                <option value="Female" {{ old('sex', $principal->sex) == 'Female' ? 'selected' : '' }}>
                                    Female</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-3">
                            <label for="civil_status">Civil Status: <span style="color:red;">*</span></label>
                            <select class="form-control" id="civil_status" name="civil_status">
                                <option value="REQUIRED" disabled>-- Select Civil Status --</option>
                                <option value="Single"
                                    {{ old('civil_status', $principal->civil_status) == 'Single' ? 'selected' : '' }}>Single
                                </option>
                                <option value="Married"
                                    {{ old('civil_status', $principal->civil_status) == 'Married' ? 'selected' : '' }}>
                                    Married</option>
                                <option value="Widowed"
                                    {{ old('civil_status', $principal->civil_status) == 'Widowed' ? 'selected' : '' }}>
                                    Widowed</option>
                                <option value="Divorced"
                                    {{ old('civil_status', $principal->civil_status) == 'Divorced' ? 'selected' : '' }}>
                                    Divorced</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12 col-lg-6">
                            <label for="email">Email Address: <span style="color:red;">*</span></label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $principal->email) }}" placeholder="Email Address" required>
                        </div>
                        <div class="form-group col-md-12 col-lg-6">
                            <label for="contact">Contact Number: <span style="color:red;">*</span></label>
                            <input type="text" class="form-control" id="contact" name="contact"
                                value="{{ old('contact', $principal->contact) }}" data-mask="(+63) 999-999-9999"
                                placeholder="(+63)" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary btn-lg">SAVE</button>
            <a href="{{ route('admin.principals') }}" type="button" class="btn btn-danger btn-lg">CANCEL</a>
        </div>
    </form>
    <div class="modal fade" id="updatePicture">
        <div class="modal-dialog">
            <form id="updatePictureForm" class="modal-content" enctype="multipart/form-data">
                <div class="modal-header">
                    <h3 class="modal-title">Update Picture</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="attachment">Upload Picture: <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="attachment" name="attachment" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-lg btn-primary">SAVE</button>
                    <button type="button" class="btn btn-lg btn-danger" data-dismiss="modal">CANCEL</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        let selectedMunicipality, selectedBrgy;
        $(document).ready(function() {
            $('#updateForm').trigger('reset');
            $('#updateForm').find('input').attr('disabled', true);
            $('#updateForm').find('select').attr('disabled', true);
            $('#updateForm').find('button').attr('disabled', true);
            $('select').trigger('chosen:updated');

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
                    $('#province_code').val('{{ $principal->province_code }}').trigger('chosen:updated');
                    $('#province_code').change();
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
                var provinceCode = $(this).val();
                $('#municipality_code').empty().append(
                    '<option value="NONE">-- Select Municipality --</option>');
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
                        $('#municipality_code').val('{{ $principal->municipality_code }}')
                            .trigger('chosen:updated');
                        $('#municipality_code').change();
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
                var municipalityCode = $(this).val();
                $('#barangay_code').empty().append('<option value="NONE">-- Select Barangay --</option>');
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
                        $('#barangay_code').val('{{ $principal->barangay_code }}').trigger(
                            'chosen:updated');
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

            $('#first_name').val('{{ $principal->first_name }}');
            $('#middle_name').val('{{ $principal->middle_name ?? '' }}');
            $('#last_name').val('{{ $principal->last_name }}');
            $('#extension_name').val('{{ $principal->extension_name ?? '' }}');
            $('#religion').val('{{ $principal->religion }}');
            $('#birthday').val('{{ $principal->birthday->format('Y-m-d') }}');
            $('#sex').val('{{ $principal->sex }}');
            $('#civil_status').val('{{ $principal->civil_status }}');
            $('#email').val('{{ $principal->email }}');
            $('#contact').val('{{ $principal->contact }}');
            $('#user_picture').attr('src',
                '{{ $principal->getFirstMediaUrl('avatar', 'thumb') ?: asset('dist/img/avatar4.png') }}');

            selectedBrgy = '{{ $principal->barangay_code }}';
            selectedMunicipality = '{{ $principal->municipality_code }}';

            $('#updateForm').find('input').removeAttr('disabled');
            $('#updateForm').find('select').removeAttr('disabled');
            $('#updateForm').find('button').removeAttr('disabled');
            $('select').trigger('chosen:updated');

            $('#pictureBtn').click(function() {
                $('#updatePicture').modal({
                    backdrop: 'static',
                    keyboard: false
                }).modal('show');
            });

            $('#updatePictureForm').submit(function(event) {
                event.preventDefault();
                $('#show-msg').html('');
                const requiredFields = $(this).find('input[required]');
                let isEmptyField = false;
                requiredFields.each(function() {
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
                    $('#updatePicture').modal('hide');
                    $('#updatePictureForm button[type=submit]').attr('disabled', true);
                    let formData = new FormData(this);
                    Swal.fire({
                        title: 'Are you sure?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'SAVE',
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
                                url: `/principals/updateImage/{{ $principal->id }}`,
                                data: formData,
                                processData: false,
                                contentType: false,
                                dataType: 'JSON',
                                success: function(response) {
                                    if (response.valid) {
                                        $('#show-msg').html(
                                            `<div class="alert alert-success">${response.msg}</div>`
                                        );
                                        setTimeout(() => $('#show-msg').html(''), 5000);
                                        $('#user_picture').attr('src', response.image);
                                    } else {
                                        $('#show-msg').html(
                                            `<div class="alert alert-danger">${response.msg}</div>`
                                        );
                                        setTimeout(() => $('#show-msg').html(''), 5000);
                                    }
                                },
                                error: function(jqXHR) {
                                    $('#show-msg').html(
                                        '<div class="alert alert-danger">Something went wrong! Please try again later.</div>'
                                    );
                                }
                            });
                        }
                        $('#updatePictureForm button[type=submit]').removeAttr('disabled');
                    });
                }
            });

            $('#updateForm').submit(function(event) {
                event.preventDefault();
                $('#show-msg').html('');
                const requiredFields = $(this).find('input[required], select[required]');
                let isEmptyField = false;
                requiredFields.each(function() {
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
                    $('#updateForm button[type=submit]').attr('disabled', true);
                    let formData = $(this).serialize();
                    Swal.fire({
                        title: 'Are you sure?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'UPDATE',
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
                                type: 'PUT',
                                url: `/principals/{{ $principal->id }}`,
                                data: formData,
                                dataType: 'JSON',
                                cache: false,
                                success: function(response) {
                                    if (response.valid) {
                                        $('html, body').animate({
                                            scrollTop: 0
                                        }, 800);
                                        $('#show-msg').html(
                                            '<div class="alert alert-success">' +
                                            response.msg + '</div>');
                                        setTimeout(() => $('#show-msg').html(''), 5000);
                                    } else {
                                        $('#show-msg').html(
                                            '<div class="alert alert-danger">' +
                                            response.msg + '</div>');
                                        setTimeout(() => $('#show-msg').html(''), 5000);
                                    }
                                },
                                error: function(jqXHR) {
                                    $('#show-msg').html(
                                        '<div class="alert alert-danger">Something went wrong! Please try again later.</div>'
                                    );
                                }
                            });
                        }
                        $('#updateForm button[type=submit]').removeAttr('disabled');
                    });
                }
            });
        });
    </script>
@endsection
