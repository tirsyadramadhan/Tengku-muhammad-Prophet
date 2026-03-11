<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; // Ensure you have yajra/laravel-datatables installed
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Join through invoice and delivery to reach po and customer
            $data = Payment::query()
                ->leftJoin('tbl_invoice', 'tbl_payment.invoice_id', '=', 'tbl_invoice.invoice_id')
                ->leftJoin('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->select([
                    'tbl_payment.*',
                    'tbl_invoice.nomor_invoice',
                    'tbl_invoice.tgl_invoice',
                    'tbl_invoice.due_date',
                    'tbl_invoice.status_invoice',
                    'tbl_delivery.delivery_no',
                    'tbl_delivery.qty_delivered',
                    'tbl_delivery.delivered_at',
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_po.harga',
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('detail_pembayaran', function ($row) {
                    $paymentDate      = \Carbon\Carbon::parse($row->payment_date_estimation);
                    $dateFormatted    = $paymentDate->format('d M Y');
                    $dateRelative     = $paymentDate->toIndonesianRelative();
                    $amountFormatted  = 'Rp ' . number_format((float) $row->amount, 0, ',', '.');

                    // ── Payment Method ────────────────────────────────────────
                    $methodMap = match (strtolower($row->metode_bayar ?? '')) {
                        'transfer' => ['label' => 'Transfer',  'icon' => 'ri-bank-line',          'color' => '#0ea5e9', 'bg' => '#e0f2fe'],
                        'cash'     => ['label' => 'Cash',      'icon' => 'ri-money-dollar-box-line', 'color' => '#10b981', 'bg' => '#dcfce7'],
                        'credit'   => ['label' => 'Credit',    'icon' => 'ri-bank-card-line',      'color' => '#f59e0b', 'bg' => '#fef3c7'],
                        default    => [
                            'label' => strtoupper($row->metode_bayar ?? 'Other'),
                            'icon' => 'ri-exchange-line',
                            'color' => '#9ca3af',
                            'bg' => '#f1f5f9'
                        ],
                    };

                    // ── Payment Status ────────────────────────────────────────
                    $statusMap = match ((int) $row->payment_status) {
                        1       => ['label' => 'Lunas',   'icon' => 'ri-checkbox-circle-fill', 'color' => '#10b981'],
                        0       => ['label' => 'Belum Lunas', 'icon' => 'ri-time-line',        'color' => '#ef4444'],
                        default => ['label' => 'Unknown', 'icon' => 'ri-question-line',        'color' => '#9ca3af'],
                    };

                    return '
                        <style>
                            .pay-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 240px;
                                max-width: 280px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .pay-amount-hero {
                                padding: 12px 14px 10px;
                                background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                                display: flex;
                                align-items: center;
                                gap: 10px;
                            }
                            .pay-amount-icon {
                                width: 32px;
                                height: 32px;
                                background: rgba(255,255,255,0.12);
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 1rem;
                                color: #34d399;
                                flex-shrink: 0;
                            }
                            .pay-amount-label {
                                font-size: 0.58rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.07em;
                                color: #94a3b8;
                            }
                            .pay-amount-value {
                                font-size: 1rem;
                                font-weight: 800;
                                color: #34d399;
                                letter-spacing: 0.01em;
                                line-height: 1.2;
                            }
                            .pay-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .pay-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .pay-row-icon {
                                width: 22px;
                                height: 22px;
                                border-radius: 6px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.75rem;
                                flex-shrink: 0;
                                margin-top: 1px;
                            }
                            .pay-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .pay-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .pay-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .pay-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .pay-relative-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                background: #e0f2fe;
                                color: #0369a1;
                                margin-top: 2px;
                            }
                            .pay-ref-grid {
                                display: grid;
                                grid-template-columns: 1fr 1fr;
                                gap: 6px;
                            }
                            .pay-ref-cell {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                                padding: 5px 7px;
                                background: #f8fafc;
                                border-radius: 7px;
                                border: 1px solid #f1f5f9;
                            }
                            .pay-method-footer {
                                padding: 8px 14px 10px;
                                border-top: 1.5px solid;
                            }
                            .pay-method-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 12px 4px 8px;
                                border-radius: 20px;
                                font-size: 0.75rem;
                                font-weight: 700;
                                border: 1.5px solid;
                            }
                            .pay-date-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 4px;
                                font-size: 0.65rem;
                                font-weight: 600;
                                color: #64748b;
                            }
                        </style>
                        <div class="pay-card shadow-lg">

                            <!-- Hero: Total Pembayaran (dark header so the green amount POPS) -->
                            <div class="pay-amount-hero">
                                <div class="pay-amount-icon">
                                    <i class="ri-money-dollar-circle-line"></i>
                                </div>
                                <div class="d-flex flex-column gap-0">
                                    <span class="pay-amount-label">Total Pembayaran</span>
                                    <span class="pay-amount-value">' . $amountFormatted . '</span>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="pay-card-body">

                                <!-- Tanggal Pembayaran -->
                                <div class="pay-row">
                                    <div class="pay-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="pay-row-content">
                                        <span class="pay-row-label">Tanggal Pembayaran</span>
                                        <span class="pay-row-value">' . $dateFormatted . '</span>
                                        <span class="pay-relative-badge">' . $dateRelative . '</span>
                                    </div>
                                </div>

                                <div class="pay-divider"></div>

                                <!-- Nama Barang + Qty side by side -->
                                <div class="pay-row">
                                    <div class="pay-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-box-3-line"></i>
                                    </div>
                                    <div class="pay-row-content" style="flex:1;">
                                        <span class="pay-row-label">Nama Barang</span>
                                        <span class="pay-row-value">' . e($row->nama_barang) . '</span>
                                        <span style="font-size:0.72rem;font-weight:600;color:#7c3aed;margin-top:2px;">
                                            <i class="ri-stack-line me-1"></i>' . number_format((int) $row->qty_delivered, 0, ',', '.') . ' Unit
                                        </span>
                                    </div>
                                </div>

                                <div class="pay-divider"></div>

                                <!-- Reference Numbers Grid: PO / Delivery / Invoice -->
                                <div class="pay-ref-grid">
                                    <div class="pay-ref-cell">
                                        <span class="pay-row-label">No. PO</span>
                                        <span style="font-size:0.76rem;font-weight:700;color:#1e293b;">' . e($row->no_po) . '</span>
                                    </div>
                                    <div class="pay-ref-cell">
                                        <span class="pay-row-label">No. Delivery</span>
                                        <span style="font-size:0.76rem;font-weight:700;color:#1e293b;">' . e($row->delivery_no) . '</span>
                                    </div>
                                    <div class="pay-ref-cell" style="grid-column:1/-1;">
                                        <span class="pay-row-label">No. Invoice</span>
                                        <span style="font-size:0.76rem;font-weight:700;color:#1e293b;">' . e($row->nomor_invoice) . '</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer: Payment Method -->
                            <div class="pay-method-footer" style="
                                background:' . $methodMap['bg'] . ';
                                border-color:' . $methodMap['color'] . '30;
                                display:flex;
                                flex-direction:column;
                                gap:6px;
                                align-items:flex-start;
                            ">
                                <!-- Method + Status side by side -->
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span class="pay-method-chip" style="background:' . $methodMap['color'] . '18;color:' . $methodMap['color'] . ';border-color:' . $methodMap['color'] . '40;">
                                        <i class="' . $methodMap['icon'] . '" style="font-size:0.85rem;"></i>
                                        ' . $methodMap['label'] . '
                                    </span>

                                    <span class="pay-status-chip" style="
                                        display:inline-flex;
                                        align-items:center;
                                        gap:4px;
                                        padding:4px 10px 4px 7px;
                                        border-radius:20px;
                                        font-size:0.72rem;
                                        font-weight:700;
                                        border:1.5px solid;
                                        background:' . $statusMap['color'] . '18;
                                        color:' . $statusMap['color'] . ';
                                        border-color:' . $statusMap['color'] . '40;
                                    ">
                                        <i class="' . $statusMap['icon'] . '" style="font-size:0.8rem;"></i>
                                        ' . $statusMap['label'] . '
                                    </span>
                                </div>

                                <!-- Date at the very bottom -->
                                <span class="pay-date-chip">
                                    <i class="ri-calendar-line"></i>' . $dateFormatted . '
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->addColumn('payment_date_estimation', function ($row) {
                    // ── No Estimation Date ────────────────────────────────────
                    if (!$row->payment_date_estimation) {
                        return '
                            <style>
                                .pmt-card {
                                    display: inline-flex;
                                    flex-direction: column;
                                    gap: 0;
                                    min-width: 200px;
                                    background: #ffffff;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                    font-family: inherit;
                                }
                                .pmt-card-body {
                                    padding: 10px 14px;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                }
                                .pmt-icon-wrap {
                                    width: 32px;
                                    height: 32px;
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 1rem;
                                    flex-shrink: 0;
                                }
                                .pmt-row-label {
                                    font-size: 0.6rem;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    color: #94a3b8;
                                }
                                .pmt-row-value {
                                    font-size: 0.82rem;
                                    font-weight: 700;
                                    color: #1e293b;
                                    line-height: 1.3;
                                }
                                .pmt-footer {
                                    padding: 5px 14px 8px;
                                    border-top: 1.5px solid;
                                }
                                .pmt-chip {
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 3px 10px 3px 7px;
                                    border-radius: 20px;
                                    font-size: 0.72rem;
                                    font-weight: 700;
                                    border: 1.5px solid;
                                }
                            </style>
                            <div class="pmt-card shadow-lg">
                                <div class="pmt-card-body">
                                    <div class="pmt-icon-wrap" style="background:#f1f5f9;color:#94a3b8;">
                                        <i class="ri-calendar-2-line"></i>
                                    </div>
                                    <div class="d-flex flex-column gap-0">
                                        <span class="pmt-row-label">Estimasi Pembayaran</span>
                                        <span class="pmt-row-value" style="color:#94a3b8;">Tidak Ditentukan</span>
                                    </div>
                                </div>
                                <div class="pmt-footer" style="background:#f9fafb;border-color:#e5e7eb;">
                                    <span class="pmt-chip" style="background:#9ca3af18;color:#9ca3af;border-color:#9ca3af40;">
                                        <i class="ri-minus-circle-line" style="font-size:0.8rem;"></i>
                                        Tidak Ada Estimasi
                                    </span>
                                </div>
                            </div>';
                    }

                    if ((int) $row->payment_status === 1) {
                        $paidDate = $row->payment_date_estimation
                            ? \Carbon\Carbon::parse($row->payment_date_estimation)->format('d M Y')
                            : null;

                        return '
                        <div class="pmt-card shadow-lg">

                            <!-- Header -->
                            <div style="padding:10px 14px 8px;background:linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%);border-bottom:1px solid #bbf7d0;display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;background:#bbf7d0;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#16a34a;flex-shrink:0;">
                                    <i class="ri-checkbox-circle-fill"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Status Pembayaran</div>
                                    <div style="font-size:0.78rem;font-weight:700;color:#14532d;">Lunas</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div style="padding:10px 14px;display:flex;flex-direction:column;gap:2px;">
                                <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Tanggal Bayar</span>
                                <span style="font-size:0.95rem;font-weight:800;color:#16a34a;letter-spacing:0.03em;">
                                    ' . ($paidDate ?? '—') . '
                                </span>
                                <span style="
                                    display: inline-block;
                                    font-size: 0.62rem;
                                    font-weight: 600;
                                    padding: 1px 7px;
                                    border-radius: 20px;
                                    background: #e0f2fe;
                                    color: #0369a1;
                                    margin-top: 2px;
                                    width:max-content;
                                ">
                                    ' . \Carbon\Carbon::parse($row->payment_date_estimation)->toIndonesianRelative() . '
                                </span>
                            </div>

                            <!-- Footer -->
                            <div style="padding:5px 14px 8px;background:#f0fdf4;border-top:1.5px solid #bbf7d0;">
                                <span class="pmt-chip" style="background:#10b98118;color:#10b981;border-color:#10b98140;">
                                    <i class="ri-check-double-line" style="font-size:0.8rem;"></i>
                                    Sudah Dibayar
                                </span>
                            </div>

                        </div>';
                    }

                    $estDate      = \Carbon\Carbon::parse($row->payment_date_estimation);
                    $isOverdue    = $estDate->lte(now());
                    $isDueToday   = $estDate->isToday();
                    $isoTarget    = $estDate->toISOString();
                    $dateFormatted = $estDate->format('d M Y');
                    $dateRelative  = $estDate->toIndonesianRelative();

                    // ── STATE: OVERDUE ────────────────────────────────────────
                    if ($isOverdue && !$isDueToday) {
                        return '
                            <div class="pmt-card shadow-lg">

                                <!-- Header -->
                                <div style="padding:10px 14px 8px;background:linear-gradient(135deg,#fef2f2 0%,#fee2e2 100%);border-bottom:1px solid #fecaca;display:flex;align-items:center;gap:8px;">
                                    <div style="width:28px;height:28px;background:#fee2e2;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#ef4444;flex-shrink:0;">
                                        <i class="ri-alarm-warning-line"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Estimasi Pembayaran</div>
                                        <div style="font-size:0.78rem;font-weight:700;color:#7f1d1d;">' . $dateFormatted . '</div>
                                    </div>
                                </div>

                                <!-- Body -->
                                <div style="padding:10px 14px;display:flex;flex-direction:column;gap:2px;">
                                    <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Telah Lewat</span>
                                    <span style="font-size:0.95rem;font-weight:800;color:#ef4444;letter-spacing:0.03em;">
                                        ' . $dateRelative . '
                                    </span>
                                </div>

                                <!-- Footer -->
                                <div style="padding:5px 14px 8px;background:#fef2f2;border-top:1.5px solid #fecaca;display:flex;flex-direction:column;align-items:flex-start;gap:6px;">
                                    <span class="pmt-chip" style="background:#ef444418;color:#ef4444;border-color:#ef444440;">
                                        <i class="ri-error-warning-line" style="font-size:0.8rem;"></i>
                                        Lewat Estimasi
                                    </span>
                                    <span style="font-size:0.65rem;font-weight:600;color:#94a3b8;">
                                        <i class="ri-calendar-line me-1"></i>' . $dateFormatted . '
                                    </span>
                                    <button
                                        class="btn-pay-now pay-now-btn bayar-sekarang-btn"
                                        data-id="' . $row->payment_id . '"
                                        data-url="' . route('payments.payNow', $row->payment_id) . '"
                                        data-token="' . csrf_token() . '">
                                        <i class="ri-money-dollar-circle-line text-success"></i>
                                        Bayar Sekarang
                                    </button>
                                </div>
                            </div>';
                    }

                    // ── STATE: DUE TODAY ──────────────────────────────────────
                    if ($isDueToday) {
                        return '
                            <div class="pmt-card shadow-lg">

                                <!-- Header -->
                                <div style="padding:10px 14px 8px;background:linear-gradient(135deg,#fffbeb 0%,#fef3c7 100%);border-bottom:1px solid #fde68a;display:flex;align-items:center;gap:8px;">
                                    <div style="width:28px;height:28px;background:#fde68a;border-radius:8px;display:flex;align-items:center;justify-content:justify-content:center;font-size:0.85rem;color:#d97706;flex-shrink:0;display:flex;align-items:center;justify-content:center;">
                                        <i class="ri-timer-flash-line"></i>
                                    </div>
                                    <div>
                                        <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Estimasi Pembayaran</div>
                                        <div style="font-size:0.78rem;font-weight:700;color:#78350f;">' . $dateFormatted . '</div>
                                    </div>
                                </div>

                                <!-- Countdown Body -->
                                <div style="padding:10px 14px;display:flex;flex-direction:column;gap:2px;" class="timer-wrapper" data-target="' . $isoTarget . '" data-id="' . $row->payment_id . '">
                                    <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Sisa Waktu Hari Ini</span>
                                    <span class="countdown-display payment-timer" style="font-size:0.95rem;font-weight:800;color:#d97706;letter-spacing:0.03em;font-variant-numeric:tabular-nums;">
                                        Calculating...
                                    </span>
                                </div>

                                <!-- Footer -->
                                <div style="padding:5px 14px 8px;background:#fffbeb;border-top:1.5px solid #fde68a;">
                                    <span class="pmt-chip" style="background:#f59e0b18;color:#d97706;border-color:#f59e0b40;">
                                        <i class="ri-alarm-line" style="font-size:0.8rem;"></i>
                                        Jatuh Tempo Hari Ini!
                                    </span>
                                    <button
                                        class="btn-pay-now pay-now-btn bayar-sekarang-btn"
                                        data-id="' . $row->payment_id . '"
                                        data-url="' . route('payments.payNow', $row->payment_id) . '"
                                        data-token="' . csrf_token() . '"                                        
                                    >
                                        <i class="ri-money-dollar-circle-line text-success"></i>
                                        Bayar Sekarang
                                    </button>
                                </div>

                            </div>';
                    }

                    // ── STATE: FUTURE COUNTDOWN ───────────────────────────────
                    return '
                        <div class="shadow-lg pmt-card timer-wrapper" data-target="' . $isoTarget . '" data-id="' . $row->payment_id . '">

                            <!-- Header -->
                            <div style="padding:10px 14px 8px;background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border-bottom:1px solid #e5e7eb;display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;background:#e0f2fe;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:#0284c7;flex-shrink:0;">
                                    <i class="ri-calendar-schedule-line"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Estimasi Pembayaran</div>
                                    <div style="font-size:0.78rem;font-weight:700;color:#1e293b;">' . $dateFormatted . '</div>
                                </div>
                            </div>

                            <!-- Countdown Body -->
                            <div style="padding:10px 14px;display:flex;flex-direction:column;gap:2px;">
                                <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Sisa Waktu</span>
                                <span class="countdown-display payment-timer" style="font-size:0.95rem;font-weight:800;color:#0284c7;letter-spacing:0.03em;font-variant-numeric:tabular-nums;">
                                    Calculating...
                                </span>
                            </div>

                            <!-- Footer -->
                            <div style="padding:5px 14px 8px;background:#f8fafc;border-top:1.5px solid #e5e7eb;">
                                <span class="pmt-chip" style="background:#3b82f618;color:#3b82f6;border-color:#3b82f640;">
                                    <i class="ri-hourglass-line" style="font-size:0.8rem;"></i>
                                    Menunggu Pembayaran
                                </span>
                                    <button
                                        class="btn-pay-now pay-now-btn bayar-sekarang-btn"
                                        data-id="' . $row->payment_id . '"
                                        data-url="' . route('payments.payNow', $row->payment_id) . '"
                                        data-token="' . csrf_token() . '"                                        
                                    >
                                        <i class="ri-money-dollar-circle-line text-success"></i>
                                        Bayar Sekarang
                                    </button>
                            </div>

                        </div>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('payment.show') ? route('payment.show', $row->payment_id) : '#';
                    $editUrl = Route::has('payment.edit') ? route('payment.edit', $row->payment_id) : '#';
                    $deleteUrl = Route::has('payment.destroy') ? route('payment.destroy', $row->payment_id) : '#';

                    $user = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editBtn = $canEditDelete ? '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>' : '';

                    $deleteBtn = $canEditDelete ? '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                        data-url="' . $deleteUrl . '" 
                        data-po="No po ' . $row->no_po . ' Nama Barang ' . $row->nama_barang . '" 
                        title="Delete">
                        <i class="ri-delete-bin-line"></i>
                    </button>' : '';

                    return '
                    <div class="d-flex align-items-center gap-2">
                        <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                            <i class="ri-eye-line"></i>
                        </a>
                        ' . $editBtn . '
                        ' . $deleteBtn . '
                    </div>
                    ';
                })
                ->orderColumn('detail_pembayaran', 'tbl_payment.payment_date $1')
                ->filterColumn('detail_pembayaran', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));
                    $numericClean  = preg_replace('/[^0-9]/', '', $keyword);

                    // ── Payment Status
                    if (str_contains($keyword_lower, 'belum lunas')) {
                        $data->where('tbl_payment.payment_status', 0);
                        return;
                    }

                    if (str_contains($keyword_lower, 'lunas')) {
                        $data->where('tbl_payment.payment_status', 1);
                        return;
                    }

                    // ── Metode Bayar
                    if (str_contains($keyword_lower, 'transfer')) {
                        $data->whereRaw("LOWER(tbl_payment.metode_bayar) = 'transfer'");
                        return;
                    }

                    if (str_contains($keyword_lower, 'cash')) {
                        $data->whereRaw("LOWER(tbl_payment.metode_bayar) = 'cash'");
                        return;
                    }

                    if (str_contains($keyword_lower, 'credit')) {
                        $data->whereRaw("LOWER(tbl_payment.metode_bayar) = 'credit'");
                        return;
                    }

                    $data->where(function ($q) use ($keyword, $numericClean) {

                        // ── Payment Date Estimation
                        $q->whereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d %M %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE(tbl_payment.payment_date_estimation) = STR_TO_DATE(?, '%d %b %Y')", [$keyword])
                            ->orWhereRaw("DATE(tbl_payment.payment_date_estimation) = STR_TO_DATE(?, '%d %M %Y')", [$keyword])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d/%m/%Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%Y-%m-%d') like ?", ["%{$keyword}%"])

                            // ── Amount
                            ->orWhereRaw("CAST(tbl_payment.amount AS CHAR) like ?", ["%{$numericClean}%"])

                            // ── Nomor Invoice
                            ->orWhere('tbl_invoice.nomor_invoice', 'like', "%{$keyword}%")

                            // ── Delivery No
                            ->orWhere('tbl_delivery.delivery_no', 'like', "%{$keyword}%")
                            ->orWhereRaw("CAST(tbl_delivery.qty_delivered AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CONCAT(tbl_delivery.qty_delivered, ' Unit') like ?", ["%{$keyword}%"])

                            // ── PO columns
                            ->orWhere('tbl_po.no_po', 'like', "%{$keyword}%")
                            ->orWhere('tbl_po.nama_barang', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('payment_date_estimation', 'tbl_payment.payment_status $1, tbl_payment.payment_date_estimation DESC')
                ->filterColumn('payment_date_estimation', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));

                    // ── Lunas / Sudah Dibayar
                    if (
                        str_contains($keyword_lower, 'lunas') ||
                        str_contains($keyword_lower, 'sudah dibayar')
                    ) {
                        $data->where('tbl_payment.payment_status', 1);
                        return;
                    }

                    // ── Belum Lunas / Menunggu Pembayaran
                    if (
                        str_contains($keyword_lower, 'belum lunas') ||
                        str_contains($keyword_lower, 'menunggu pembayaran')
                    ) {
                        $data->where('tbl_payment.payment_status', 0);
                        return;
                    }

                    // ── Tidak Ada Estimasi / Tidak Ditentukan
                    if (
                        str_contains($keyword_lower, 'tidak ada estimasi') ||
                        str_contains($keyword_lower, 'tidak ditentukan')
                    ) {
                        $data->whereNull('tbl_payment.payment_date_estimation');
                        return;
                    }

                    // ── Lewat Estimasi / Telah Lewat (overdue, not today)
                    if (
                        str_contains($keyword_lower, 'lewat estimasi') ||
                        str_contains($keyword_lower, 'telah lewat')
                    ) {
                        $data->where('tbl_payment.payment_status', 0)
                            ->whereNotNull('tbl_payment.payment_date_estimation')
                            ->whereRaw("DATE(tbl_payment.payment_date_estimation) < CURDATE()");
                        return;
                    }

                    // ── Jatuh Tempo Hari Ini
                    if (str_contains($keyword_lower, 'jatuh tempo')) {
                        $data->where('tbl_payment.payment_status', 0)
                            ->whereRaw("DATE(tbl_payment.payment_date_estimation) = CURDATE()");
                        return;
                    }

                    // ── Sisa Waktu (future)
                    if (str_contains($keyword_lower, 'sisa waktu')) {
                        $data->where('tbl_payment.payment_status', 0)
                            ->whereRaw("DATE(tbl_payment.payment_date_estimation) > CURDATE()");
                        return;
                    }

                    // ── Date fallback — e.g. "25 Aug 2025"
                    $data->where(function ($q) use ($keyword) {
                        $q->whereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d %M %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE(tbl_payment.payment_date_estimation) = STR_TO_DATE(?, '%d %b %Y')", [$keyword])
                            ->orWhereRaw("DATE(tbl_payment.payment_date_estimation) = STR_TO_DATE(?, '%d %M %Y')", [$keyword])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%d/%m/%Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_payment.payment_date_estimation, '%Y-%m-%d') like ?", ["%{$keyword}%"]);
                    });
                })
                ->rawColumns(['detail_pembayaran', 'action', 'payment_date_estimation'])
                ->make(true);
        }

        // Statistics (unchanged)
        $totalVolume = Payment::sum('amount');
        $totalTransactions = Payment::count();
        $lastTransaction = Payment::latest('input_date')->first();

        return view('payment-index', compact('totalVolume', 'totalTransactions', 'lastTransaction'));
    }

    public function create()
    {
        // Fetch invoices that do NOT have a payment yet
        $invoices = \App\Models\Invoice::whereDoesntHave('payment')
            ->with(['po.customer', 'delivery'])
            ->get()
            ->map(function ($invoice) {
                // Calculate invoice amount: delivered quantity * unit price
                $amount = $invoice->delivery->qty_delivered * $invoice->delivery->po->harga;
                $invoice->total_display = $amount;
                $invoice->customer_name = $invoice->po->customer->cust_name ?? 'Unknown';
                return $invoice;
            });

        return view('payment-create', compact('invoices'));
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id|unique:tbl_payment,invoice_id',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0',
            'metode_bayar' => 'required|string',
            'pay_now'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice  = \App\Models\Invoice::findOrFail($request->invoice_id);
            $payNow   = (int) $request->input('pay_now', 0) === 1;
            $status   = $payNow ? 1 : 0;
            $paidDate = $payNow ? now()->toDateString() : null;

            DB::transaction(function () use ($request, $invoice, $status, $paidDate, $payNow) {
                $payment = Payment::create([
                    'invoice_id'              => $invoice->invoice_id,
                    'amount'                  => $request->amount,
                    'metode_bayar'            => $request->metode_bayar,
                    'payment_date_estimation' => $request->payment_date, // form field → correct column
                    'payment_date'            => $paidDate,              // only set if paying now
                    'payment_status'          => $status,
                    'input_by'                => Auth::id(),
                    'input_date'              => now(),
                ]);

                // only mark invoice as paid if paying right now
                if ($payNow == 1) {
                    $invoice->update(['status_invoice' => 1]);
                }

                $this->logCreate(
                    $payment,
                    'Payment ditambahkan ke invoice ' . $invoice->nomor_invoice .
                        ' yang terhubung ke delivery ' . $invoice->delivery->delivery_no .
                        ' dengan PO ' . $invoice->delivery->po->no_po .
                        ' (' . $invoice->delivery->po->nama_barang . ')'
                );
            });

            if ($invoice->po) {
                $invoice->po->syncStatus();
            }

            return response()->json([
                'success'      => true,
                'message'      => 'Pembayaran berhasil direkam.',
                'redirect_url' => route('payment.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($payment_id)
    {
        $payment = Payment::with([
            'invoice',
            'invoice.delivery',
            'invoice.delivery.po',
            'invoice.delivery.po.customer',
            'invoice.delivery.po.input_user',
            'invoice.payment',        // all payments for payment history table
            'input_user',              // recorded by (input_by foreign key)
        ])->findOrFail($payment_id);

        // Extract invoice so the blade can use $invoice as its root,
        // while $payment remains available if needed separately.
        $invoice = $payment->invoice;

        return view('payment-show', compact('payment', 'invoice'));
    }
    /**
     * Delete a payment record and reverse its side effects.
     * Called via AJAX DELETE from the index page.
     */
    public function destroy(Payment $payment)
    {
        try {
            // 1. Grab references BEFORE deleting (they'll be gone after)
            $invoice  = $payment->invoice;
            $delivery = $invoice?->delivery;
            $po       = $delivery?->po;

            $this->logDelete($payment, $payment, 'Payment dengan invoice ' . $payment->invoice->nomor_invoice . ' yang terhubung ke delivery ' . $payment->invoice->delivery->delivery_no . ' dengan PO ' . $payment->invoice->delivery->po->no_po . ' (' . $payment->invoice->delivery->po->nama_barang . ') ' . ' Di hapus');

            // 2. Delete the payment
            $payment->delete();

            // 3. Reverse invoice status back to Unpaid (0) if no other payments exist
            if ($invoice) {
                $remainingPayments = $invoice->payment()->count(); // hasOne, so 0 or 1
                if ($remainingPayments === 0) {
                    $invoice->update(['status_invoice' => 0]);
                }
            }

            // 4. Re-sync PO status up the chain
            if ($po) {
                $po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form to edit an existing payment.
     */

    public function edit(Payment $payment)
    {
        // Pull unpaid invoices + the one already on this payment
        // Uses Eloquent with() — reads column names from YOUR models
        $invoices = Invoice::with(['delivery.po.customer'])
            ->where(function ($q) use ($payment) {
                $q->where('status_invoice', 0)
                    ->orWhere('invoice_id', $payment->invoice_id);
            })
            ->get()
            ->map(function ($inv) {
                return (object) [
                    'invoice_id'    => $inv->invoice_id,
                    'nomor_invoice' => $inv->nomor_invoice,
                    'total_display' => optional($inv->delivery?->po)->total ?? 0,
                    'customer_name' => optional($inv->delivery?->po?->customer)->nama_cust
                        ?? optional($inv->delivery?->po?->customer)->name
                        ?? optional($inv->delivery?->po?->customer)->customer_name
                        ?? '(no customer)',
                    'delivery'      => (object) [
                        'delivery_no' => $inv->delivery?->delivery_no,
                        'po'          => (object) [
                            'no_po'       => $inv->delivery?->po?->no_po,
                            'nama_barang' => $inv->delivery?->po?->nama_barang,
                        ],
                    ],
                ];
            });

        return view('payment-edit', compact('payment', 'invoices'));
    }
    /**
     * Update an existing payment record.
     * Handles PO/Invoice status re-sync on change.
     */
    public function update(Request $request, Payment $payment)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id',
            'amount'       => 'required|numeric|min:0',
            'metode_bayar' => 'required|string|max:100',
            'payment_date' => 'required|date',
            'pay_now'      => 'nullable|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $oldInvoice = $payment->invoice;
            $oldPo      = $oldInvoice?->delivery?->po;
            $oldPayment = $payment->toArray();

            $payNow   = (int) $request->input('pay_now', 0) === 1;
            $status   = $payNow ? 1 : 0;
            $paidDate = $payNow ? now()->toDateString() : null;

            DB::transaction(function () use ($request, $payment, $oldInvoice, $oldPo, $oldPayment, $status, $paidDate, $payNow) {
                $payment->update([
                    'invoice_id'              => $request->invoice_id,
                    'amount'                  => $request->amount,
                    'metode_bayar'            => $request->metode_bayar,
                    'payment_date_estimation' => $request->payment_date,
                    'payment_date'            => $paidDate,
                    'payment_status'          => $status,
                    'edit_by'                 => Auth::id(),
                ]);

                $newPayment = $payment->fresh();

                $this->logUpdate(
                    $newPayment,
                    $oldPayment,
                    'Payment dengan invoice ' . $newPayment->invoice->nomor_invoice .
                        ' yang terhubung ke delivery ' . $newPayment->invoice->delivery->delivery_no .
                        ' dengan PO ' . $newPayment->invoice->delivery->po->no_po .
                        ' (' . $newPayment->invoice->delivery->po->nama_barang . ') Di update'
                );

                // if invoice changed, reverse old invoice back to Unpaid
                if ($oldInvoice && $oldInvoice->invoice_id != $request->invoice_id) {
                    $oldInvoice->update(['status_invoice' => 0]);
                    $oldPo?->syncStatus();
                }

                // mark new invoice as paid only if pay_now ticked
                $newInvoice = $newPayment->invoice;
                if ($newInvoice && $payNow) {
                    $newInvoice->update(['status_invoice' => 1]);
                } elseif ($newInvoice && !$payNow) {
                    $newInvoice->update(['status_invoice' => 0]);
                }
            });

            $payment->fresh()->invoice?->delivery?->po?->syncStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Pembayaran berhasil diperbarui.',
                'redirect_url' => route('payment.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function payNow($id)
    {
        $payment = Payment::findOrFail($id);

        if ((int) $payment->payment_status === 1) {
            return response()->json([
                'message' => 'Pembayaran ini sudah tercatat sebagai lunas.'
            ], 422);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'payment_status' => 1,
                'payment_date'   => now()->toDateString(),
                'edit_by'        => Auth::id(),
            ]);

            // mark the linked invoice as paid too
            if ($payment->invoice) {
                $payment->invoice->update(['status_invoice' => 1]);
            }
        });

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi.'
        ]);
    }
}
