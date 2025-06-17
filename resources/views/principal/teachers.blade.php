@extends('layout.master')
@section('title')
    | Teacher
@endsection
@section('active-teacher-list')
    active
@endsection
@section('app-title')
    Teachers
@endsection
@section('content')
    <div id="toolbar">
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="/teachers/" data-toolbar="#toolbar">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="image">Image</th>
                <th data-field="teacher_name">Teacher Name</th>
                <th data-field="contact">CONTACT</th>
                <th data-field="email">EMAIL</th>
                <th data-field="role">ROLE</th>
            </tr>
        </thead>
    </table>

    <!-- Adviser Modal -->
    <div class="modal fade" id="adviserModal" tabindex="-1" role="dialog" aria-labelledby="adviserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="adviserForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="adviserModalLabel">Set Adviser</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="adviserFormMsg"></div>
                    <input type="hidden" id="teacher_id" name="teacher_id">
                    <input type="hidden" id="adviser_id" name="adviser_id">
                    <div class="form-group">
                        <label for="grade_level">Grade Level: <span class="text-danger">*</span></label>
                        <select class="form-control" id="grade_level" name="grade_level" required>
                            <option value="" disabled selected>-- Select Grade Level --</option>
                            @foreach ([7, 8, 9, 10, 11, 12] as $level)
                                <option value="{{ $level }}">{{ $level }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="section">Section: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="section" name="section" placeholder="e.g., A"
                            required maxlength="20">
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
                    return myCustomPrint(table, "List of Teachers");
                },
            });


            // Adviser modal form validation
            $('#adviserForm').validate({
                rules: {
                    grade_level: {
                        required: true
                    },
                    section: {
                        required: true,
                        maxlength: 20
                    },
                },
                messages: {
                    grade_level: {
                        required: "Please select a grade level."
                    },
                    section: {
                        required: "Please enter a section.",
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

            // Adviser form submission
            $('#adviserForm').submit(function(event) {
                event.preventDefault();
                if ($(this).valid()) {
                    $('#adviserFormMsg').html('');
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

                    const isUpdate = $('#adviser_id').val() !== '';
                    const url = isUpdate ? `/advisers/${$('#adviser_id').val()}` : '/advisers';
                    const method = isUpdate ? 'PUT' : 'POST';

                    Swal.fire({
                        title: isUpdate ? 'Update Adviser?' : 'Set Adviser?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: isUpdate ? 'Update' : 'Set',
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
                                        $('#adviserModal').modal('hide');
                                        $('#adviserFormMsg').html('');
                                        $('#adviserForm').trigger('reset');
                                        $table.bootstrapTable('refresh');
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    } else {
                                        $('#adviserFormMsg').html(
                                            '<div class="alert alert-danger">' +
                                            response.msg + '</div>');
                                    }
                                },
                                error: function(jqXHR) {
                                    $('#adviserFormMsg').html(
                                        '<div class="alert alert-danger">Something went wrong! Please try again later.</div>'
                                    );
                                }
                            });
                        }
                    });
                }
            });
        });

        function setAdviser(teacherId, isUpdate = false) {
            $('#adviserForm').trigger('reset');
            $('#adviserFormMsg').html('');
            $('#teacher_id').val(teacherId);
            $('#adviser_id').val('');
            $('#adviserModalLabel').text(isUpdate ? 'Update Adviser' : 'Set Adviser');

            if (isUpdate) {
                $.ajax({
                    method: 'GET',
                    url: `/advisers/teacher/${teacherId}`,
                    dataType: 'JSON',
                    cache: false,
                    success: function(response) {
                        if (response.adviser) {
                            $('#adviser_id').val(response.adviser.id);
                            $('#grade_level').val(response.adviser.grade_level);
                            $('#section').val(response.adviser.section);
                            $('#grade_level').trigger('chosen:updated');
                        }
                        $('#adviserModal').modal('show');
                    },
                    error: function(jqXHR) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Failed to load adviser details. Please try again later.'
                        });
                    }
                });
            } else {
                $('#adviserModal').modal('show');
            }
        }
    </script>
@endsection
