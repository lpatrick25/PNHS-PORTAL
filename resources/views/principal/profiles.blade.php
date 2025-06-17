@extends('layout.master')
@section('title', '| Student Profile')
@section('active-profile', 'active')
@section('app-title', 'Profile')
@section('content')
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{ $data['avatar'] ?? asset('dist/img/avatar.png') }}" alt="User profile picture"
                            style="width: 150px; height: 150px;">
                    </div>
                    <h3 class="profile-username text-center">{{ $data['full_name'] }}</h3>
                    <p class="text-muted text-center">{{ Str::ucfirst(auth()->user()->role) }}</p>
                    <div class="form-group">
                        <label>Teacher ID:</label>
                        <input type="text" class="form-control" id="teacher_id" name="teacher_id"
                            value="{{ $data['teacher_id'] }}" readonly>
                    </div>
                    <hr>
                    <div class="form-group">
                        <button type="button" class="btn btn-primary btn-lg btn-block" id="pictureBtn"><i
                                class="fa fa-image"></i> PICTURE</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-8 mb-4">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <label>First Name:</label>
                            <p class="form-control">{{ $data['first_name'] }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Middle Name:</label>
                            <p class="form-control">{{ $data['middle_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Last Name:</label>
                            <p class="form-control">{{ $data['last_name'] }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Suffix:</label>
                            <p class="form-control">{{ $data['extension_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Civil Status:</label>
                            <p class="form-control">{{ $data['civil_status'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Religion:</label>
                            <p class="form-control">{{ $data['religion'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Birthday:</label>
                            <p class="form-control">{{ $data['birthday'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Contact:</label>
                            <p class="form-control">{{ $data['contact'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <hr>
                    <h4>Address Information</h4>
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <label>Region:</label>
                            <p class="form-control">{{ $data['region_name'] ?? 'REGION VIII (Eastern Visayas)' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Province:</label>
                            <p class="form-control">{{ $data['province_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Municipality:</label>
                            <p class="form-control">{{ $data['municipality_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Barangay:</label>
                            <p class="form-control">{{ $data['barangay_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Zip Code:</label>
                            <p class="form-control">{{ $data['zip_code'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
        $(document).ready(function() {

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
                                url: `/teachers/updateImage/{{ $data['teacher_id'] }}`,
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
        });
    </script>
