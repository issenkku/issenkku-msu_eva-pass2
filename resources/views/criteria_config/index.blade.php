<!-- resources/views/criteria/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold text-gray-800">
                    ชื่อหน้า
                </h2>
                <a href="{{ route('criteria_config.create') }}" class="px-5 py-2 bg-lime-400 text-gray-800 font-semibold rounded-md hover:bg-lime-300">
                    เพิ่มเกณฑ์
                </a>
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
        document.addEventListener('DOMContentLoaded', function () {
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
                card.innerHTML = `
                    <h3 class="text-xl font-semibold text-gray-900">${item.version_name || 'ไม่ระบุชื่อเวอร์ชัน'}</h3>
                    <p class="text-sm text-gray-600 mb-4">สร้างโดย: <span class="font-semibold">${item.created_by && item.created_by.name ? item.created_by.name : (item.created_by_name ?? '-')}</span></p>
                    <div class="flex space-x-2 mt-auto">
                        <a href="/criteria-config/${item.id}/edit" class="flex-1 text-center px-4 py-2 bg-yellow-400 text-gray-800 rounded-md hover:bg-yellow-500">แก้ไข</a>
                        <button type="button" onclick="showDeleteModal(${item.id}, this)" class="flex-1 text-center px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-500 hover:text-white">ลบ</button>
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

        // Modal HTML
        function ensureDeleteModal() {
            if (deleteModal) return;
            deleteModal = document.createElement('div');
            deleteModal.id = 'delete-modal';
            deleteModal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40 hidden';
            deleteModal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-8 text-center relative animate-fade-in">
                    <div class="mx-auto mb-4 flex items-center justify-center w-16 h-16 rounded-full bg-red-100">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">ยืนยันการลบเวอร์ชัน</h3>
                    <p class="text-gray-600 mb-6">คุณต้องการลบเวอร์ชันนี้หรือไม่? <br><span class="text-red-500 font-semibold">ข้อมูลนี้จะไม่สามารถกู้คืนได้</span></p>
                    <div class="flex justify-center gap-4 mt-4">
                        <button id="confirm-delete-btn" class="px-6 py-2 bg-red-600 text-white rounded-md font-semibold hover:bg-red-500">ลบ</button>
                        <button id="cancel-delete-btn" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md font-semibold hover:bg-gray-300">ยกเลิก</button>
                    </div>
                </div>
            `;
            document.body.appendChild(deleteModal);
            // Event listeners
            deleteModal.querySelector('#cancel-delete-btn').onclick = function() {
                hideDeleteModal();
            };
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) hideDeleteModal();
            });
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
            deleteModal.classList.add('hidden');
            deleteTargetId = null;
            deleteTargetBtn = null;
        }

        function deleteCriteriaVersion(id, btn) {
            showDeleteModal(id, btn);
        }

        function doDeleteCriteriaVersion(id, btn) {
            btn.disabled = true;
            fetch(`/reports/${id}`, {
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
                    // Remove card from UI
                    btn.closest('.bg-gray-100').remove();
                    hideDeleteModal();
                } else {
                    return res.json().then(data => { throw new Error(data.message || 'ลบไม่สำเร็จ'); });
                }
            })
            .catch(err => {
                alert('เกิดข้อผิดพลาด: ' + err.message);
                btn.disabled = false;
                hideDeleteModal();
            });
        }
    </script>
        </div>
    </div>
@endsection