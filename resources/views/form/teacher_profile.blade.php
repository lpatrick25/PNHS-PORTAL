<div class="row">
    <div class="form-group col-md-12 col-lg-3">
        <label for="first_name">First Name: <span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" required>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="middle_name">Middle Name: <span style="color:red;"></span></label>
        <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name">
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="last_name">Last Name: <span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" required>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="extension_name">Extension Name: <span style="color:red;"></span></label>
        <input type="text" class="form-control" id="extension_name" name="extension_name"
            placeholder="Extension Name">
    </div>
    <div class="col-lg-12">
        <p class="text-left text-danger"><strong>ADDRESS</strong></p>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="province_code">Province: <span style="color:red;">*</span></label>
        <select class="form-control" id="province_code" name="province_code" required>
            <option selected="true" value="REQUIRED" disabled>-- Select Province --</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="municipality_code">Municipality: <span style="color:red;">*</span></label>
        <select class="form-control" id="municipality_code" name="municipality_code" required>
            <option selected="true" value="REQUIRED" disabled>-- Select Municipality --</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="barangay_code">Barangay: <span style="color:red;">*</span></label>
        <select class="form-control" id="barangay_code" name="barangay_code" required>
            <option selected="true" value="REQUIRED" disabled>-- Select Barangay --</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="zip_code">Zip Code: <span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="zip_code" name="zip_code" placeholder="Zip Code" required
            readonly>
    </div>
    <div class="col-lg-12">
        <p class="text-left text-danger"><strong>TEACHER INFORMATION</strong></p>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="religion">Religion: <span style="color:red;">*</span></label>
        <select id="religion" name="religion" class="form-control">
            <option selected="true" value="REQUIRED" disabled>-- Select Religion --</option>
            <option>Roman Catholic</option>
            <option>Seventh-Day Adventist</option>
            <option>Iglesia ni Cristo</option>
            <option>Jehovah Witnesses</option>
            <option>Pentecostal</option>
            <option>Church of Christ</option>
            <option>Christian</option>
            <option>Baptist</option>
            <option>Protestant</option>
            <option>Born Again</option>
            <option>Muslim</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="birthday">Birthday: <span style="color:red;">*</span></label>
        <input type="date" class="form-control" id="birthday" name="birthday" required>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="sex">Sex: <span style="color:red;">*</span></label>
        <select class="form-control" id="sex" name="sex">
            <option selected="true" value="REQUIRED" disabled>-- Select Sex --</option>
            <option>Male</option>
            <option>Female</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-3">
        <label for="civil_status">Civil Status: <span style="color:red;">*</span></label>
        <select class="form-control" id="civil_status" name="civil_status">
            <option selected="true" value="REQUIRED" disabled>-- Select Civil Status --</option>
            <option>Single</option>
            <option>Married</option>
            <option>Widowed</option>
            <option>Divorced</option>
        </select>
    </div>
    <div class="form-group col-md-12 col-lg-6">
        <label for="email">Email Address: <span style="color:red;">*</span></label>
        <input type="email" class="form-control" id="email" name="email" placeholder="Email Address"
            required>
    </div>
    <div class="form-group col-md-12 col-lg-6">
        <label for="contact">Contact Number: <span style="color:red;">*</span></label>
        <input type="text" class="form-control" id="contact" name="contact" data-mask="(+63) 999-999-9999"
            placeholder="(+63)" required>
    </div>
</div>
