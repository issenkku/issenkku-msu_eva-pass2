{{-- style เฉพาะของฟอร์ม assignment-data ที่ใช้ร่วมกันระหว่าง create/edit --}}
<style>
    body {
        font-family: 'Sarabun', sans-serif;
    }

    .step-card {
        transition: all 0.3s ease;
        position: relative;
    }

    .step-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 8px 8px 0 0;
    }

    .step-card.step-1::before { background: linear-gradient(90deg, #2563eb, #3b82f6); }
    .step-card.step-2::before { background: linear-gradient(90deg, #059669, #10b981); }
    .step-card.step-3::before { background: linear-gradient(90deg, #7c3aed, #8b5cf6); }
    .step-card.step-4::before { background: linear-gradient(90deg, #ea580c, #f97316); }

    .form-section {
        transition: all 0.3s ease;
    }

    .form-section:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .position-card {
        transition: all 0.3s ease;
    }

    .position-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .summary-card {
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    }

    .bg-gradient-blue { background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); }
    .bg-gradient-green { background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); }
    .bg-gradient-purple { background: linear-gradient(135deg, #e9d5ff 0%, #ddd6fe 100%); }
    .bg-gradient-orange { background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%); }

    .step-completed {
        animation: stepComplete 0.5s ease-in-out;
    }

    @keyframes stepComplete {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
