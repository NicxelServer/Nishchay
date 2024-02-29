[16:55] Abhijeet Bhosale
@extends('frontend_home.leftmenu')
 
<style>
    /* Custom CSS to adjust positioning */
    .main-content {
        margin-top: -30px; /* Adjust this value as needed */
    }
 
    #designationName{
        width: 200px; /* Adjust the width as needed */
    }
</style>
 
<div class="main-content">
    <section class="section">
        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mt-2">Nixcel Software Solutions Designation</h4>
                        </div>
                        <div class="col-12 text-right mt-n4">
                            <div class="buttons">
                                <!-- Button to show Add New Designation Modal -->
                                <button class="btn btn-primary" data-toggle="modal" data-target="#addDesignationModal">Add New Designation</button>
                            </div>
                        </div>

                        <!-- Table displaying designations -->
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="save-stage" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th>Sr.No</th>
                                            <th>Designation</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($designations as $key => $designation)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $designation->designation_name }}</td>
                                            <td>
                                                <!-- Edit action link with encrypted ID -->
                                                <button class="btn btn-warning toggle-edit-form"
                                                    data-designation-id="{{ $designation->tbl_designation_id }}"
                                                    data-encrypted-id="{{ $designation->encrypted_id }}">Edit</button>
                                                <!-- Delete action form with encrypted ID -->

                                                <a href="/admin/deletedesignation/{{ $designation->encrypted_id }}" class="btn btn-danger delete-designation" data-encrypted-id="{{ $designation->encrypted_id }}">Delete</a>

                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>


<!-- Add Designation Modal -->
<div class="modal fade" id="addDesignationModal" tabindex="-1" role="dialog" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/admin/storedesignation" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="designationName">Enter Designation Name</label>
                        <input type="text" class="form-control" id="designationName" name="designationName" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Designation Modal -->
@foreach($designations as $designation)
<div class="modal fade" id="editDesignationModal_{{ $designation->tbl_designation_id }}" tabindex="-1" role="dialog" aria-labelledby="editDesignationModalLabel_{{ $designation->tbl_designation_id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="/admin/editdesignation" method="POST">
                @csrf
                <input type="hidden" name="enc_id" value="{{ $designation->encrypted_id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="editDesignationModalLabel_{{ $designation->tbl_designation_id }}">Edit Designation</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editDesignationName_{{ $designation->tbl_designation_id }}">Enter Designation Name</label>
                        <input type="text" class="form-control" id="editDesignationName_{{ $designation->tbl_designation_id }}" name="designationName" value="{{ $designation->designation_name }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
    // Script for delete confirmation with SweetAlert
    document.querySelectorAll('.delete-designation').forEach(function (button) {
        button.addEventListener('click', function (event) {
            event.preventDefault();
            var encryptedId = this.dataset.encryptedId; // Retrieve encrypted_id from data attribute

            // Use SweetAlert to confirm the delete action
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If confirmed, redirect to the delete URL
                    window.location.href = "/admin/deletedesignation/" + encryptedId;
                }
            });
        });
    });

    // Script to toggle display of edit designation form
document.querySelectorAll('.toggle-edit-form').forEach(function (button) {
    button.addEventListener('click', function () {
        var designationId = this.dataset.designationId;
        $('#editDesignationModal_' + designationId).modal('show');
    });
});

</script>
 