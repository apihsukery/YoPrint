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
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:30:45
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                document_001.pdf
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    completed
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:32:12
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                image_002.jpg
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    processing
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:35:28
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                report_003.xlsx
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                    pending
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:38:05
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                data_004.csv
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    failed
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:40:33
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                presentation_005.pptx
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    completed
                                </span>
                            </td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                2025-11-05 10:42:18
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                archive_006.zip
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    processing
                                </span>
                            </td>
                        </tr>
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
                // Create FormData to send file
                const formData = new FormData();
                formData.append('file', selectedFile);

                // TODO: Replace with your actual upload endpoint
                // For now, just show a message
                alert(`Ready to upload: ${selectedFile.name}\n\nImplement your upload logic here.`);

                // Example upload code (uncomment and modify when ready):
                /*
                fetch('/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Upload successful:', data);
                    // Reset the form
                    selectedFile = null;
                    fileInput.value = '';
                    dropZoneText.textContent = 'Select file/Drag and drop (CSV only)';
                    uploadBtn.disabled = true;
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                });
                */
            }
        });
    </script>
</body>
</html>
