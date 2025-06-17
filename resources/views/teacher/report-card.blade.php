@extends('layout.master')
@section('title')
    | Report Card
@endsection
@section('active-report-card')
    active
@endsection
@section('app-title')
    Report Card
@endsection
@section('content')
    <div id="toolbar">
        <label for="school_year">School Year:</label>
        <select name="school_year" id="school_year" class="form-control">
            @foreach ($schoolYears as $year)
                <option value="{{ $year->id }}" {{ $year->current ? 'selected' : '' }}>{{ $year->school_year }}</option>
            @endforeach
        </select>
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="{{ route('teacher.advisory-students') }}" data-toolbar="#toolbar" data-toolbar-align="left">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="image">Image</th>
                <th data-field="rfid_no">RFID No</th>
                <th data-field="student_lrn">Student LRN</th>
                <th data-field="student_name">Student Name</th>
                <th data-field="contact">Contact</th>
                <th data-field="email">Email</th>
                <th data-field="status">Status</th>
                <th data-field="action">Action</th>
            </tr>
        </thead>
    </table>
    <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exportModalLabel">Exported Files</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="file-content">
                    <ul id="fileList" class="list-group"></ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        function generateReportCard(studentId) {
            const startTime = Date.now();
            let timerInterval;

            Swal.fire({
                title: 'Generating Student Report Card',
                html: 'Please wait... Time Taken: <b>0</b> seconds',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            timerInterval = setInterval(() => {
                const currentTime = Date.now();
                const timeTaken = ((currentTime - startTime) / 1000).toFixed(2);
                const timerElement = Swal.getHtmlContainer().querySelector('b');
                if (timerElement) timerElement.textContent = timeTaken;
            }, 1000);

            $.ajax({
                method: 'POST',
                url: `/reportCards/${studentId}/${$('#school_year').val()}`,
                dataType: 'JSON',
                cache: false,
                success: function(response) {
                    if (response.valid) {
                        $('#show-msg').html(
                            '<div class="alert alert-success">' +
                            response.msg + '</div>'
                        );

                        // Remove the notification after 5 seconds (5000 milliseconds)
                        setTimeout(function() {
                            $('#show-msg').html(
                                ''); // Clears the notification
                        }, 5000); // 5000 milliseconds = 5 seconds


                        $('#file-content').html('');
                        $('#file-content').html('<ul id="fileList" class="list-group"></ul>');

                        // Add the exported file to the modal list
                        const filePath = response.file_path;
                        const fileName = filePath.split('/').pop();
                        $('#fileList').append(
                            `<li class="list-group-item">
                                <a href="${filePath}" target="_blank" download>${fileName}</a>
                            </li>`
                        );

                        $('#file-content').append(response.download);

                        // Show the modal
                        $('#exportModal').modal('show');
                    } else {
                        $('#show-msg').html(
                            '<div class="alert alert-danger">' +
                            response.msg + '</div>'
                        );

                        // Remove the notification after 5 seconds (5000 milliseconds)
                        setTimeout(function() {
                            $('#show-msg').html(
                                ''); // Clears the notification
                        }, 5000); // 5000 milliseconds = 5 seconds

                    }
                    clearInterval(timerInterval);
                    Swal.close();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    if (jqXHR.responseJSON && jqXHR.responseJSON
                        .error) {
                        var errors = jqXHR.responseJSON.error;
                        var errorMsg = "Error submitting data: " +
                            errors + ". ";
                        $('#show-msg').html(
                            '<div class="alert alert-danger">' +
                            errorMsg + '</div>'
                        );

                        // Remove the notification after 5 seconds (5000 milliseconds)
                        setTimeout(function() {
                            $('#show-msg').html(
                                ''); // Clears the notification
                        }, 5000); // 5000 milliseconds = 5 seconds

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong! Please try again later.'
                        });
                    }
                }
            });
        }

        $(document).ready(function() {
            var $table = $('#table');
            var $schoolYear = $('#school_year');

            // Initialize Bootstrap Table
            $table.bootstrapTable({
                exportDataType: 'all',
                printPageBuilder: function(table) {
                    return myCustomPrint(table, "List of Students");
                },
                queryParams: function(params) {
                    return {
                        school_year_id: $schoolYear.val()
                    };
                }
            });

            // Refresh table when school year changes
            $schoolYear.on('change', function() {
                $table.bootstrapTable('refresh', {
                    url: '{{ route('teacher.advisory-students') }}?school_year_id=' + $(this).val()
                });
            });
        });
    </script>
@endsection
