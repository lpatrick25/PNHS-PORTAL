<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Palale National High School Attendance System">
    <meta name="keywords" content="Palale National High School, Attendance Portal, student attendance, palale">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Login Portal - {{ env('APP_NAME') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ asset('dist/img/PNHS_Logo.png') }}">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- icheck bootstrap -->
    <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/adminlte.min.css">
    <style type="text/css">
        body {
            background-color: #e9ecef;
            background-image: url('dist/img/main_bg.jpg');
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            color: #333;
        }

        .login-box {
            width: 450px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .login-logo img {
            width: 100%;
            height: auto;
            border-radius: 10px 10px 0 0;
        }

        .card-outline {
            border-top: 3px solid #0055a4;
            /* Blue from logo */
        }

        .btn-success {
            background-color: #ffcc00;
            /* Yellow from logo */
            border-color: #ffcc00;
            color: #333;
        }

        .btn-success:hover {
            background-color: #e6b800;
            border-color: #e6b800;
        }

        .form-control {
            border-color: #0055a4;
        }

        .input-group-text {
            background-color: #0055a4;
            color: #fff;
        }

        .text-success {
            color: #28a745 !important;
        }

        .modal-content {
            border-radius: 10px;
        }

        .modal-header {
            background-color: #0055a4;
            color: #fff;
        }

        .btn-primary {
            background-color: #28a745;
            /* Green accent */
            border-color: #28a745;
        }

        .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center" style="border: none;">
                <img src="dist/img/header.png" class="login-logo" style="margin: 0px; width: 100%; height: 150px;"
                    id="login-header">
                <p href="index.php" class="h3 text-bold text-primary mt-3">LOGIN PORTAL</p>
            </div>
            <div class="card-body">
                <form id="quickForm" method="post">
                    <div id="error-msg"></div>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="username" id="username"
                            placeholder="Student ID | Employee ID | Instructor ID" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-user"></span>
                            </div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="Password" autocomplete="off">
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-eye" id="show-password"></span>
                            </div>
                            <div class="input-group-text">
                                <span class="fas fa-lock"></span>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-success btn-block">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="updateData" class="modal animated fade">
        <div class="modal-dialog">
            <form id="updateDataForm" class="modal-content">
                <input type="hidden" class="form-control" id="emailTxt" name="emailTxt" required>
                <div class="modal-header">
                    <h3 class="modal-title">Update Password</h3>
                </div>
                <div class="modal-body">
                    <div id="display-msg"></div>
                    <div class="form-group">
                        <label for="password">Password: <span class="text-danger">*</span></label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="cpassword">Confirm Password: <span class="text-danger">*</span></label>
                        <input type="password" id="cpassword" name="cpassword" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer text-right">
                    <button type="submit" class="btn btn-lg btn-primary"><i class="fa fa-save"> SAVE</i></button>
                    <button type="button" data-dismiss="modal" class="btn btn-lg btn-danger"><i class="fa fa-times">
                            CANCEL</i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <!-- Bootstrap 4 -->
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Fileinput -->
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <!-- Validation -->
    <script src="{{ asset('plugins/jquery-validation/jquery.validate.js') }}"></script>
    <script src="{{ asset('plugins/jquery-validation/additional-methods.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="dist/js/adminlte.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#quickForm').trigger('reset');

            $('#show-password').click(function() {
                var inputType = $('#password').attr('type');
                if (inputType === 'password') {
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                    $('#password').attr('type', 'text');
                } else {
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                    $('#password').attr('type', 'password');
                }
            });

            $('#quickForm').submit(function(event) {
                event.preventDefault();
                $('#error-msg').html('');

                var form_data = {
                    username: $('#username').val(),
                    password: $('#password').val(),
                };

                if (!form_data.username || !form_data.password) {
                    $('#error-msg').html(
                        '<p class="alert alert-danger">Please fill in both username and password.</p>');
                    return;
                }

                $('#quickForm button[type=submit]').prop('disabled', true);

                $.ajax({
                    method: 'POST',
                    url: '{{ route('login') }}',
                    data: $('#quickForm').serialize(),
                    dataType: 'JSON',
                    cache: false,
                    success: function(response) {
                        if (response.valid) {
                            window.location.href = response.return_url;
                        } else {
                            $('#error-msg').html('<p class="alert alert-danger">' + response
                                .msg + '</p>');
                        }
                    },
                    error: function() {
                        $('#error-msg').html(
                            '<p class="alert alert-danger">Something went wrong. Please try again later.</p>'
                            );
                    }
                }).always(function() {
                    $('#quickForm button[type=submit]').prop('disabled', false);
                });
            });
        });
    </script>
</body>

</html>
