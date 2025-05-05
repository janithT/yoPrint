@extends('layouts.app')

@section('title', 'File Upload')

@section('content')
<div class="container">
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    <!-- File Upload Section -->
    <div class="card mb-4">
        <div class="card-header">Upload File</div>
        <div class="card-body">
            <form id="upload-form" action="{{ route('file.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="file" class="form-label">Choose a file or drag and drop</label>
                    <div class="border border-dashed p-4 text-center rounded bg-light" 
                         ondrop="handleDrop(event)" 
                         ondragover="handleDragOver(event)">
                        <input type="file" name="file" id="file" class="form-control" onchange="showFileDetails()" hidden>
                        <div id="drop-zone-text">Drag and drop your file here or click to select</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">Upload</button>
            </form>
        </div>
    </div>

    <!-- Table for Uploaded Files -->
    <div class="card">
        <div class="card-header">Uploaded Files</div>
        <div class="card-body p-0">
            <table class="table table-bordered m-0">
                <thead class="table-light">
                    <tr>
                        <th>Time</th>
                        <th>Filename</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($files as $index => $file)
                        <tr>
                            <td>{{ $file->created_at->format('Y-m-d H:i') }}</td>
                            <td>{{ $file->filename  }}</td>
                            <td>{{ $file->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No files uploaded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Optional JS for drag-and-drop -->
<script>
    const dropZone = document.querySelector('.border');
    const fileInput = document.getElementById('file');
    const dropText = document.getElementById('drop-zone-text');

    dropZone.addEventListener('click', () => fileInput.click());

    function handleDragOver(e) {
        e.preventDefault();
        dropZone.classList.add('border-primary');
        dropText.innerText = 'Drop it here!';
    }

    function handleDrop(e) {
        e.preventDefault();
        dropZone.classList.remove('border-primary');
        dropText.innerText = 'File selected!';
        const files = e.dataTransfer.files;
        fileInput.files = files;
        showFileDetails();
    }

    function showFileDetails() {
        const file = fileInput.files[0];
        if (file) {
            dropText.innerText = `Selected: ${file.name} (${Math.round(file.size / 1024)} KB)`;
        }
    }
</script>
@endsection
