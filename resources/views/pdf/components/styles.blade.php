<style>
    @page {
        size: A4 portrait;
        margin: 8mm;
    }

    body {
        margin: 0;
        padding: 0;
        background: #f4f5f7;
        font-family: 'DejaVu Sans', Arial, sans-serif;
        color: #0f172a;
        height: 297mm;
    }

    .page {
        width: 210mm;
        margin: 0 auto;
        padding: 10mm 12mm 12mm;
        max-height: calc(297mm - 16mm);
    }

    .card {
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 18px 32px rgba(15, 23, 42, 0.08);
        max-height: 100%;
    }

    .gradient-header {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: #f8fafc;
        padding: 32px;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
    }

    .eyebrow {
        text-transform: uppercase;
        letter-spacing: 0.4em;
        font-size: 10px;
        margin: 0 0 8px;
        color: rgba(248, 250, 252, 0.8);
    }

    .header-meta {
        text-align: right;
        font-size: 11px;
        letter-spacing: 0.05em;
    }

    .section-row,
    .section-grid,
    .items,
    .summary,
    .footer-grid {
        padding: 0 20px;
    }

    .section-row {
        display: flex;
        gap: 12px;
        padding-top: 20px;
    }

    .box,
    .small-card,
    .footer-card {
        flex: 1;
        background: #f8fafc;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 12px;
    }

    .box p,
    .small-card p,
    .footer-card p {
        margin: 4px 0;
        font-size: 11px;
    }

    .box strong,
    .summary-card strong {
        font-weight: 600;
    }

    .section-grid {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .small-card {
        min-width: 0;
    }

    .items {
        margin-top: 20px;
        padding-bottom: 16px;
        max-height: 120mm;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 10px;
    }

    .items-table th {
        background: #0f172a;
        color: #ffffff;
        letter-spacing: 0.3em;
        text-transform: uppercase;
        font-size: 9px;
        padding: 8px 6px;
        text-align: left;
    }

    .items-table td {
        padding: 8px 6px;
        border-bottom: 1px solid #e2e8f0;
        vertical-align: middle;
    }

    .items-table td.amount,
    .items-table th.amount {
        text-align: right;
    }

    .summary {
        display: flex;
        justify-content: flex-end;
        padding-bottom: 16px;
    }

    .summary-card {
        width: 280px;
        background: #f8fafc;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        padding: 16px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        margin-bottom: 4px;
    }

    .summary-divider {
        border-top: 1px dashed #cbd5f5;
        margin: 10px 0;
    }

    .grand {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        font-weight: 700;
    }

    .footer-grid {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
    }

    .footer-card {
        min-height: 90px;
        line-height: 1.4;
        font-size: 11px;
        padding-bottom: 8px;
    }

    .footer-card .label {
        font-size: 9px;
        text-transform: uppercase;
        letter-spacing: 0.3em;
        color: #6b7280;
        margin-bottom: 4px;
        display: block;
    }

    .page,
    .card {
        page-break-inside: avoid;
        page-break-after: avoid;
    }
</style>
