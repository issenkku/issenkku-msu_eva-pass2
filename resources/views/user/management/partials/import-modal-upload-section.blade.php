{{-- ส่วนอัปโหลดไฟล์และดาวน์โหลด template --}}
<div class="space-y-6">
    <div
        class="file-drop-zone border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-purple-400 cursor-pointer"
        ondrop="handleDrop(event)"
        ondragover="handleDragOver(event)"
        ondragleave="handleDragLeave(event)"
        onclick="document.getElementById('fileInput').click()"
    >
        <div id="dropZoneContent">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="text-lg text-gray-600 mb-2">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
            <p class="text-sm text-gray-500">รองรับไฟล์ .xlsx, .xls, .csv ขนาดไม่เกิน 10MB</p>
        </div>

        <input
            type="file"
            id="fileInput"
            name="import_file"
            accept=".xlsx,.xls,.csv"
            class="hidden"
            onchange="handleFileSelect(event)"
            required
        >
    </div>

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

    <div id="uploadProgress" class="hidden">
        <div class="bg-gray-200 rounded-full h-2">
            <div class="progress-bar bg-purple-600 h-2 rounded-full" style="width: 0%"></div>
        </div>
        <p class="text-sm text-gray-600 mt-2">กำลังอัปโหลด... <span id="progressText">0%</span></p>
    </div>

    <div class="bg-yellow-50 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-medium text-yellow-900">ยังไม่มีไฟล์ Template?</h3>
                <p class="text-sm text-yellow-700 mt-1">ดาวน์โหลด Template Excel เพื่อใช้เป็นแม่แบบในการนำเข้าข้อมูล</p>
            </div>

            <a href="{{ route('users.import.template') }}" class="bg-yellow-100 text-yellow-800 px-4 py-2 rounded hover:bg-yellow-200 transition-colors">
                ดาวน์โหลด Template
            </a>
        </div>
    </div>
</div>
