@extends('layout.master')
@section('title')
    | Subject Handled
@endsection
@section('active-subjects')
    active
@endsection
@section('app-title')
    Subject Handled
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
        data-url="{{ route('teacher.subjects-teacher-load') }}" data-toolbar="#toolbar" data-toolbar-align="left">
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
    <div id="viewStudent" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">List of Students</h3>
                </div>
                <div class="modal-body">
                    <table id="table1" class="table table-bordered"></table>
                </div>
                <div class="modal-footer text-right">
                    <button type="button" data-dismiss="modal" class="btn btn-danger btn-md"><i class="fa fa-times"></i>
                        Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript">
        function view(subjectLoadId) {
            var $table1 = $('#table1');
            $table1.bootstrapTable('destroy').bootstrapTable({
                autoRefresh: false,
                url: '{{ route('teacher.subject-students') }}?subject_load_id=' + subjectLoadId,
                formatLoadingMessage: function() {
                    return 'Fetching students, please wait...';
                },
                columns: [{
                        field: 'count',
                        title: '#'
                    },
                    {
                        field: 'image',
                        title: 'Image'
                    },
                    {
                        field: 'student_lrn',
                        title: 'Student LRN'
                    },
                    {
                        field: 'student_name',
                        title: 'Student Name'
                    }
                ]
            });
            $('#viewStudent').modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        }

        $(document).ready(function() {
            var $table = $('#table');
            var $schoolYear = $('#school_year');

            $table.bootstrapTable({
                exportDataType: 'all',
                printPageBuilder: function(table) {
                    return myCustomPrint(table, "List of Subject Loads");
                },
                queryParams: function(params) {
                    return {
                        school_year_id: $schoolYear.val()
                    };
                }
            });

            $schoolYear.on('change', function() {
                $table.bootstrapTable('refresh', {
                    url: '{{ route('teacher.subjects-teacher-load') }}?school_year_id=' + $(this)
                        .val()
                });
            });
        });
    </script>
@endsection
