@extends('layout.master')
@section('title')
    | Settings
@endsection
@section('active-settings')
    active
@endsection
@section('app-title')
    Settings
@endsection
@section('content')
    <div id="show-msg"></div>
    <div class="row">
        <div class="col-lg-4">
            <div class="card card-outline card-success">
                <div class="card-content">
                    <div class="card-header">
                        <h3 class="card-title">School Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="school_id">School ID: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="school_id" name="school_id" value="303414"
                                required readonly>
                        </div>
                        <div class="form-group">
                            <label for="school_name">School Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="school_name" name="school_name"
                                value="PALALE NATIONAL HIGH SCHOOL" required readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div id="toolbar">
                <button type="button" class="btn btn-primary btn-md" id="add-btn"><i class="fa fa-plus"></i> Add
                    Settings</button>
            </div>
            <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true"
                data-show-columns="false" data-cookie="false" data-cookie-id-table="table" data-search="true"
                data-click-to-select="false" data-show-copy-rows="false" data-page-number="1" data-total-rows="11"
                data-show-toggle="false" data-show-export="false" data-filter-control="true"
                data-show-search-clear-button="false" data-key-events="false" data-mobile-responsive="true"
                data-check-on-init="true" data-show-print="false" data-sticky-header="true" data-url="/school-years"
                data-toolbar="#toolbar">
                <thead>
                    <tr>
                        <th data-field="count">#</th>
                        <th data-field="school_year">School Year</th>
                        <th data-field="start_date">Start Date</th>
                        <th data-field="end_date">End Date</th>
                        <th data-field="current">Current</th>
                        <th data-field="action">Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    <div id="addModal" class="modal fade">
        <div class="modal-dialog modal-sm">
            <form id="addForm" class="modal-content">
                <input type="hidden" class="form-control" id="current" name="current" value="0">
                <div class="modal-header">
                    <h3 class="modal-title">Add School Year</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="school_year">School Year</label>
                        <input type="text" class="form-control" id="school_year" name="school_year" data-mask="2099-2099"
                            required>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-md"><i class="fa fa-plus"></i> Add</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger btn-md"><i class="fa fa-times"></i>
                        Close</button>
                </div>
            </form>
        </div>
    </div>
    <div id="updateModal" class="modal fade">
        <div class="modal-dialog modal-sm">
            <form id="updateForm" class="modal-content">
                <input type="hidden" class="form-control" id="current" name="current" value="0">
                <div class="modal-header">
                    <h3 class="modal-title">Update School Year</h3>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="school_year">School Year</label>
                        <input type="text" class="form-control" id="school_year" name="school_year"
                            data-mask="2099-2099" required>
                    </div>
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-md"><i class="fa fa-edit"></i> Update</button>
                    <button type="button" data-dismiss="modal" class="btn btn-danger btn-md"><i
                            class="fa fa-times"></i> Close</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        let schoolYearID;

        function refresh_table() {
            $('#table').bootstrapTable('refresh');
        }

        function view(id) {
            $.ajax({
                method: 'GET',
                url: `/school-years/${id}`,
                dataType: 'JSON',
                cache: false,
                success: function(response) {
                    if (response) {
                        schoolYearID = response.id;
                        $('#updateForm').find('input[id=school_year]').val(response.school_year);
                        $('#updateForm').find('input[id=start_date]').val(response.start_date);
                        $('#updateForm').find('input[id=end_date]').val(response.end_date);
                        $('#updateForm').find('input[id=current]').val(response.current ? 1 : 0);
                        $('#updateModal').modal({
                            backdrop: 'static',
                            keyboard: false,
                            show: true
                        });
                    }
                },
                error: function(jqXHR) {
                    let errorMsg = jqXHR.responseJSON?.error || 'Something went wrong! Please try again later.';
                    $('#show-msg').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                    setTimeout(() => $('#show-msg').html(''), 5000);
                }
            });
        }

        function setCurrent(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will set the selected school year as the current one.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Proceed'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'PATCH',
                        url: `/school-years/${id}/set-current`,
                        dataType: 'JSON',
                        cache: false,
                        success: function(response) {
                            if (response.valid) {
                                refresh_table();
                                $('#show-msg').html(
                                    `<div class="alert alert-success">${response.msg}</div>`);
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            } else {
                                $('#show-msg').html(
                                    `<div class="alert alert-danger">${response.msg}</div>`);
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            }
                        },
                        error: function(jqXHR) {
                            let errorMsg = jqXHR.responseJSON?.msg ||
                                'Failed to set current school year.';
                            $('#show-msg').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                            setTimeout(() => $('#show-msg').html(''), 5000);
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            var $table = $('#table');
            $table.bootstrapTable({
                exportDataType: $(this).val(),
                printPageBuilder: function(table) {
                    return myCustomPrint(table, "List of School Year");
                },
            });

            $('#add-btn').click(function(event) {
                event.preventDefault();
                $('#addModal').modal({
                    backdrop: 'static',
                    keyboard: false,
                    show: true
                });
            });

            $('#addForm').validate({
                rules: {
                    school_year: {
                        required: true,
                        regex: /^\d{4}-\d{4}$/
                    },
                    start_date: {
                        required: true
                    },
                    end_date: {
                        required: true
                    },
                },
                messages: {
                    school_year: {
                        required: "Please enter school year",
                        regex: "School year must be in the format YYYY-YYYY (e.g., 2024-2025)"
                    },
                    start_date: "Please enter start date",
                    end_date: "Please enter end date",
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".form-group").append(error);
                },
                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },
                submitHandler: function(form) {
                    Swal.fire({
                        title: 'Are you sure?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Proceed'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                method: 'POST',
                                url: '/school-years',
                                data: $(form).serialize(),
                                dataType: 'JSON',
                                cache: false,
                                success: function(response) {
                                    if (response.valid) {
                                        $(form)[0].reset();
                                        $('#addModal').modal('hide');
                                        refresh_table();
                                        $('#show-msg').html(
                                            `<div class="alert alert-success">${response.msg}</div>`
                                            );
                                        setTimeout(() => $('#show-msg').html(''),
                                            5000);
                                    } else {
                                        $('#show-msg').html(
                                            `<div class="alert alert-danger">${response.msg}</div>`
                                            );
                                        setTimeout(() => $('#show-msg').html(''),
                                            5000);
                                    }
                                },
                                error: function(jqXHR) {
                                    let errorMsg = jqXHR.responseJSON?.msg ||
                                        'An error occurred. Please try again.';
                                    $('#show-msg').html(
                                        `<div class="alert alert-danger">${errorMsg}</div>`
                                        );
                                    setTimeout(() => $('#show-msg').html(''), 5000);
                                }
                            });
                        }
                    });
                }
            });

            $('#updateForm').validate({
                rules: {
                    school_year: {
                        required: true,
                        regex: /^\d{4}-\d{4}$/
                    },
                    start_date: {
                        required: true
                    },
                    end_date: {
                        required: true
                    },
                },
                messages: {
                    school_year: {
                        required: "Please enter school year",
                        regex: "School year must be in the format YYYY-YYYY (e.g., 2024-2025)"
                    },
                    start_date: "Please enter start date",
                    end_date: "Please enter end date",
                },
                errorElement: "span",
                errorPlacement: function(error, element) {
                    error.addClass("invalid-feedback");
                    element.closest(".form-group").append(error);
                },
                highlight: function(element) {
                    $(element).addClass("is-invalid");
                },
                unhighlight: function(element) {
                    $(element).removeClass("is-invalid");
                },
                submitHandler: function(form) {
                    Swal.fire({
                        title: 'Are you sure?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Proceed'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                method: 'PUT',
                                url: `/school-years/${schoolYearID}`,
                                data: $(form).serialize(),
                                dataType: 'JSON',
                                cache: false,
                                success: function(response) {
                                    if (response.valid) {
                                        $(form)[0].reset();
                                        $('#updateModal').modal('hide');
                                        refresh_table();
                                        $('#show-msg').html(
                                            `<div class="alert alert-success">${response.msg}</div>`
                                            );
                                        setTimeout(() => $('#show-msg').html(''),
                                            5000);
                                    } else {
                                        $('#show-msg').html(
                                            `<div class="alert alert-danger">${response.msg}</div>`
                                            );
                                        setTimeout(() => $('#show-msg').html(''),
                                            5000);
                                    }
                                },
                                error: function(jqXHR) {
                                    let errorMsg = jqXHR.responseJSON?.msg ||
                                        'An error occurred. Please try again.';
                                    $('#show-msg').html(
                                        `<div class="alert alert-danger">${errorMsg}</div>`
                                        );
                                    setTimeout(() => $('#show-msg').html(''), 5000);
                                }
                            });
                        }
                    });
                }
            });
        });
    </script>
@endsection
