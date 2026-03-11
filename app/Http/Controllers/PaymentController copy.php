<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables; // Ensure you have yajra/laravel-datatables installed
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;

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
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_delivery.qty_delivered',
                    'tbl_delivery.delivery_no'
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('detail_pembayaran', function ($row) {
                    $paymentDate      = \Carbon\Carbon::parse($row->payment_date);
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
                                <div style="padding:5px 14px 8px;background:#fef2f2;border-top:1.5px solid #fecaca;display:flex;align-items:center;justify-content:space-between;">
                                    <span class="pmt-chip" style="background:#ef444418;color:#ef4444;border-color:#ef444440;">
                                        <i class="ri-error-warning-line" style="font-size:0.8rem;"></i>
                                        Lewat Estimasi
                                    </span>
                                    <span style="font-size:0.65rem;font-weight:600;color:#94a3b8;">
                                        <i class="ri-calendar-line me-1"></i>' . $dateFormatted . '
                                    </span>
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
                            </div>

                        </div>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('payment.show') ? route('payment.show', $row->payment_id) : '#';
                    $editUrl = Route::has('payment.edit') ? route('payment.edit', $row->payment_id) : '#';
                    $deleteUrl = Route::has('payment.destroy') ? route('payment.destroy', $row->payment_id) : '#';

                    return '
                        <div class="d-flex align-items-center gap-2">
                            <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-label-info" title="Details">
                                <i class="ri-eye-line"></i>
                            </a>
                            <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                                <i class="ri-pencil-line"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                                data-url="' . $deleteUrl . '" 
                                title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>';
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $invoice = \App\Models\Invoice::with('po')->findOrFail($request->invoice_id);

            DB::transaction(function () use ($request, $invoice) {
                $payment = Payment::create([
                    'invoice_id'     => $invoice->invoice_id,
                    'po_id'          => $invoice->po_id,
                    'payment_date'   => $request->payment_date,
                    'amount'         => $request->amount,
                    'metode_bayar'   => $request->metode_bayar,
                    'status_invoice' => 1,
                    'input_by'       => auth()->id() ?? 1,
                    'input_date'     => now(),
                ]);

                $invoice->update(['status_invoice' => 1]);
                $this->logCreate($payment, 'Payment ditambahkan ke invoice ' . $payment->invoice->nomor_invoice . ' yang terhubung ke delivery ' . $payment->invoice->delivery->delivery_no . ' dengan PO ' . $payment->invoice->delivery->po->no_po . ' (' . $payment->invoice->delivery->po->nama_barang . ')');
            });

            // Trigger PO status update
            if ($invoice->po) {
                $invoice->po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil direkam.',
                'redirect_url' => route('payment.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
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
        $validated = $request->validate([
            'invoice_id'   => 'required|exists:tbl_invoice,invoice_id',
            'amount'       => 'required|numeric|min:1',
            'metode_bayar' => 'required|string|max:100',
            'payment_date' => 'required|date',
        ]);

        try {
            // 1. Grab the OLD invoice before we change anything
            $oldInvoice  = $payment->invoice;
            $oldDelivery = $oldInvoice?->delivery;
            $oldPo       = $oldDelivery?->po;

            $oldPayment = $payment->toArray();

            // 2. Save updated payment
            $payment->update($validated);

            $newPayment = $payment->fresh();

            $this->logUpdate($newPayment, $oldPayment, 'Payment dengan invoice ' . $payment->invoice->nomor_invoice . ' yang terhubung ke delivery ' . $payment->invoice->delivery->delivery_no . ' dengan PO ' . $payment->invoice->delivery->po->no_po . ' (' . $payment->invoice->delivery->po->nama_barang . ') ' . ' Di update');

            // 3. If the invoice_id CHANGED, reverse the old invoice back to Unpaid
            if ($oldInvoice && $oldInvoice->invoice_id != $validated['invoice_id']) {
                $oldInvoice->update(['status_invoice' => 0]);
                $oldPo?->syncStatus();
            }

            // 4. Mark the NEW (or same) invoice as Paid and re-sync its PO
            $newInvoice = $payment->fresh()->invoice;
            if ($newInvoice) {
                $newInvoice->update(['status_invoice' => 1]);
                $newInvoice->delivery?->po?->syncStatus();
            }

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
}
