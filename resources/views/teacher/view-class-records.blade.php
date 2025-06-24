@extends('layout.master')
@section('title')
    | {{ $subjectLoad->subject->subject_code . ' - ' . $subjectLoad->subject->subject_name }}
@endsection
@section('active-class-records')
    active
@endsection
@section('app-title')
    Class Record <span class="text-success" style="font-weight: bolder;">Grade {{ $subjectLoad->grade_level }}</span> - <span
        class="text-danger" style="font-weight: bolder;">{{ $subjectLoad->section }}</span>
@endsection
@section('custom-css')
    <style type="text/css">
        td,
        th,
        thead {
            border: 1px solid black;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            text-align: center;
            padding: 8px;
        }

        .bootstrap-table .fixed-table-container .table th,
        .bootstrap-table .fixed-table-container .table td {
            vertical-align: middle;
            box-sizing: border-box;
            border: 1px solid black;
        }

        .editable-click:after {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            content: "\f044";
            margin-left: 5px;
            color: #007bff;
        }

        .editableform-loading:after {
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            content: "\f110";
            margin-left: 5px;
            animation: spin 1s infinite linear;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .editableform {
            margin: 10px !important;
        }

        .bootstrap-table .fixed-table-container .table thead th {
            vertical-align: middle;
        }
    </style>
@endsection
@section('content')
    <div id="show-msg"></div>
    <input type="hidden" id="subjectLoadId" value="{{ $subjectLoad->id }}">
    <div class="row" id="class-records-tables">
        <div class="col-lg-12">
            <hr class="card card-outline card-success">
            <div class="row">
                <div class="col-lg-6 text-left">
                    <a href="{{ route('viewClassRecordTeacher') }}" class="btn btn-danger btn-md"><i
                            class="fa fa-arrow-left"></i> Go Back</a>
                </div>
                <div class="col-lg-2">
                    <div class="form-group">
                        <select name="select_quarter" id="select_quarter" class="form-control">
                            <option value="1st Quarter">1st Quarter</option>
                            <option value="2nd Quarter">2nd Quarter</option>
                            <option value="3rd Quarter">3rd Quarter</option>
                            <option value="4th Quarter">4th Quarter</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2 text-right">
                    <button class="btn btn-primary btn-md" id="generate-btn"><i class="fa fa-sync"></i> Generate
                        Records</button>
                </div>
                <div class="col-lg-2 text-right">
                    <button class="btn btn-success btn-md" onclick="exportToExcel('{{ $subjectLoad->id }}')"><i
                            class="fa fa-file-excel"></i> Export to Excel</button>
                </div>
            </div>
            <hr class="card card-outline card-success">
        </div>
        <div class="col-lg-12 x-editable-list">
            <div id="toolbar-1">
                <h3>{{ $subjectLoad->subject->subject_name }}</h3>
            </div>
            <table id="table1" data-show-refresh="true" data-auto-refresh="true" data-pagination="false"
                data-show-columns="false" data-cookie="false" data-cookie-id-table="records_table" data-search="false"
                data-click-to-select="false" data-show-copy-rows="false" data-page-number="1" data-show-toggle="false"
                data-show-export="false" data-filter-control="true" data-show-search-clear-button="false"
                data-key-events="false" data-mobile-responsive="true" data-check-on-init="true" data-show-print="false"
                data-sticky-header="true" data-toolbar="#toolbar-1">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
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
        function exportToExcel(subject_load_id) {
            const startTime = Date.now();
            let timerInterval;

            Swal.fire({
                title: 'Exporting Class Records',
                html: 'Please wait... Time Taken: <b>0</b> seconds',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            timerInterval = setInterval(() => {
                const currentTime = Date.now();
                const timeTaken = ((currentTime - startTime) / 1000).toFixed(2);
                Swal.getHtmlContainer().querySelector('b').textContent = timeTaken;
            }, 1000);

            $.ajax({
                method: 'GET',
                url: '{{ route('classRecords.export', '') }}/' + subject_load_id,
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    clearInterval(timerInterval);
                    Swal.close();
                    if (response.valid) {
                        $('#show-msg').html(`<div class="alert alert-success">${response.msg}</div>`);
                        setTimeout(() => $('#show-msg').html(''), 5000);
                        $('#fileList').empty();
                        $('#fileList').append(`
                            <li class="list-group-item">
                                <a href="${response.file_path}" target="_blank" download="${response.file_name}">${response.file_name}</a>
                            </li>
                        `);
                        $('#exportModal').modal('show');
                    } else {
                        $('#show-msg').html(`<div class="alert alert-danger">${response.msg}</div>`);
                        setTimeout(() => $('#show-msg').html(''), 5000);
                    }
                },
                error: function(jqXHR) {
                    clearInterval(timerInterval);
                    Swal.close();
                    const errorMsg = jqXHR.responseJSON?.msg || 'Failed to export class records';
                    $('#show-msg').html(`<div class="alert alert-danger">${errorMsg}</div>`);
                    setTimeout(() => $('#show-msg').html(''), 5000);
                }
            });
        }

        // function defaultTableHeader() {
        //     return `
    //     <tr>
    //         <th class="text-center" style="min-width: 200px;">LEARNERS' NAME</th>
    //         <th colspan="10" class="text-center">WRITTEN WORKS (30%)</th>
    //         <th colspan="10" class="text-center">PERFORMANCE TASKS (50%)</th>
    //         <th class="text-center" style="min-width: 50px;">QUARTERLY ASSESSMENT (20%)</th>
    //     </tr>
    //     <tr>
    //         <th></th>
    //         ${Array.from({length: 10}, (_, i) => `<th>${i+1}</th>`).join('')}
    //         ${Array.from({length: 10}, (_, i) => `<th>${i+1}</th>`).join('')}
    //         <th>1</th>
    //     </tr>
    // `;
        // }
        function defaultTableHeader() {
            const generateColumns = (count, start = 1) =>
                Array.from({
                    length: count
                }, (_, i) => `<th>${i + start}</th>`).join('');

            return `
        <tr>
            <th class="text-center" style="min-width: 200px;">LEARNERS' NAME</th>
            <th colspan="10" class="text-center">WRITTEN WORKS (30%)</th>
            <th colspan="10" class="text-center">PERFORMANCE TASKS (50%)</th>
            <th class="text-center" style="min-width: 50px;">QUARTERLY ASSESSMENT (20%)</th>
        </tr>
        <tr>
            <th></th>
            ${generateColumns(10)}
            ${generateColumns(10)}
            <th>1</th>
        </tr>
    `;
        }

        // function refresh_tables() {
        //     $.ajax({
        //         url: '{{ route('classRecords.bySubjectLoad') }}',
        //         method: 'GET',
        //         data: {
        //             subject_load_id: $('#subjectLoadId').val(),
        //             quarter: $('#select_quarter').val(),
        //         },
        //         success: function(response) {
        //             const students = response.students || [];
        //             const scores = response.scores || {};
        //             const current = response.current || false;

        //             const thead = $("#table1 thead");
        //             thead.empty();
        //             thead.append(defaultTableHeader());

        //             if (students.length > 0) {
        //                 let headerRow = `<tr>`;
        //                 headerRow +=
        //                     `<th class="text-center" style="min-width: 200px;">Highest Possible Score</th>`;
        //                 scores.writtenWorks.forEach((score, index) => {
        //                     headerRow += `
    //                         <th class="editable"
    //                             data-name="total_written_work_${index + 1}"
    //                             data-pk="Written Works ${index + 1},${score.quarter},${$('#subjectLoadId').val()}"
    //                             data-update-type="totalScore">
    //                             ${score.score ?? ''}
    //                         </th>`;
        //                 });
        //                 scores.performanceTasks.forEach((score, index) => {
        //                     headerRow += `
    //                         <th class="editable"
    //                             data-name="total_performance_task_${index + 1}"
    //                             data-pk="Performance Tasks ${index + 1},${score.quarter},${$('#subjectLoadId').val()}"
    //                             data-update-type="totalScore">
    //                             ${score.score ?? ''}
    //                         </th>`;
        //                 });
        //                 headerRow += `
    //                     <th class="editable"
    //                         data-name="total_quarterly_assessment"
    //                         data-pk="Quarterly Assessment,${scores.quarterlyAssessment.quarter},${$('#subjectLoadId').val()}"
    //                         data-update-type="totalScore">
    //                         ${scores.quarterlyAssessment.score ?? ''}
    //                     </th>`;
        //                 headerRow += `</tr>`;
        //                 thead.append(headerRow);
        //             }

        //             const tbody = $("#table1 tbody");
        //             tbody.empty();
        //             students.forEach(student => {
        //                 let row = `<tr>`;
        //                 row += `<td class="text-left" style="min-width: 200px;">${student.name}</td>`;
        //                 student.writtenWorks.forEach((score, index) => {
        //                     row += `
    //                         <td class="editable"
    //                             data-name="written_work_${index + 1}"
    //                             data-pk="${student.writtenWorksRecordsID[index]}"
    //                             data-update-type="score">
    //                             ${score !== 0 ? score : ''}
    //                         </td>`;
        //                 });
        //                 student.performanceTasks.forEach((score, index) => {
        //                     row += `
    //                         <td class="editable"
    //                             data-name="performance_task_${index + 1}"
    //                             data-pk="${student.performanceTasksRecordsID[index]}"
    //                             data-update-type="score">
    //                             ${score !== 0 ? score : ''}
    //                         </td>`;
        //                 });
        //                 row += `
    //                     <td class="editable"
    //                         data-name="quarterly_assessment"
    //                         data-pk="${student.quarterlyAssessmentRecordsID}"
    //                         data-update-type="score">
    //                         ${student.quarterlyAssessment !== 0 ? student.quarterlyAssessment : ''}
    //                     </td>`;
        //                 row += `</tr>`;
        //                 tbody.append(row);
        //             });

        //             $('#table1').bootstrapTable({
        //                 stickyHeader: true,
        //                 filterControl: true,
        //                 search: true,
        //                 pagination: false,
        //                 autoRefresh: true,
        //                 toolbarAlign: 'right',
        //                 buttonsAlign: 'left',
        //                 searchAlign: 'left',
        //                 classes: 'table table-bordered table-hover x-editor-custom',
        //             });

        //             initializeEditable();
        //         },
        //         error: function(jqXHR) {
        //             Swal.fire({
        //                 icon: 'error',
        //                 title: 'Error',
        //                 text: jqXHR.responseJSON?.msg || 'Failed to fetch class records'
        //             });
        //         }
        //     });
        // }
        function refresh_tables() {
            $.ajax({
                url: '{{ route('classRecords.bySubjectLoad') }}',
                method: 'GET',
                data: {
                    subject_load_id: $('#subjectLoadId').val(),
                    quarter: $('#select_quarter').val(),
                },
                success: function(response) {
                    const students = response.students || [];
                    const scores = response.scores || {};
                    const current = response.current || false;

                    const thead = $("#table1 thead");
                    thead.empty();
                    thead.append(defaultTableHeader());

                    if (students.length > 0) {
                        let headerRow = `<tr>`;
                        headerRow +=
                            `<th class="text-center" style="min-width: 200px;">Highest Possible Score</th>`;
                        scores.writtenWorks.forEach((score, index) => {
                            headerRow += `
                        <th class="${current ? 'editable' : ''}"
                            ${current ? `data-name="total_written_work_${index + 1}"` : ''}
                            ${current ? `data-pk="Written Works ${index + 1},${score.quarter},${$('#subjectLoadId').val()}"` : ''}
                            ${current ? `data-update-type="totalScore"` : ''}>
                            ${score.score ?? ''}
                        </th>`;
                        });
                        scores.performanceTasks.forEach((score, index) => {
                            headerRow += `
                        <th class="${current ? 'editable' : ''}"
                            ${current ? `data-name="total_performance_task_${index + 1}"` : ''}
                            ${current ? `data-pk="Performance Tasks ${index + 1},${score.quarter},${$('#subjectLoadId').val()}"` : ''}
                            ${current ? `data-update-type="totalScore"` : ''}>
                            ${score.score ?? ''}
                        </th>`;
                        });
                        headerRow += `
                    <th class="${current ? 'editable' : ''}"
                        ${current ? `data-name="total_quarterly_assessment"` : ''}
                        ${current ? `data-pk="Quarterly Assessment,${scores.quarterlyAssessment.quarter},${$('#subjectLoadId').val()}"` : ''}
                        ${current ? `data-update-type="totalScore"` : ''}>
                        ${scores.quarterlyAssessment.score ?? ''}
                    </th>`;
                        headerRow += `</tr>`;
                        thead.append(headerRow);
                    }

                    const tbody = $("#table1 tbody");
                    tbody.empty();
                    students.forEach(student => {
                        let row = `<tr>`;
                        row += `<td class="text-left" style="min-width: 200px;">${student.name}</td>`;
                        student.writtenWorks.forEach((score, index) => {
                            row += `
                        <td class="${current ? 'editable' : ''}"
                            ${current ? `data-name="written_work_${index + 1}"` : ''}
                            ${current ? `data-pk="${student.writtenWorksRecordsID[index]}"` : ''}
                            ${current ? `data-update-type="score"` : ''}>
                            ${score !== 0 ? score : ''}
                        </td>`;
                        });
                        student.performanceTasks.forEach((score, index) => {
                            row += `
                        <td class="${current ? 'editable' : ''}"
                            ${current ? `data-name="performance_task_${index + 1}"` : ''}
                            ${current ? `data-pk="${student.performanceTasksRecordsID[index]}"` : ''}
                            ${current ? `data-update-type="score"` : ''}>
                            ${score !== 0 ? score : ''}
                        </td>`;
                        });
                        row += `
                    <td class="${current ? 'editable' : ''}"
                        ${current ? `data-name="quarterly_assessment"` : ''}
                        ${current ? `data-pk="${student.quarterlyAssessmentRecordsID}"` : ''}
                        ${current ? `data-update-type="score"` : ''}>
                        ${student.quarterlyAssessment !== 0 ? student.quarterlyAssessment : ''}
                    </td>`;
                        row += `</tr>`;
                        tbody.append(row);
                    });

                    $('#table1').bootstrapTable({
                        stickyHeader: true,
                        filterControl: true,
                        search: true,
                        pagination: false,
                        autoRefresh: true,
                        toolbarAlign: 'right',
                        buttonsAlign: 'left',
                        searchAlign: 'left',
                        classes: 'table table-bordered table-hover x-editor-custom',
                    });

                    // Only initialize editable fields if the quarter is current
                    if (current) {
                        initializeEditable();
                    }
                },
                error: function(jqXHR) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: jqXHR.responseJSON?.msg || 'Failed to fetch class records'
                    });
                }
            });
        }

        function initializeEditable() {
            $.fn.editable.defaults.mode = 'inline';
            $('.editable').editable({
                type: 'number',
                url: function(params) {
                    const pk = $(this).data('pk');
                    const updateType = $(this).data('update-type');
                    const value = params.value;

                    const url = updateType === 'totalScore' ?
                        '{{ route('classRecords.updateTotalScore') }}' :
                        '{{ route('classRecords.updateScore') }}';

                    const data = updateType === 'totalScore' ? {
                        records_name: pk.split(',')[0],
                        quarter: pk.split(',')[1],
                        subject_load_id: pk.split(',')[2],
                        value: value,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    } : {
                        pk: pk,
                        value: value,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    };

                    return $.ajax({
                        url: url,
                        method: 'POST',
                        data: data,
                        success: function(response) {
                            if (response.valid) {
                                $('#show-msg').html(
                                    `<div class="alert alert-success">${response.msg}</div>`);
                                $('#table1').bootstrapTable('refresh');
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.msg
                                });
                            }
                        },
                        error: function(jqXHR) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: jqXHR.responseJSON?.msg || 'Failed to update score'
                            });
                        }
                    });
                },
                validate: function(value) {
                    if ($.trim(value) === '') return 'This field is required.';
                    if (isNaN(value) || value < 0 || value > 100) return 'Enter a score between 0 and 100.';
                }
            });
        }

        $(document).ready(function() {
            $('#select_quarter').change(refresh_tables);
            $('#select_quarter').trigger('change');

            $('#generate-btn').click(function() {
                Swal.fire({
                    title: 'Generate Class Records?',
                    html: `For quarter: <strong>${$('#select_quarter').val()}</strong>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Generate'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            method: 'POST',
                            url: '{{ route('classRecords.generate') }}',
                            data: {
                                subject_load_id: $('#subjectLoadId').val(),
                                quarter: $('#select_quarter').val(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'JSON',
                            success: function(response) {
                                $('#show-msg').html(
                                    `<div class="alert alert-${response.valid ? 'success' : 'danger'}">${response.msg}</div>`
                                );
                                if (response.valid) {
                                    refresh_tables();
                                }
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            },
                            error: function(jqXHR) {
                                $('#show-msg').html(
                                    `<div class="alert alert-danger">${jqXHR.responseJSON?.msg || 'Failed to generate records'}</div>`
                                );
                                setTimeout(() => $('#show-msg').html(''), 5000);
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
