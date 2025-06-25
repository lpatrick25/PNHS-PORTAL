@extends('layout.master')
@section('title')
    | Subjects
@endsection
@section('active-subject-list')
    active
@endsection
@section('app-title')
    Subjects
@endsection
@section('content')
    <div id="toolbar">
        <button class="btn btn-primary" data-toggle="modal" data-target="#subjectModal" onclick="resetSubjectForm()">
            <i class="fa fa-plus"></i> Add Subject
        </button>
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="/subjects" data-toolbar="#toolbar">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="subject_code">Subject Code</th>
                <th data-field="subject_name">Subject Name</th>
                <th data-field="action">Action</th>
            </tr>
        </thead>
    </table>

    <!-- Subject Modal -->
    <div class="modal fade" id="subjectModal" tabindex="-1" role="dialog" aria-labelledby="subjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="subjectForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subjectModalLabel">Add Subject</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="subjectFormMsg"></div>
                    <input type="hidden" id="subject_id" name="subject_id">
                    <div class="form-group">
                        <label for="subject_code">Subject Code: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject_code" name="subject_code" required
                            maxlength="255">
                    </div>
                    <div class="form-group">
                        <label for="subject_name">Subject Name: <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="subject_name" name="subject_name" required
                            maxlength="50">
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
                    return myCustomPrint(table, "List of Subjects");
                },
            });

            // Form validation
            $('#subjectForm').validate({
                rules: {
                    subject_code: {
                        required: true,
                        maxlength: 255
                    },
                    subject_name: {
                        required: true,
                        maxlength: 50
                    },
                },
                messages: {
                    subject_code: {
                        required: "Please enter a subject code.",
                        maxlength: "Subject code must not exceed 255 characters."
                    },
                    subject_name: {
                        required: "Please enter a subject name.",
                        maxlength: "Subject name must not exceed 50 characters."
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
            $('#subjectForm').submit(function(event) {
                event.preventDefault();
                if ($(this).valid()) {
                    $('#subjectFormMsg').html('');
                    const subjectId = $('#subject_id').val();
                    const isUpdate = subjectId !== '';
                    const url = isUpdate ? `/subjects/${subjectId}` : '/subjects';
                    const method = isUpdate ? 'PUT' : 'POST';

                    Swal.fire({
                        title: isUpdate ? 'Update Subject?' : 'Add Subject?',
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
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                },
                                success: function(response) {
                                    if (response.valid) {
                                        $('#subjectModal').modal('hide');
                                        $('#subjectFormMsg').html('');
                                        $('#subjectForm').trigger('reset');
                                        $('#subject_id').val('');
                                        $table.bootstrapTable('refresh');
                                        Swal.fire({
                                            icon: 'success',
                                            title: response.msg,
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                    } else {
                                        $('#subjectFormMsg').html(
                                            '<div class="alert alert-danger">' +
                                            response.msg + '</div>');
                                    }
                                },
                                error: function(jqXHR) {
                                    let errorMessage =
                                        '<div class="alert alert-danger">';

                                    try {
                                        const response = JSON.parse(jqXHR
                                            .responseText);

                                        // Display main message if available
                                        if (response.message) {
                                            errorMessage +=
                                                `<p>${response.message}</p>`;
                                        }

                                        if (response.errors) {
                                            errorMessage += '<ul>';
                                            for (const field in response.errors) {
                                                response.errors[field].forEach(
                                                    error => {
                                                        errorMessage +=
                                                            `<li>${error}</li>`;
                                                    });
                                            }
                                            errorMessage += '</ul>';
                                        }

                                    } catch (e) {
                                        errorMessage +=
                                            'Something went wrong! Please try again later.';
                                    }

                                    errorMessage += '</div>';
                                    $('#subjectFormMsg').html(errorMessage);
                                }
                            });
                        }
                    });
                }
            });
        });

        function resetSubjectForm() {
            $('#subjectForm').trigger('reset');
            $('#subject_id').val('');
            $('#subjectFormMsg').html('');
            $('#subjectModalLabel').text('Add Subject');
        }

        function editSubject(subjectId) {
            $.ajax({
                method: 'GET',
                url: `/subjects/${subjectId}`,
                dataType: 'JSON',
                success: function(data) {
                    $('#subject_id').val(data.id);
                    $('#subject_code').val(data.subject_code);
                    $('#subject_name').val(data.subject_name);
                    $('#subjectModalLabel').text('Update Subject');
                    $('#subjectModal').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to load subject',
                        text: 'Please try again later.'
                    });
                }
            });
        }

        function deleteSubject(subjectId) {
            Swal.fire({
                title: 'Delete Subject?',
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
                        url: `/subjects/${subjectId}`,
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
