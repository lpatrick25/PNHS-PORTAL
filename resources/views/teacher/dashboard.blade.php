@extends('layout.master')
@section('title')
    | Dashboard
@endsection
@section('active-dashboard')
    active
@endsection
@section('app-title')
    Dashboard
@endsection
@section('content')
    <div class="row">
        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $dashboard['student_count'] ?? 0 }}<sup style="font-size: 20px"></sup></h3>
                    <p>No. Students</p>
                </div>
                <div class="icon">
                    <i class="ion ion-person-stalker"></i>
                </div>
                <a href="{{ route('teacher.advisory') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $dashboard['subject_count'] ?? 0 }}<sup style="font-size: 20px"></sup></h3>
                    <p>No. Subjects Handled</p>
                </div>
                <div class="icon">
                    <i class="ion ion-clipboard"></i>
                </div>
                <a href="{{ route('teacher.subjects') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $dashboard['advisory_section'] ?? 'N/A' }}<sup style="font-size: 20px"></sup></h3>
                    <p>Grade Level: {{ $dashboard['advisory_grade_level'] ?? 'N/A' }}</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <a href="{{ route('teacher.advisory') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $dashboard['attendance_count'] ?? 0 }}<sup style="font-size: 20px"></sup></h3>
                    <p>No. Attendance Records</p>
                </div>
                <div class="icon">
                    <i class="ion ion-calendar"></i>
                </div>
                <a href="{{ route('teacher.attendance') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $dashboard['class_record_count'] ?? 0 }}<sup style="font-size: 20px"></sup></h3>
                    <p>No. Class Records</p>
                </div>
                <div class="icon">
                    <i class="ion ion-document-text"></i>
                </div>
                <a href="{{ route('teacher.class-records') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $dashboard['report_card_count'] ?? 0 }}<sup style="font-size: 20px"></sup></h3>
                    <p>No. Report Cards</p>
                </div>
                <div class="icon">
                    <i class="ion ion-card"></i>
                </div>
                <a href="{{ route('teacher.report-card') }}" class="small-box-footer">More info <i
                        class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
@endsection
