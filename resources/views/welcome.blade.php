<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4">
    <div class="max-w-6xl mx-auto">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <!-- File Upload Section -->
            <div class="p-6 border-b border-gray-200">
                <div id="dropZone" class="border-2 border-gray-900 rounded-lg p-6 flex items-center justify-between hover:bg-gray-50 transition-colors cursor-pointer">
                    <div class="flex items-center space-x-2">
                        <span id="dropZoneText" class="text-gray-700 text-base">Select file/Drag and drop (CSV only)</span>
                    </div>
                    <button type="button" id="uploadBtn" class="px-6 py-2 bg-white border-2 border-gray-900 text-gray-900 font-medium rounded hover:bg-gray-900 hover:text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                        Upload File
                    </button>
                </div>
                <input type="file" id="fileInput" accept=".csv" class="hidden" />
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Time
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                File Name
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="fileTableBody">
                        @forelse($files as $file)
                        <tr class="hover:bg-gray-50" data-file-id="{{ $file->id }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="time-display" data-timestamp="{{ $file->created_at->timestamp }}">
                                    {{ $file->created_at->format('Y-m-d g:ia') }} (<span class="relative-time">{{ $file->created_at->diffForHumans() }}</span>)
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $file->original_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-gray-100 text-gray-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                    ];
                                @endphp
                                <span class="status-badge px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$file->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $file->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No files uploaded yet
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const dropZoneText = document.getElementById('dropZoneText');
        let selectedFile = null;

        // Disable upload button initially
        uploadBtn.disabled = true;

        // Click on drop zone to select file
        dropZone.addEventListener('click', (e) => {
            // Don't trigger if clicking the upload button
            if (e.target.id !== 'uploadBtn') {
                fileInput.click();
            }
        });

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight drop zone when dragging over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('bg-gray-100', 'border-gray-700');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('bg-gray-100', 'border-gray-700');
            });
        });

        // Handle dropped files
        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                // Check if it's a CSV file
                if (file.name.endsWith('.csv') || file.type === 'text/csv') {
                    handleFile(file);
                } else {
                    alert('Please select a CSV file only.');
                }
            }
        });

        // Handle file selection from input
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        // Handle selected file
        function handleFile(file) {
            selectedFile = file;
            dropZoneText.textContent = `Selected: ${file.name}`;
            uploadBtn.disabled = false;
        }

        // Upload button click handler
        uploadBtn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent triggering dropZone click

            if (selectedFile) {
                // Disable button during upload
                uploadBtn.disabled = true;
                uploadBtn.textContent = 'Uploading...';

                // Create FormData to send file
                const formData = new FormData();
                formData.append('file', selectedFile);

                fetch('/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reset the form
                        selectedFile = null;
                        fileInput.value = '';
                        dropZoneText.textContent = 'Select file/Drag and drop (CSV only)';
                        uploadBtn.textContent = 'Upload File';

                        // Reload page to show new file
                        window.location.reload();
                    } else {
                        alert('Upload failed: ' + data.message);
                        uploadBtn.disabled = false;
                        uploadBtn.textContent = 'Upload File';
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                    uploadBtn.disabled = false;
                    uploadBtn.textContent = 'Upload File';
                });
            }
        });

        // Real-time relative time update
        function getRelativeTime(timestamp) {
            const now = Math.floor(Date.now() / 1000); // Current time in seconds
            const diff = now - timestamp; // Difference in seconds

            const minute = 60;
            const hour = minute * 60;
            const day = hour * 24;
            const week = day * 7;
            const month = day * 30;
            const year = day * 365;

            if (diff < minute) {
                return diff <= 1 ? '1 second ago' : `${diff} seconds ago`;
            } else if (diff < hour) {
                const minutes = Math.floor(diff / minute);
                return minutes === 1 ? '1 minute ago' : `${minutes} minutes ago`;
            } else if (diff < day) {
                const hours = Math.floor(diff / hour);
                return hours === 1 ? '1 hour ago' : `${hours} hours ago`;
            } else if (diff < week) {
                const days = Math.floor(diff / day);
                return days === 1 ? '1 day ago' : `${days} days ago`;
            } else if (diff < month) {
                const weeks = Math.floor(diff / week);
                return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
            } else if (diff < year) {
                const months = Math.floor(diff / month);
                return months === 1 ? '1 month ago' : `${months} months ago`;
            } else {
                const years = Math.floor(diff / year);
                return years === 1 ? '1 year ago' : `${years} years ago`;
            }
        }

        function updateRelativeTimes() {
            document.querySelectorAll('.time-display').forEach(element => {
                const timestamp = parseInt(element.getAttribute('data-timestamp'));
                const relativeTimeElement = element.querySelector('.relative-time');
                if (relativeTimeElement) {
                    relativeTimeElement.textContent = getRelativeTime(timestamp);
                }
            });
        }

        // Update relative times immediately on page load
        updateRelativeTimes();

        // Update relative times every 60 seconds
        setInterval(updateRelativeTimes, 60000);

        // Poll for status updates
        const statusPollers = new Map();

        function pollFileStatus(fileId, row) {
            // Check if already polling
            if (statusPollers.has(fileId)) {
                return;
            }

            const pollInterval = setInterval(() => {
                fetch(`/upload/status/${fileId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Find status badge in the row
                        const statusBadge = row.querySelector('.status-badge');
                        if (statusBadge && statusBadge.textContent.trim() !== data.status) {
                            // Update status badge
                            statusBadge.textContent = data.status;

                            // Update badge color classes
                            statusBadge.classList.remove('bg-gray-100', 'text-gray-800', 'bg-blue-100', 'text-blue-800', 'bg-red-100', 'text-red-800', 'bg-green-100', 'text-green-800');

                            const statusColors = {
                                'pending': ['bg-gray-100', 'text-gray-800'],
                                'processing': ['bg-blue-100', 'text-blue-800'],
                                'failed': ['bg-red-100', 'text-red-800'],
                                'completed': ['bg-green-100', 'text-green-800']
                            };

                            if (statusColors[data.status]) {
                                statusBadge.classList.add(...statusColors[data.status]);
                            }
                        }

                        // Stop polling if completed or failed
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(pollInterval);
                            statusPollers.delete(fileId);
                        }
                    })
                    .catch(error => {
                        console.error('Status poll error:', error);
                    });
            }, 3000); // Poll every 3 seconds

            statusPollers.set(fileId, pollInterval);
        }

        // Start polling for files that are pending or processing
        document.querySelectorAll('tbody tr').forEach(row => {
            const statusBadge = row.querySelector('.status-badge');
            if (statusBadge) {
                const status = statusBadge.textContent.trim();
                const fileId = row.getAttribute('data-file-id');

                if (fileId && (status === 'pending' || status === 'processing')) {
                    pollFileStatus(fileId, row);
                }
            }
        });
    </script>
</body>
</html>
