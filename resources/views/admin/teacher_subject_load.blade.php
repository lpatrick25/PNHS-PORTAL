@extends('layout.master')
@section('title')
    | Teacher Subject Loads
@endsection
@section('active-teacher-subject-list')
    active
@endsection
@section('app-title')
    Teacher Subject Loads
@endsection
@section('content')
    <div id="toolbar">
        <button class="btn btn-danger" onclick="window.history.back();"><i class="fa fa-arrow-left"></i> Go Back</button>
        <button class="btn btn-primary" data-toggle="modal" data-target="#loadModal" onclick="resetLoadForm()">
            <i class="fa fa-plus"></i> Add Teacher Subject Load
        </button>
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="/teacherSubjectLoads?teacher_id={{ $teacherId }}&school_year_id={{ $currentSchoolYear->id }}"
        data-toolbar="#toolbar">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="teacher_name">Teacher Name</th>
                <th data-field="subject_name">Subject Name</th>
                <th data-field="school_year">School Year</th>
                <th data-field="grade_level">Grade Level</th>
                <th data-field="section">Section</th>
                <th data-field="action">Action</th>
            </tr>
        </thead>
    </table>

    <!-- Load Modal -->
    <div class="modal fade" id="loadModal" tabindex="-1" role="dialog" aria-labelledby="loadModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="loadForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loadModalLabel">Add Teacher Subject Load</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="loadFormMsg"></div>
                    <input type="hidden" id="load_id" name="load_id">
                    <input type="hidden" id="teacher_id" name="teacher_id" value="{{ $teacherId }}">
                    <input type="hidden" id="school_year_id" name="school_year_id" value="{{ $currentSchoolYear->id }}">
                    <div class="form-group">
                        <label for="subject_id">Subject: <span class="text-danger">*</span></label>
                        <select class="form-control" id="subject_id" name="subject_id" required>
                            <option value="" disabled selected>-- Select Subject --</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="grade_level">Grade Level: <span class="text-danger">*</span></label>
                        <select class="form-control" id="grade_level" name="grade_level" required>
                            <option value="" disabled selected>-- Select Grade Level --</option>
                            @foreach ([7, 8, 9, 10, 11, 12] as $level)
                                <option value="{{ $level }}">Grade {{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="section">Section: <span class="text-danger">*</span></label>
                        <select class="form-control" id="section" name="section" required>
                            <option value="" disabled selected>-- Select Section --</option>
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
                    return myCustomPrint(table, "List of Teacher Subject Loads");
                },
                onLoadError: function(status, res) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load data. Please try again later.'
                    });
                }
            });

            // Load sections based on grade level
            $('#grade_level').change(function() {
                const gradeLevel = $(this).val();
                if (gradeLevel) {
                    $.ajax({
                        method: 'GET',
                        url: '{{ route('admin.getSectionsByGradeLevel') }}',
                        data: {
                            grade_level: gradeLevel
                        },
                        dataType: 'JSON',
                        success: function(sections) {
                            const $sectionSelect = $('#section');
                            $sectionSelect.empty().append(
                                '<option value="" disabled selected>-- Select Section --</option>'
                            );
                            sections.forEach(function(section) {
                                $sectionSelect.append('<option value="' + section +
                                    '">' + section + '</option>');
                            });
                            $sectionSelect.trigger('chosen:updated');
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to load sections',
                                text: 'Please try again later.'
                            });
                        }
                    });
                } else {
                    $('#section').empty().append(
                        '<option value="" disabled selected>-- Select Section --</option>').trigger(
                        'change');
                }
            });

            // Form validation
            $('#loadForm').validate({
                rules: {
                    teacher_id: {
                        required: true
                    },
                    subject_id: {
                        required: true
                    },
                    school_year_id: {
                        required: true
                    },
                    grade_level: {
                        required: true
                    },
                    section: {
                        required: true,
                        maxlength: 20
                    },
                },
                messages: {
                    teacher_id: {
                        required: "Please select a teacher."
                    },
                    subject_id: {
                        required: "Please select a subject."
                    },
                    school_year_id: {
                        required: "Please select a school year."
                    },
                    grade_level: {
                        required: "Please select a grade level."
                    },
                    section: {
                        required: "Please select a section.",
                        maxlength: "Section must not exceed 20 characters."
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
            $('#loadForm').submit(function(event) {
                event.preventDefault();
                if ($(this).valid()) {
                    $('#loadFormMsg').html('');
                    const loadId = $('#load_id').val();
                    const isUpdate = loadId !== '';
                    const url = isUpdate ? `/teacherSubjectLoads/${loadId}` : '/teacherSubjectLoads';
                    const method = isUpdate ? 'PUT' : 'POST';

                    Swal.fire({
                        title: isUpdate ? 'Update Load?' : 'Add Load?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: isUpdate ? 'Update' : 'Add',
                        cancelButtonText: 'Cancel',
                        reverseButtons: true,
                        allowOutsideClick: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                method: method,
                                url: url,
                                data: $(this).serialize(),
                                dataType: 'JSON',
                                cache: false,
                                success: function(response) {
                                    if (response.valid) {
                                        $('#loadModal').modal('hide');
                                        $('#loadFormMsg').html('');
                                        $('#load_id').val('');
                                        $('#subject_id, #section').trigger('change');
                                        $table.bootstrapTable('refresh');
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    } else {
                                        $('#loadFormMsg').html(
                                            '<div class="alert alert-danger">' +
                                            response.msg + '</div>');
                                    }
                                },
                                error: function(jqXHR) {
                                    const errorMsg = jqXHR.responseJSON?.message ||
                                        'Something went wrong! Please try again later.';
                                    $('#loadFormMsg').html(
                                        '<div class="alert alert-danger">' +
                                        errorMsg + '</div>');
                                }
                            });
                        }
                    });
                }
            });
        });

        function resetLoadForm() {
            // $('#loadForm').trigger('reset');
            $('#load_id').val('');
            $('#loadFormMsg').html('');
            $('#teacher_id, #subject_id, #section').trigger('chosen:updated');
            $('#loadModalLabel').text('Add Teacher Subject Load');
        }

        function editLoad(loadId) {
            $.ajax({
                method: 'GET',
                url: `/teacherSubjectLoads/${loadId}`,
                dataType: 'JSON',
                success: function(data) {
                    $('#load_id').val(data.id);
                    $('#subject_id').val(data.subject_id).trigger('chosen:updated');
                    $('#grade_level').val(data.grade_level);
                    $.ajax({
                        method: 'GET',
                        url: '{{ route('admin.getSectionsByGradeLevel') }}',
                        data: {
                            grade_level: data.grade_level
                        },
                        dataType: 'JSON',
                        success: function(sections) {
                            const $sectionSelect = $('#section');
                            $sectionSelect.empty().append(
                                '<option value="" disabled>-- Select Section --</option>');
                            sections.forEach(function(section) {
                                $sectionSelect.append('<option value="' + section + '">' +
                                    section + '</option>');
                            });
                            $sectionSelect.val(data.section).trigger('chosen:updated');
                            $('#loadModalLabel').text('Update Teacher Subject Load');
                            $('#loadModal').modal('show');
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Failed to load sections',
                                text: 'Please try again later.'
                            });
                        }
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to load data',
                        text: 'Please try again later.'
                    });
                }
            });
        }

        function deleteLoad(loadId) {
            Swal.fire({
                title: 'Delete Load?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Delete',
                cancelButtonText: 'Cancel',
                reverseButtons: true,
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        method: 'DELETE',
                        url: `/teacherSubjectLoads/${loadId}`,
                        dataType: 'JSON',
                        cache: false,
                        success: function(response) {
                            if (response.valid) {
                                $('#table').bootstrapTable('refresh');
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
