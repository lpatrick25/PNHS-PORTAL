@extends('layout.master')
@section('title')
    | Attendance List
@endsection
@section('active-attendance')
    active
@endsection
@section('app-title')
    Attendance List
@endsection
@section('content')
    <div id="show-msg"></div>
    <div id="subject-list">
        <div id="toolbar">
            <div class="form-inline">
                <label for="school_year" class="mr-2">School Year:</label>
                <select name="school_year" id="school_year" class="form-control chosen-select" style="width: 200px;">
                    @foreach ($schoolYears as $year)
                        <option value="{{ $year->id }}" {{ $year->current ? 'selected' : '' }}>{{ $year->school_year }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true"
            data-show-columns="false" data-cookie="false" data-cookie-id-table="subject_table" data-search="true"
            data-click-to-select="false" data-show-copy-rows="false" data-page-number="1" data-show-toggle="false"
            data-show-export="false" data-filter-control="true" data-show-search-clear-button="false"
            data-key-events="false" data-mobile-responsive="true" data-check-on-init="true" data-show-print="false"
            data-sticky-header="true" data-url="{{ route('teacher.subjects-teacher-load') }}" data-toolbar="#toolbar"
            data-toolbar-align="left">
            <thead>
                <tr>
                    <th data-field="count">#</th>
                    <th data-field="subject_code">Subject Code</th>
                    <th data-field="subject_name">Subject Name</th>
                    <th data-field="grade_level">Grade Level</th>
                    <th data-field="section">Section</th>
                    <th data-field="school_year">School Year</th>
                    <th data-field="action">Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <div id="attendance-list" style="display: none">
        <div id="toolbar">
            <button class="btn btn-danger btn-md" id="back-btn"><i class="fa fa-arrow-left"></i> Back</button>
            <button class="btn btn-primary btn-md" id="add-btn"><i class="fa fa-sync"></i> Generate Attendance</button>
        </div>
        <table id="table1" data-show-refresh="true" data-auto-refresh="true" data-pagination="true"
            data-show-columns="false" data-cookie="false" data-cookie-id-table="attendance_table" data-search="true"
            data-click-to-select="false" data-show-copy-rows="false" data-page-number="1" data-show-toggle="false"
            data-show-export="false" data-filter-control="true" data-show-search-clear-button="false"
            data-key-events="false" data-mobile-responsive="true" data-check-on-init="true" data-show-print="false"
            data-sticky-header="true" data-toolbar="#toolbar">
            <thead>
                <tr>
                    <th data-field="count">#</th>
                    <th data-field="attendance_date">Attendance Date</th>
                    <th data-field="number_of_present">Present</th>
                    <th data-field="number_of_late">Late</th>
                    <th data-field="number_of_absent">Absent</th>
                    <th data-field="action">Action</th>
                </tr>
            </thead>
        </table>
    </div>
    <div id="viewStudent" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Attendance for <span id="modal-subject"></span></h3>
                </div>
                <div class="modal-body">
                    <div id="modal-msg"></div>
                    <div id="toolbar1">
                        <div class="form-group">
                            <label for="rfid_no">RFID No: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="rfid_no" name="rfid_no" required autofocus>
                        </div>
                    </div>
                    <table id="table2" data-show-refresh="false" data-auto-refresh="false" data-pagination="false"
                        data-show-columns="false" data-cookie="false" data-cookie-id-table="student_table"
                        data-search="false" data-click-to-select="false" data-show-copy-rows="false"
                        data-page-number="1" data-show-toggle="false" data-show-export="false"
                        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
                        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false"
                        data-sticky-header="true" data-toolbar="#toolbar1">
                        <thead>
                            <tr>
                                <th data-field="count">#</th>
                                <th data-field="image">Image</th>
                                <th data-field="student_lrn">Student LRN</th>
                                <th data-field="student_name">Student Name</th>
                                <th data-field="attendance_status">Status</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-dismiss="modal" class="btn btn-danger btn-md"><i
                            class="fa fa-times"></i> Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        let subjectLoadId, attendanceDate, subjectName;

        function view(subject_load_id) {
            subjectLoadId = subject_load_id;
            $('#subject-list').hide();
            $('#attendance-list').show();
            $('#table1').bootstrapTable('destroy').bootstrapTable({
                url: '/attendances/by-subject-load/' + subject_load_id,
                formatLoadingMessage: function() {
                    return 'Fetching attendance records, please wait...';
                },
                columns: [{
                    field: 'count',
                    title: '#'
                }, {
                    field: 'attendance_date',
                    title: 'Attendance Date'
                }, {
                    field: 'number_of_present',
                    title: 'Present'
                }, {
                    field: 'number_of_late',
                    title: 'Late'
                }, {
                    field: 'number_of_absent',
                    title: 'Absent'
                }, {
                    field: 'action',
                    title: 'Action'
                }]
            });
        }

        function viewStudents(date) {
            attendanceDate = date;
            $('#modal-subject').text(subjectName);
            $('#table2').bootstrapTable('destroy').bootstrapTable({
                autoRefresh: false,
                url: '/attendances/by-date/' + subjectLoadId + '/' + date,
                formatLoadingMessage: function() {
                    return 'Fetching students, please wait...';
                },
                columns: [{
                    field: 'count',
                    title: '#'
                }, {
                    field: 'image',
                    title: 'Image'
                }, {
                    field: 'student_lrn',
                    title: 'Student LRN'
                }, {
                    field: 'student_name',
                    title: 'Student Name'
                }, {
                    field: 'attendance_status',
                    title: 'Status'
                }]
            });
            $('#viewStudent').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
            $('#rfid_no').focus();
        }

        function updateStatus(attendanceId, studentName) {
            Swal.fire({
                title: `Update Attendance for ${studentName}`,
                html: `
                    <select id="status" class="form-control">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                `,
                showCancelButton: true,
                confirmButtonText: 'Update',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    return document.getElementById('status').value;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'PUT',
                        url: '{{ route('attendances.update', ':id') }}'.replace(':id', attendanceId),
                        data: {
                            status: result.value,
                        },
                        dataType: 'JSON',
                        success: function(response) {
                            if (response.valid) {
                                $('#table2').bootstrapTable('refresh');
                                $('#table1').bootstrapTable('refresh');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.msg,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.msg
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update attendance'
                            });
                        }
                    });
                }
            });
        }

        $(document).ready(function() {
            // Initialize Chosen
            $('.chosen-select').chosen({
                width: '100%',
                placeholder_text_single: '-- Select Option --'
            });

            $('#table').bootstrapTable({
                exportDataType: 'all',
                printPageBuilder: function(table) {
                    return myCustomPrint(table, "List of Subject Loads");
                },
                queryParams: function(params) {
                    return {
                        teacher_id: '{{ $teacherId }}',
                        school_year_id: $('#school_year').val()
                    };
                },
                onLoadError: function(status, res) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load data. Please try again later.'
                    });
                }
            });

            $('#school_year').on('change', function() {
                $('#table').bootstrapTable('refresh', {
                    url: '{{ route('teacher.subjects-teacher-load') }}?school_year_id=' +
                        $(this).val()
                });
            });

            $('#back-btn').click(function() {
                $('#subject-list').show();
                $('#attendance-list').hide();
                subjectLoadId = null;
                subjectName = null;
            });

            $('#add-btn').click(function() {
                const currentDate = new Date().toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                }).split('/').join('-');
                Swal.fire({
                    title: 'Generate Attendance?',
                    html: `For date: <strong>${currentDate}</strong>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Generate',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'POST',
                            url: '{{ route('attendances.generate') }}',
                            data: {
                                subject_load_id: subjectLoadId,
                                attendance_date: currentDate,
                            },
                            dataType: 'JSON',
                            success: function(response) {
                                if (response.valid) {
                                    $('#show-msg').html(
                                        `<div class="alert alert-success">${response.msg}</div>`
                                    );
                                    $('#table1').bootstrapTable('refresh');
                                    setTimeout(() => $('#show-msg').html(''), 5000);
                                } else {
                                    $('#show-msg').html(
                                        `<div class="alert alert-danger">${response.msg}</div>`
                                    );
                                    setTimeout(() => $('#show-msg').html(''), 5000);
                                }
                            },
                            error: function(jqXHR) {
                                const errorMsg = jqXHR.responseJSON?.msg ||
                                    'Failed to generate attendance';
                                $('#show-msg').html(
                                    `<div class="alert alert-danger">${errorMsg}</div>`
                                );
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            }
                        });
                    }
                });
            });

            $('#rfid_no').on('input', function() {
                let rfid_no = $(this).val().trim();
                if (rfid_no.length >= 7) { // Adjust based on RFID tag length
                    $.ajax({
                        method: 'POST',
                        url: '{{ route('attendances.processRfid') }}',
                        data: {
                            rfid_no: rfid_no,
                            subject_load_id: subjectLoadId,
                            attendance_date: attendanceDate,
                        },
                        dataType: 'JSON',
                        success: function(response) {
                            $('#rfid_no').val('').focus();
                            if (response.valid) {
                                $('#table2').bootstrapTable('refresh');
                                $('#table1').bootstrapTable('refresh');
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.msg,
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.msg
                                });
                            }
                        },
                        error: function(jqXHR) {
                            $('#rfid_no').val('').focus();
                            const errorMsg = jqXHR.responseJSON?.msg ||
                                'Failed to process RFID';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        }
                    });
                }
            });

            $('#viewStudent').on('shown.bs.modal', function() {
                $('#rfid_no').focus();
            });
        });
    </script>
@endsection
