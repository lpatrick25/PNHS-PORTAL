@extends('layout.master')
@section('title')
    | Class Records
@endsection
@section('active-class-records')
    active
@endsection
@section('app-title')
    Class Records
@endsection
@section('content')
    <div id="show-msg"></div>
    <div id="toolbar">
        <div class="form-inline">
            <label for="school_year" class="mr-2">School Year:</label>
            <select name="school_year" id="school_year" class="form-control chosen-select" style="width: 200px;">
                @foreach ($schoolYears as $year)
                    <option value="{{ $year->id }}" {{ $year->current ? 'selected' : '' }}>{{ $year->school_year }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <table id="table"
           data-show-refresh="true"
           data-auto-refresh="true"
           data-pagination="true"
           data-show-columns="false"
           data-cookie="false"
           data-cookie-id-table="class_records_table"
           data-search="true"
           data-click-to-select="false"
           data-show-copy-rows="false"
           data-page-number="1"
           data-show-toggle="false"
           data-show-export="false"
           data-filter-control="true"
           data-show-search-clear-button="false"
           data-key-events="false"
           data-mobile-responsive="true"
           data-check-on-init="true"
           data-show-print="false"
           data-sticky-header="true"
           data-url="{{ route('teacher.subjects-teacher-load') }}?teacher_id={{ $teacher_id }}&school_year_id={{ $currentSchoolYear->id }}"
           data-toolbar="#toolbar"
           data-toolbar-align="left">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="subject_name">Subject</th>
                <th data-field="grade_level">Grade Level</th>
                <th data-field="section">Section</th>
                <th data-field="school_year">School Year</th>
                <th data-field="action">Action</th>
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    <script type="text/javascript">
        function view(subject_load_id) {
            window.location.href = '{{ route('viewClassRecord', '') }}/' + subject_load_id;
        }

        $(document).ready(function() {
            $('.chosen-select').chosen({
                width: '100%',
                placeholder_text_single: '-- Select Option --'
            });

            $('#table').bootstrapTable({
                exportDataType: 'all',
                printPageBuilder: function(table) {
                    return myCustomPrint(table, "List of Class Records");
                },
                queryParams: function(params) {
                    return {
                        teacher_id: '{{ $teacher_id }}',
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
                    url: '{{ route('teacherSubjectLoads.index') }}?teacher_id={{ $teacher_id }}&school_year_id=' + $(this).val()
                });
            });
        });
    </script>
@endsection
