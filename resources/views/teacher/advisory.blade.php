@extends('layout.master')
@section('title')
    | Advisory
@endsection
@section('active-advisory')
    active
@endsection
@section('app-title')
    Advisory
@endsection
@section('content')
    <div id="toolbar">
        <label for="school_year">School Year:</label>
        <select name="school_year" id="school_year" class="form-control">
            @foreach($schoolYears as $year)
                <option value="{{ $year->id }}" {{ $year->current ? 'selected' : '' }}>{{ $year->school_year }}</option>
            @endforeach
        </select>
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false"
        data-show-export="false" data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
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
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    <script type="text/javascript">
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
                    url: '{{ route("teacher.advisory-students") }}?school_year_id=' + $(this).val()
                });
            });
        });
    </script>
@endsection
