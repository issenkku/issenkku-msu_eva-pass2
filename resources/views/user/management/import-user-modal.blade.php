<!-- Import User Modal - resources/views/user/management/import-user-modal.blade.php -->

<!-- Modal Background -->
<div id="importUserModal" class="fixed z-[9999] inset-0 bg-black bg-opacity-50 hidden items-center justify-center overflow-y-auto">
    <!-- Modal Box -->
    <div class="bg-white rounded-xl w-full max-w-4xl mx-4 p-6 relative max-h-[90vh] overflow-y-auto shadow-2xl">
        <!-- Header -->
        <div class="flex justify-between items-center border-b pb-4 mb-6">
            <div>
                <h2 class="text-xl font-semibold text-purple-700">นำเข้าข้อมูลเจ้าหน้าที่</h2>
                <p class="text-sm text-gray-600 mt-1">อัปโหลดไฟล์ Excel หรือ CSV เพื่อนำเข้าข้อมูลเจ้าหน้าที่จำนวนมาก</p>
            </div>
            <button onclick="closeImportModal()" class="text-gray-500 hover:text-red-500 text-2xl leading-none">&times;</button>
        </div>

        <!-- Form -->
        <form id="importForm" action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">

            <!-- File Upload Section -->
            <div class="space-y-6">
                <!-- File Drop Zone -->
                <div class="file-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 cursor-pointer"
                     ondrop="handleDrop(event)" 
                     ondragover="handleDragOver(event)" 
                     ondragleave="handleDragLeave(event)"
                     onclick="document.getElementById('fileInput').click()">
                    
                    <div id="dropZoneContent">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="text-lg text-gray-600 mb-2">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
                        <p class="text-sm text-gray-500">รองรับไฟล์ .xlsx, .xls, .csv (ขนาดไม่เกิน 10MB)</p>
                    </div>

                    <input type="file" 
                           id="fileInput" 
                           name="import_file" 
                           accept=".xlsx,.xls,.csv" 
                           class="hidden" 
                           onchange="handleFileSelect(event)"
                           required>
                </div>

                <!-- Selected File Display -->
                <div id="selectedFileInfo" class="hidden bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="h-8 w-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div>
                                <p class="font-medium text-gray-900" id="fileName"></p>
                                <p class="text-sm text-gray-500" id="fileSize"></p>
                            </div>
                        </div>
                        <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Upload Progress -->
                <div id="uploadProgress" class="hidden">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div class="progress-bar bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">กำลังอัปโหลด... <span id="progressText">0%</span></p>
                </div>

                <!-- Template Download -->
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="font-medium text-yellow-900">ไม่มีไฟล์ Template?</h3>
                            <p class="text-sm text-yellow-700 mt-1">ดาวน์โหลด Template Excel เพื่อใช้เป็นแม่แบบในการนำเข้าข้อมูล</p>
                        </div>
                        <a href="{{ route('users.import.template') }}" class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded hover:bg-yellow-200 transition-colors">
                            ดาวน์โหลด Template
                        </a>
                    </div>
                </div>

                <!-- Validation Errors Display -->
                @if($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">เกิดข้อผิดพลาดในการนำเข้าข้อมูล</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Import Errors Display -->
                @if(session('import_errors'))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 max-h-40 overflow-y-auto">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">รายการข้อผิดพลาด</h3>
                            <div class="mt-2 text-sm text-red-700">
                                @foreach(session('import_errors') as $error)
                                    <div class="mb-2 p-2 bg-red-100 rounded">
                                        <strong>แถวที่ {{ $error['row'] }}:</strong> {{ $error['error'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-4 mt-8 pt-6 border-t">
                <x-button 
                    type= defualt 
                    text="ย้อนกลับ" 
                    onclick="closeImportModal()" 
                    icon="fas fa-arrow-left" />
                <button type="submit" id="submitBtn" class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                    <span id="submitText">นำเข้าข้อมูล</span>
                    <svg id="loadingIcon" class="hidden animate-spin -mr-1 ml-3 h-5 w-5 text-white inline" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success/Error Messages Display -->
@if(session('success'))
<div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
    <div class="flex items-center space-x-3">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <span>{{ session('success') }}</span>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('warning'))
<div id="warningMessage" class="fixed top-4 right-4 bg-yellow-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
    <div class="flex items-center space-x-3">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span>{{ session('warning') }}</span>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
@endif

@if(session('error'))
<div id="errorMessage" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300 max-w-md">
    <div class="flex items-start space-x-3">
        <svg class="h-6 w-6 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <div class="font-medium">เกิดข้อผิดพลาด</div>
            <div class="text-sm mt-1">{{ session('error') }}</div>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
</div>
@endif

<style>
.file-drop-zone {
    transition: all 0.3s ease;
}
.file-drop-zone.drag-over {
    border-color: #7c3aed;
    background-color: #f3f4f6;
    transform: scale(1.02);
}
.progress-bar {
    transition: width 0.3s ease;
}
</style>

<script>
let selectedFile = null;

function openImportModal(button) {
    const modal = document.getElementById('importUserModal');
    const form = document.getElementById('importForm');

    // Reset the form
    form.reset();
    resetFileUpload();

    // Use route from data attribute
    const action = button.getAttribute('data-action');
    if (action) {
        form.action = action;
    }

    // Set form method to POST
    document.getElementById('formMethod').value = "POST";

    // Show the modal
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeImportModal() {
    const modal = document.getElementById('importUserModal');
    const form = document.getElementById('importForm');
    
    // Reset form and file upload
    form.reset();
    resetFileUpload();
    resetFormState();
    
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function handleDragOver(event) {
    event.preventDefault();
    event.currentTarget.classList.add('drag-over');
}

function handleDragLeave(event) {
    event.preventDefault();
    event.currentTarget.classList.remove('drag-over');
}

function handleDrop(event) {
    event.preventDefault();
    const dropZone = event.currentTarget;
    dropZone.classList.remove('drag-over');
    
    const files = event.dataTransfer.files;
    if (files.length > 0) {
        handleFile(files[0]);
    }
}

function handleFileSelect(event) {
    const file = event.target.files[0];
    if (file) {
        handleFile(file);
    }
}

function handleFile(file) {
    // Validate file type
    const allowedTypes = ['.xlsx', '.xls', '.csv'];
    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
    
    if (!allowedTypes.includes(fileExtension)) {
        alert('กรุณาเลือกไฟล์ .xlsx, .xls หรือ .csv เท่านั้น');
        return;
    }

    // Validate file size (10MB limit)
    if (file.size > 10 * 1024 * 1024) {
        alert('ขนาดไฟล์ใหญ่เกินไป กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 10MB');
        return;
    }

    selectedFile = file;
    displaySelectedFile(file);
    enableSubmitButton();
}

function displaySelectedFile(file) {
    const fileInfo = document.getElementById('selectedFileInfo');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    
    fileName.textContent = file.name;
    fileSize.textContent = formatFileSize(file.size);
    
    fileInfo.classList.remove('hidden');
}

function removeFile() {
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('selectedFileInfo').classList.add('hidden');
    disableSubmitButton();
}

function resetFileUpload() {
    selectedFile = null;
    document.getElementById('fileInput').value = '';
    document.getElementById('selectedFileInfo').classList.add('hidden');
    document.getElementById('uploadProgress').classList.add('hidden');
    disableSubmitButton();
}

function resetFormState() {
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingIcon = document.getElementById('loadingIcon');
    const progressDiv = document.getElementById('uploadProgress');
    
    submitBtn.disabled = false;
    submitText.textContent = 'นำเข้าข้อมูล';
    loadingIcon.classList.add('hidden');
    progressDiv.classList.add('hidden');
}

function enableSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
}

function disableSubmitButton() {
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function simulateUploadProgress() {
    const progressBar = document.querySelector('.progress-bar');
    const progressText = document.getElementById('progressText');
    let progress = 0;
    
    const interval = setInterval(() => {
        progress += Math.random() * 15;
        if (progress > 100) progress = 100;
        
        progressBar.style.width = progress + '%';
        progressText.textContent = Math.round(progress) + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
        }
    }, 200);
}

// Handle form submission (Traditional form submission for Blade)
document.getElementById('importForm').addEventListener('submit', function(e) {
    if (!selectedFile) {
        e.preventDefault();
        alert('กรุณาเลือกไฟล์ที่ต้องการนำเข้า');
        return;
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const loadingIcon = document.getElementById('loadingIcon');
    const progressDiv = document.getElementById('uploadProgress');
    
    submitBtn.disabled = true;
    submitText.textContent = 'กำลังนำเข้า...';
    loadingIcon.classList.remove('hidden');
    progressDiv.classList.remove('hidden');
    
    // Start progress simulation
    simulateUploadProgress();
    
    // Let the form submit normally to Laravel
});

// Auto-hide success/error messages after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
    messages.forEach(function(message) {
        setTimeout(function() {
            if (message.parentElement) {
                message.style.transform = 'translateX(100%)';
                setTimeout(function() {
                    if (message.parentElement) {
                        message.remove();
                    }
                }, 300);
            }
        }, 5000);
    });
});
</script>