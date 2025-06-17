@extends('layout.master')
@section('title', '| Student Profile')
@section('active-profile', 'active')
@section('app-title', 'Profile')
@section('content')
    <div class="row">
        <div class="col-lg-3 col-md-4 mb-4">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <img class="profile-user-img img-fluid img-circle"
                            src="{{ $data['avatar'] ?? asset('dist/img/avatar.png') }}" alt="User profile picture"
                            style="width: 150px; height: 150px;">
                    </div>
                    <h3 class="profile-username text-center">{{ $data['full_name'] }}</h3>
                    <p class="text-muted text-center">{{ Str::ucfirst(auth()->user()->role) }}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-9 col-md-8 mb-4">
            <div class="card card-primary card-outline shadow-sm">
                <div class="card-header">
                    <h3 class="card-title">Personal Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <label>First Name:</label>
                            <p class="form-control">{{ $data['first_name'] }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Middle Name:</label>
                            <p class="form-control">{{ $data['middle_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Last Name:</label>
                            <p class="form-control">{{ $data['last_name'] }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Suffix:</label>
                            <p class="form-control">{{ $data['extension_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Civil Status:</label>
                            <p class="form-control">{{ $data['civil_status'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Religion:</label>
                            <p class="form-control">{{ $data['religion'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Birthday:</label>
                            <p class="form-control">{{ $data['birthday'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <label>Contact:</label>
                            <p class="form-control">{{ $data['contact'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                    <hr>
                    <h4>Address Information</h4>
                    <div class="row">
                        <div class="col-lg-6 col-md-6">
                            <label>Region:</label>
                            <p class="form-control">{{ $data['region_name'] ?? 'REGION VIII (Eastern Visayas)' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Province:</label>
                            <p class="form-control">{{ $data['province_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Municipality:</label>
                            <p class="form-control">{{ $data['municipality_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Barangay:</label>
                            <p class="form-control">{{ $data['barangay_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <label>Zip Code:</label>
                            <p class="form-control">{{ $data['zip_code'] ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
