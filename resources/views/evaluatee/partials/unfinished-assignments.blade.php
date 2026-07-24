<div class="rounded-2xl border px-10 pb-6 pt-6 shadow-md" style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
    <div class="mb-6 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-purple-700">
            <i class="fas fa-bell text-fuchsia-500"></i>
            การประเมินที่ยังไม่เสร็จ
        </h3>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @forelse($unfinishedAssignments as $assignment)
            <x-evaluation-header
                :title="$assignment['title']"
                :period="$assignment['period']"
                :deadline="$assignment['deadline']"
                :daysLeft="$assignment['daysLeft']"
                :evaluationId="$assignment['id']" />
        @empty
            <div class="col-span-full rounded-xl border border-gray-100 bg-white py-10 text-center shadow-inner">
                <p class="text-lg text-gray-500">ไม่มีการประเมินที่ค้างอยู่</p>
            </div>
        @endforelse
    </div>
</div>
