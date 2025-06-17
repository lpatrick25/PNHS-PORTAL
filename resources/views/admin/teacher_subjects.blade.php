@extends('layout.master')
@section('title')
    | Teacher Subjects
@endsection
@section('active-teacher-subject-list')
    active
@endsection
@section('app-title')
    Teacher Subjects
@endsection
@section('content')
    <div id="toolbar">
    </div>
    <table id="table" data-show-refresh="true" data-auto-refresh="true" data-pagination="true" data-show-columns="false"
        data-cookie="false" data-cookie-id-table="table" data-search="true" data-click-to-select="false"
        data-show-copy-rows="false" data-page-number="1" data-show-toggle="false" data-show-export="false"
        data-filter-control="true" data-show-search-clear-button="false" data-key-events="false"
        data-mobile-responsive="true" data-check-on-init="true" data-show-print="false" data-sticky-header="true"
        data-url="getTeacherList" data-toolbar="#toolbar">
        <thead>
            <tr>
                <th data-field="count">#</th>
                <th data-field="image">Image</th>
                <th data-field="teacher_name">Teacher Name</th>
                <th data-field="contact">CONTACT</th>
                <th data-field="email">EMAIL</th>
                <th data-field="role">ROLE</th>
                <th data-field="action">ACTION</th>
            </tr>
        </thead>
    </table>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            var $table = $('#table');
            $table.bootstrapTable({
                exportDataType: $(this).val(),
                printPageBuilder: function printPageBuilder(table) {
                    return myCustomPrint(table, "List of Teacher Subjects");
                },
            });
        });
    </script>
@endsection
