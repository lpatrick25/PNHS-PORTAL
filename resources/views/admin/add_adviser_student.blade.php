@extends('layout.master')
@section('title')
    | Advisory Students
@endsection
@section('active-adviser-list')
    active
@endsection
@section('app-title')
    Advisory Students
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-6">
            <h3 class="card-title">
                Adviser: <span class="text-danger">{{ $adviser->teacher->full_name_with_extension }}</span><br>
                Grade Level: <span class="text-danger">{{ $adviser->grade_level }}</span></h3>
        </div>
        <div class="col-lg-3 text-left">
            <h3 class="card-title">
                School Year: <span class="text-danger">{{ $currentSchoolYear->school_year }}</span><br>
                Section: <span class="text-danger">{{ $adviser->section }}</span>
            </h3>
        </div>
        <div class="col-lg-3">
            <select name="school_year" id="school_year" class="form-control">
                <option value="" disabled>-- Select School Year --</option>
                @foreach ($schoolYears as $schoolYear)
                    <option value="{{ $schoolYear->id }}" {{ $currentSchoolYear->id == $schoolYear->id ? 'selected' : '' }}>
                        {{ $schoolYear->school_year }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div id="toolbar">
        <div class="btn-group">
            <button onclick="window.history.back()" class="btn btn-danger btn-md">
                <i class="fa fa-arrow-left"></i> Go Back
            </button>
            <button class="btn btn-primary btn-md" data-toggle="modal" data-target="#addStudentModal">
                <i class="fa fa-plus"></i> Add Student
            </button>
        </div>
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="/studentStatuses/getAdviserStudents/{{ $adviser->id }}/{{ $currentSchoolYear->id }}"
        data-toolbar="#toolbar">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="image">Image</th>
                <th data-field="student_lrn">Student LRN</th>
                <th data-field="student_name">Student Name</th>
                <th data-field="status">Status</th>
                <th data-field="action" data-print-ignore="true">Action</th>
            </tr>
        </thead>
    </table>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1" role="dialog" aria-labelledby="addStudentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="addStudentForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStudentModalLabel">Add Student</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="addStudentFormMsg"></div>
                    <input type="hidden" id="adviser_id" name="adviser_id" value="{{ $adviser->id ?? '' }}">
                    <input type="hidden" id="grade_level" name="grade_level" value="{{ $adviser->grade_level ?? '' }}">
                    <input type="hidden" id="section" name="section" value="{{ $adviser->section ?? '' }}">
                    <input type="hidden" id="school_year_id" name="school_year_id"
                        value="{{ $currentSchoolYear->id ?? '' }}">
                    <input type="hidden" id="status" name="status" value="ENROLLED">
                    <div class="form-group">
                        <label for="student_id">Student: <span class="text-danger">*</span></label>
                        <select class="form-control" id="student_id" name="student_id" required>
                            <option value="" disabled selected>-- Select Student --</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            var $table = $('#table');
            $table.bootstrapTable({
                exportDataType: $(this).val(),
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, "List of Advisory Students");
                },
            });

            // Refresh student dropdown
            function refreshStudentDropdown() {
                $.ajax({
                    method: 'GET',
                    url: '/studentStatuses/not-enrolled/' + $('#school_year_id').val(),
                    dataType: 'JSON',
                    success: function(data) {
                        const $studentSelect = $('#student_id');
                        $('#student_id').empty().append(
                            '<option selected="true" value="NONE">-- Select Student --</option>'
                        );
                        data.forEach(function(student) {
                            $('#student_id').append('<option value="' + student.id + '">' +
                                student.full_name_with_extension +
                                '</option>');
                        });
                        $('#student_id').trigger('chosen:updated');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to load students',
                            text: 'Please try again later.'
                        });
                    }
                });
            }

            // Load students for initial school year
            refreshStudentDropdown();

            // Update table and student dropdown on school year change
            $('#school_year').change(function() {
                const schoolYearId = $(this).val();
                if (schoolYearId) {
                    $table.bootstrapTable('refresh', {
                        url: '/studentStatuses/getAdviserStudents/{{ $adviser->id }}/' +
                            schoolYearId
                    });
                }
            });

            // Form validation
            $('#addStudentForm').validate({
                rules: {
                    student_id: {
                        required: true
                    },
                },
                messages: {
                    student_id: {
                        required: "Please select a student."
                    },
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element) {
                    $(element).removeClass('is-invalid');
                }
            });

            // Form submission
            $('#addStudentForm').submit(function(event) {
                event.preventDefault();
                if ($(this).valid()) {
                    $('#addStudentFormMsg').html('');
                    let isEmptyField = false;
                    $(this).find('input[required], select[required]').each(function() {
                        if ($(this).val() === '' || $(this).val() === null) {
                            isEmptyField = true;
                        }
                    });

                    if (isEmptyField) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Some fields are empty'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Add Student?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Add',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                method: 'POST',
                                url: '/studentStatuses',
                                data: $(this).serialize(),
                                dataType: 'JSON',
                                cache: false,
                                success: function(response) {
                                    if (response.valid) {
                                        $('#addStudentModal').modal('hide');
                                        $('#addStudentFormMsg').html('');
                                        $table.bootstrapTable('refresh');
                                        refreshStudentDropdown();
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    } else {
                                        $('#addStudentFormMsg').html(
                                            '<div class="alert alert-danger">' +
                                            response.msg + '</div>');
                                    }
                                },
                                error: function(jqXHR) {
                                    $('#addStudentFormMsg').html(
                                        '<div class="alert alert-danger">Something went wrong! Please try again later.</div>'
                                    );
                                }
                            });
                        }
                    });
                }
            });
        });

        function removeStudentFromAdviser(studentStatusId) {
            Swal.fire({
                title: 'Remove Student?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Remove',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'DELETE',
                        url: '/studentStatuses/' + studentStatusId,
                        dataType: 'JSON',
                        cache: false,
                        success: function(response) {
                            if (response.valid) {
                                $('#table').bootstrapTable('refresh');
                                refreshStudentDropdown();
                                Swal.fire({
                                    icon: 'success',
                                    title: response.msg,
                                    showConfirmButton: false,
                                    timer: 1500
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
                                text: 'Something went wrong! Please try again later.'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
