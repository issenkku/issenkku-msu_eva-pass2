{{-- ไฟล์มุมมอง: resources/views\criteria_config\index.blade.php --}}
<!-- resources/views/criteria/index.blade.php -->
@extends('layouts.app')

@section('content')
    {{-- บล็อกเนื้อหา --}}
    <div class="py-12">
        {{-- บล็อกเนื้อหา --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">
                    กำหนดเกณฑ์การประเมิน
                </h2>
                <x-button 
                    type="primary" 
                    text="เพิ่มเกณฑ์" 
                    href="{{ route('criteria_config.create') }}"
                    icon="fas fa-plus" />
            </div>

            <hr class="mb-8">

            <!-- Criteria Grid (fetch from controller) -->
            <div id="criteria-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Loading spinner -->
                <div id="criteria-loading" class="col-span-3 flex justify-center py-10">
                    <span class="text-gray-500">Loading...</span>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Unified showAlert function, globally available
        // Modal-based alert (replaces browser alert)
        function showAlert(message, type = 'success') {
            let modal = document.getElementById('custom-alert-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'custom-alert-modal';
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.innerHTML = `
                    <div id="custom-alert-box" class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in">
                        <div class="flex justify-center mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full ${type === 'error' ? 'bg-red-100' : 'bg-green-100'}">
                                ${type === 'error' ? '<svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>' : '<svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>'}
                            </span>
                        </div>
                        <div class="text-lg font-semibold mb-2 ${type === 'error' ? 'text-red-600' : 'text-green-600'}">${type === 'error' ? 'เกิดข้อผิดพลาด' : 'สำเร็จ'}</div>
                        <div class="mb-4 text-gray-700">${message}</div>
                        <button id="custom-alert-ok" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ตกลง</button>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                // update content if already exists
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.querySelector('#custom-alert-box').className = `bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in`;
                modal.querySelector('#custom-alert-box').innerHTML = `
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full ${type === 'error' ? 'bg-red-100' : 'bg-green-100'}">
                            ${type === 'error' ? '<svg class=\"w-7 h-7 text-red-500\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6 18L18 6M6 6l12 12\"/></svg>' : '<svg class=\"w-7 h-7 text-green-500\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M5 13l4 4L19 7\"/></svg>'}
                        </span>
                    </div>
                    <div class="text-lg font-semibold mb-2 ${type === 'error' ? 'text-red-600' : 'text-green-600'}">${type === 'error' ? 'เกิดข้อผิดพลาด' : 'สำเร็จ'}</div>
                    <div class="mb-4 text-gray-700">${message}</div>
                    <button id="custom-alert-ok" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ตกลง</button>
                `;
                modal.style.display = '';
            }
            // Close on OK
            modal.querySelector('#custom-alert-ok').onclick = function() {
                modal.style.display = 'none';
            };
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Show success alert if redirected with ?success=1
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('success')) {
                showAlert('อัปเดตข้อมูลสำเร็จ', 'success');
            }
            fetchCriteriaVersions();
        });

        function fetchCriteriaVersions() {
            fetch("{{ route('report-structure.index') }}", {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                renderCriteriaCards(data.data);
            })
            .catch(error => {
                document.getElementById('criteria-grid').innerHTML = '<div class="col-span-3 text-center text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</div>';
            });
        }

        function renderCriteriaCards(criteriaVersions) {
            const grid = document.getElementById('criteria-grid');
            grid.innerHTML = '';
            if (!criteriaVersions || criteriaVersions.length === 0) {
                grid.innerHTML = '<div class="col-span-3 text-center text-gray-500">ไม่พบข้อมูลเกณฑ์</div>';
                return;
            }
            criteriaVersions.forEach((item, idx) => {
                // item = CriteriaVersionResource
                const card = document.createElement('div');
                card.className = 'bg-gray-100 p-6 rounded-lg shadow-sm flex flex-col justify-between';
                // Fix: support both created_by (object) and created_by_name (string or null)
                let creatorName = '-';
                if (item.created_by && typeof item.created_by === 'object' && item.created_by.name) {
                    creatorName = item.created_by.name;
                } else if (item.created_by_name) {
                    creatorName = item.created_by_name;
                } else if (typeof item.created_by === 'string') {
                    creatorName = item.created_by;
                }
                card.innerHTML = `
                    <h3 class="text-xl font-semibold text-gray-900">${item.report_title || 'ไม่ระบุชื่อรายงาน'}</h3>
                    <p class="text-sm text-gray-600 mb-4">สร้างโดย: <span class="font-semibold">${creatorName}</span></p>
                    <div class="flex space-x-2 items-center justify-center">
                        <x-button 
                            type="warning"
                            text="แก้ไข"
                            icon="fas fa-edit"
                            href="/criteria-config/${item.id}/edit" />
                        <x-button 
                            type="danger" 
                            text="ลบ" 
                            buttonType="button" 
                            icon="fas fa-trash-alt"
                            onclick="showDeleteModal(${item.id}, this)" />
                    </div>
                `;
                grid.appendChild(card);
            });
        }

        // Delete function
        // Modal state
        let deleteModal = null;
        let deleteTargetId = null;
        let deleteTargetBtn = null;

        // Modal HTML (top bar style)
        function ensureDeleteModal() {
            if (deleteModal) return;
            deleteModal = document.createElement('div');
            deleteModal.id = 'delete-modal';
            deleteModal.className = 'fixed inset-0 z-[99999] flex items-center justify-center'; // high z-index, full screen

            // Add overlay and modal box
            deleteModal.innerHTML = `
                <div class="fixed inset-0 bg-gray-800 bg-opacity-40 modal-overlay"></div>
                <div class="relative z-10 mt-6 bg-white border border-red-200 rounded-xl shadow-2xl max-w-md w-full p-8 text-center animate-fade-in">
                    <div class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">ยืนยันการลบเวอร์ชัน</h3>
                    <p class="text-gray-600 mb-6">คุณต้องการลบเวอร์ชันนี้หรือไม่? <br><span class="text-red-500 font-semibold">ข้อมูลนี้จะไม่สามารถกู้คืนได้</span></p>
                    <div class="flex justify-center gap-4 mt-4">
                        <button id="cancel-delete-btn" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md font-semibold hover:bg-gray-300">ยกเลิก</button>
                        <button id="confirm-delete-btn" class="px-6 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-500">ลบ</button>
                    </div>
                </div>
            `;
            document.body.appendChild(deleteModal);

            // Prevent closing by clicking overlay
            deleteModal.querySelector('.modal-overlay').onclick = function(e) { e.stopPropagation(); };

            // Event listeners
            deleteModal.querySelector('#cancel-delete-btn').onclick = function() {
                hideDeleteModal();
            };
            deleteModal.querySelector('#confirm-delete-btn').onclick = function() {
                if (deleteTargetId && deleteTargetBtn) {
                    doDeleteCriteriaVersion(deleteTargetId, deleteTargetBtn);
                }
            };
        }

        function showDeleteModal(id, btn) {
            ensureDeleteModal();
            deleteTargetId = id;
            deleteTargetBtn = btn;
            deleteModal.classList.remove('hidden');
        }
        function hideDeleteModal() {
            if (deleteModal) deleteModal.classList.add('hidden');
            deleteTargetId = null;
            deleteTargetBtn = null;
        }

        function deleteCriteriaVersion(id, btn) {
            showDeleteModal(id, btn);
        }

        function doDeleteCriteriaVersion(id, btn) {
            btn.disabled = true;
            fetch(`/report-version/${id}`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                credentials: 'same-origin'
            })
            .then(res => {
                if (res.ok) {
                    hideDeleteModal();
                    showAlert('ลบข้อมูลสำเร็จ', 'success');
                    setTimeout(() => { window.location.reload(); }, 1200);
                } else {
                    return res.json().then(data => { throw new Error(data.message || 'ลบไม่สำเร็จ'); });
                }
            })
            .catch(err => {
                let msg = err.message;
                console.error('Delete error:', msg);
                showAlert('เกิดข้อผิดพลาด: ' + msg, 'error');
                btn.disabled = false;
                hideDeleteModal();
            });
        }
    </script>
        </div>
    </div>
@endsection
