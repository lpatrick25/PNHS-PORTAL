@extends('layout.master')

@section('title')
    | Attendances
@endsection

@section('active-attendances')
    active
@endsection

@section('app-title')
    Attendances
@endsection

@section('content')
    <div id="show-msg"></div>
    <div id="attendance-records" class="row">
        <div class="col-12 text-center">
            <div class="overlay">
                <i class="fas fa-2x fa-sync-alt fa-spin"></i>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $.ajax({
                method: 'GET',
                url: '{{ route('student.getStudentAttendance') }}',
                dataType: 'JSON',
                cache: false,
                success: function(response) {
                    const recordsContainer = $('#attendance-records');
                    recordsContainer.empty(); // Clear loading state

                    if (response.valid) {
                        // Define background color classes for cards
                        const bgClasses = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning'];
                        let colorIndex = 0;

                        // Build cards for each grade level and section
                        response.data.forEach((record) => {
                            const gradeLevel = record.grade_level;
                            const section = record.section;
                            const subjects = record.subjects;

                            let card = `
                                <div class="col-12 mb-4">
                                    <div class="card card-outline card-primary shadow-sm">
                                        <div class="card-header">
                                            <h3 class="card-title">
                                                <span class="badge badge-danger">Grade ${gradeLevel}</span>
                                                <span class="badge badge-success">Section ${section}</span>
                                            </h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                            `;

                            // Add subject boxes
                            subjects.forEach((subject) => {
                                const bgClass = bgClasses[colorIndex];
                                colorIndex = (colorIndex + 1) % bgClasses.length;

                                // Determine badge color based on attendance percentage
                                const percentage = subject.attendance_percentage;
                                let badgeClass = 'badge-success';
                                if (percentage < 75) badgeClass = 'badge-danger';
                                else if (percentage < 90) badgeClass = 'badge-warning';

                                card += `
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <div class="small-box ${bgClass} shadow-sm">
                                            <div class="inner">
                                                <h3>${subject.attendance_summary}<sup class="badge ${badgeClass} ml-2">${percentage}%</sup></h3>
                                                <p class="font-weight-bold">${subject.subject_code}</p>
                                                <p class="text-muted">${subject.subject_name}</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-book"></i>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            card += `
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            recordsContainer.append(card);
                        });
                    } else {
                        recordsContainer.html(`
                            <div class="col-12">
                                <div class="alert alert-warning alert-dismissible fade show">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    ${response.msg}
                                </div>
                            </div>
                        `);
                    }
                },
                error: function(jqXHR) {
                    const recordsContainer = $('#attendance-records');
                    recordsContainer.empty();

                    let errorMsg = 'Something went wrong! Please try again later.';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.msg) {
                        errorMsg = jqXHR.responseJSON.msg;
                    }

                    recordsContainer.html(`
                        <div class="col-12">
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                ${errorMsg}
                            </div>
                        </div>
                    `);
                }
            });
        });
    </script>
@endsection
