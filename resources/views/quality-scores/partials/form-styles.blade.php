@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .quality-score-page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .quality-score-card,
        .quality-score-form-container,
        .quality-score-info-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .quality-score-card-header,
        .quality-score-form-header {
            background-color: #f3e8ff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }
        .quality-score-card-header h4,
        .quality-score-form-header h4 {
            color: #2c2c2c;
            margin: 0;
            font-weight: 500;
            font-size: 1.1rem;
        }
        .quality-score-card-header p {
            margin: 8px 0 0;
            color: #666666;
        }
        .quality-score-form-body,
        .quality-score-card-body {
            padding: 24px;
        }
        .quality-score-form-group {
            margin-bottom: 20px;
        }
        .quality-score-label {
            font-weight: 500;
            color: #2c2c2c;
            margin-bottom: 6px;
            font-size: 0.9rem;
            display: block;
        }
        .quality-score-control,
        .quality-score-select {
            border: 1px solid #cccccc;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 0.9rem;
            transition: border-color 0.15s ease;
            width: 100%;
        }
        .quality-score-control:focus,
        .quality-score-select:focus {
            border-color: #666666;
            box-shadow: 0 0 0 0.15rem rgba(102, 102, 102, 0.1);
            outline: none;
        }
        .quality-score-alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            border: 1px solid #f5c6cb;
            background-color: #f8d7da;
            color: #721c24;
        }
        .quality-score-info-card {
            padding: 16px;
            background-color: #f8f9fa;
        }
        .quality-score-info-card h5 {
            margin-bottom: 16px;
        }
        .quality-score-info-item {
            display: flex;
            margin-bottom: 8px;
            gap: 12px;
        }
        .quality-score-info-label {
            width: 160px;
            flex-shrink: 0;
            font-weight: 500;
            color: #333333;
        }
        .quality-score-info-value {
            color: #666666;
        }
        .quality-score-selected-box {
            min-height: 100px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 15px;
            background-color: #ffffff;
        }
        .quality-score-empty-message {
            text-align: center;
            color: #666666;
            font-style: italic;
            padding: 20px;
        }
        .quality-score-row,
        .quality-user-score-row {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            background-color: #f8f9fa;
        }
        .quality-score-row-info,
        .quality-user-info {
            flex: 1;
            min-width: 0;
        }
        .quality-score-row-name,
        .quality-user-name {
            font-weight: 500;
            color: #333333;
            margin-bottom: 4px;
        }
        .quality-score-row-meta,
        .quality-user-email {
            font-size: 0.8rem;
            color: #666666;
        }
        .quality-score-user-criteria {
            font-size: 0.8rem;
            color: #007bff;
            margin-top: 4px;
        }
        .quality-score-input-group {
            width: 150px;
            flex-shrink: 0;
        }
        .quality-score-remove-button {
            flex-shrink: 0;
            background: none;
            border: none;
            color: #dc3545;
            font-size: 1.1rem;
            cursor: pointer;
            padding: 5px;
            border-radius: 3px;
            transition: background-color 0.15s ease;
        }
        .quality-score-remove-button:hover {
            background-color: #f5c6cb;
        }
        .quality-score-actions {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            margin-top: 24px;
        }
        .quality-score-btn {
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            font-size: 0.9rem;
            border: 1px solid #cccccc;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            background-color: #ffffff;
            color: #333333;
        }
        .quality-score-btn:hover:not(:disabled) {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
            text-decoration: none;
        }
        .quality-score-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .quality-score-btn-secondary {
            color: #666666;
        }
        .is-invalid {
            border-color: #dc3545;
        }
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 4px;
        }
        .text-danger {
            color: #dc3545 !important;
        }
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #cccccc;
            border-radius: 3px;
            padding: 5px 8px;
            min-height: 45px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #007bff;
            border: 1px solid #007bff;
            border-radius: 3px;
            color: #ffffff;
            padding: 2px 8px;
            margin-right: 5px;
            margin-top: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #ffffff;
            margin-right: 5px;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ffcccc;
        }
    </style>
@endpush
