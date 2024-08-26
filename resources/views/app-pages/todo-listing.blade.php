@extends('app-layout/app-layout')

@section('content')

<div class="container mt-5">
    <!-- Page Title -->
    <div class="row mb-4">
        <div class="col-md-12 border-bottom">
            <h1 class="text-primary mb-3">PHP - Simple To Do List App</h1>
        </div>
    </div>

    <!-- Notifications Container -->
    <div id="notifications" class="position-fixed top-0 end-0 p-3" style="z-index: 1050;"></div>

    <!-- Form to Add New Tasks -->
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-center">
            <form id="task-form" action="{{ route('tasks.store') }}" method="POST" class="d-flex">
                @csrf
                <input type="text" name="task" id="task-input" class="form-control col-md-8 me-2" placeholder="Enter your task" required>
                <button type="submit" class="col-md-4 btn btn-primary">Add Task</button>
            </form>
            <!-- Filter Button -->
                <div class="col-md-4 d-flex justify-content-center">
                    <button id="filter-toggle" class="btn btn-secondary">Show All</button>
                </div>
        </div>
    </div>

    

    <!-- Listing of Tasks -->
    <div class="row justify-content-center">
        <div class="col-md-10">
            <table class="table table-hover" id="task-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Task</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tasks as $index => $task)
                        <tr data-id="{{ $task->id }}" class="{{ $task->status === 'Done' ? 'd-none done-task' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $task->name }}</td>
                            <td>{{ $task->status }}</td>
                            <td>
                                @if($task->status == 'Pending')
                                    <button class="btn btn-success btn-sm mark-done">
                                        <i class="bi bi-check2-square"></i>
                                    </button>
                                @endif
                                <button class="btn btn-danger btn-sm delete-task"><i class="bi bi-x"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Delete Confirmation -->
<div class="modal fade" id="confirm-delete-modal" tabindex="-1" aria-labelledby="confirm-delete-modal-label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirm-delete-modal-label">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this task?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirm-delete-btn" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        let taskIdToDelete = null;
        let filterState = 'pending'; // Initial filter state set to 'pending'

        // Function to create a notification popup
        function showNotification(message, type = 'success') {
            const color = type === 'success' ? 'bg-success' : 'bg-danger';
            const notification = `
                <div class="toast align-items-center text-white ${color} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            $('#notifications').append(notification);
            const toast = new bootstrap.Toast($('#notifications .toast').last()[0]);
            toast.show();
        }

        // Handle form submission
        $('#task-form').submit(function(event) {
            event.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    const newRow = `
                        <tr data-id="${response.task.id}" class="${response.task.status === 'Done' ? 'd-none done-task' : ''}">
                            <td>${$('#task-table tbody tr').length + 1}</td>
                            <td>${response.task.name}</td>
                            <td>${response.task.status}</td>
                            <td>
                                @if($task->status == 'Pending')
                                    <button class="btn btn-success btn-sm mark-done">
                                        <i class="bi bi-check2-square"></i>
                                    </button>
                                @endif
                                <button class="btn btn-danger btn-sm delete-task"><i class="bi bi-x"></i></button>
                            </td>
                        </tr>
                    `;
                    $('#task-table tbody').append(newRow);
                    $('#task-input').val(''); // Clear input field
                    showNotification('Task added successfully!');
                },
                error: function(xhr) {
                    showNotification(xhr.responseJSON.message, 'error');
                }
            });
        });

        // Handle mark as done button
        $(document).on('click', '.mark-done', function() {
            const $row = $(this).closest('tr');
            const taskId = $row.data('id');

            $.ajax({
                url: `/tasks/${taskId}/done`,
                method: 'PATCH',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    $row.find('td').eq(2).text('Done'); // Update status
                    $row.addClass('d-none done-task'); // Hide the row
                    showNotification('Task marked as done!');
                }
            });
        });

        // Handle delete button
        $(document).on('click', '.delete-task', function() {
            const $row = $(this).closest('tr');
            taskIdToDelete = $row.data('id');
            const modal = new bootstrap.Modal(document.getElementById('confirm-delete-modal'));
            modal.show();
        });

        // Confirm delete
        $('#confirm-delete-btn').click(function() {
            if (taskIdToDelete) {
                $.ajax({
                    url: `/tasks/${taskIdToDelete}`,
                    method: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $(`tr[data-id="${taskIdToDelete}"]`).remove(); // Remove the row from the table
                        $('#task-table tbody tr').each(function(index) {
                            $(this).find('td').eq(0).text(index + 1); // Update row numbers
                        });
                        showNotification('Task deleted successfully!');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('confirm-delete-modal'));
                        modal.hide(); // Hide the modal
                        $('body').removeClass('modal-open'); // Remove the modal-open class
                        $('.modal-backdrop').remove(); // Remove any modal-backdrop
                    },
                    error: function(xhr) {
                        showNotification('An error occurred while deleting the task.', 'error');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('confirm-delete-modal'));
                        modal.hide(); // Hide the modal
                        $('body').removeClass('modal-open'); // Remove the modal-open class
                        $('.modal-backdrop').remove(); // Remove any modal-backdrop
                    }
                });
            }
        });

        // Toggle filter button
        function applyFilter() {
            if (filterState === 'pending') {
                $('#task-table tbody tr').each(function() {
                    const status = $(this).find('td').eq(2).text().trim();
                    if (status === 'Done') {
                        $(this).addClass('d-none'); // Hide completed tasks
                    } else {
                        $(this).removeClass('d-none'); // Show pending tasks
                    }
                });
                $('#filter-toggle').text('Show All');
            } else {
                $('#task-table tbody tr').removeClass('d-none'); // Show all tasks
                $('#filter-toggle').text('Show Pending');
            }
        }

        // Apply initial filter state to show only pending tasks
        applyFilter();

        // Handle button click
        $('#filter-toggle').click(function() {
            filterState = filterState === 'pending' ? 'all' : 'pending';
            applyFilter();
        });
    });



</script>

@endsection
