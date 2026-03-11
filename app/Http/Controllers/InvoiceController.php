<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Delivery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class InvoiceController extends Controller
{

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Invoice::query()
                ->leftJoin('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->leftJoin('tbl_payment', 'tbl_invoice.invoice_id', '=', 'tbl_payment.invoice_id')
                ->select([
                    'tbl_invoice.*',
                    'tbl_delivery.delivery_no',
                    'tbl_delivery.qty_delivered',
                    'tbl_delivery.delivered_at',
                    'tbl_delivery.delivered_status',
                    'tbl_delivery.invoiced_status',
                    'tbl_po.no_po',
                    'tbl_po.nama_barang',
                    'tbl_po.harga',
                    'tbl_po.total as po_total',
                    DB::raw('tbl_delivery.qty_delivered * tbl_po.harga as invoice_amount'),
                    'tbl_payment.payment_date',
                    'tbl_payment.amount as paid_amount'
                ]);

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('invoice_details', function ($row) {
                    $invoicedAtDate = Carbon::parse($row->tgl_invoice);
                    $relative        = $invoicedAtDate->toIndonesianRelative();

                    $tenggatWaktu = Carbon::parse($row->due_date);
                    $relative_2        = $tenggatWaktu->toIndonesianRelative();

                    $tglInvoice  = $row->tgl_invoice ? \Carbon\Carbon::parse($row->tgl_invoice) : null;
                    $dueDate     = $row->due_date    ? \Carbon\Carbon::parse($row->due_date)    : null;

                    $tglFormatted   = $tglInvoice ? $tglInvoice->format('d M Y')  : '—';
                    $dueFormatted   = $dueDate    ? $dueDate->format('d M Y')     : '—';

                    // ── Due Date State ──────────────────────────────────────────
                    $isOverdue  = $dueDate && $dueDate->isPast() && (int) $row->status_invoice === 0;
                    $isDueToday = $dueDate && $dueDate->isToday() && (int) $row->status_invoice === 0;
                    $dueBadgeColor = '#10b981'; // default green = fine
                    $dueBadgeLabel = 'On Time';
                    if ($isDueToday) {
                        $dueBadgeColor = '#f59e0b';
                        $dueBadgeLabel = 'Jatuh Tempo Hari Ini';
                    } elseif ($isOverdue) {
                        $dueBadgeColor = '#ef4444';
                        $dueBadgeLabel = 'OVERDUE';
                    }

                    // ── Invoice Status ──────────────────────────────────────────
                    $statusMap = match ((int) $row->status_invoice) {
                        0       => ['label' => 'Unpaid',    'icon' => 'ri-time-line',         'color' => '#ef4444', 'bg' => '#fef2f2', 'border' => '#fecaca'],
                        1       => ['label' => 'Paid',      'icon' => 'ri-checkbox-circle-fill', 'color' => '#10b981', 'bg' => '#f0fdf4', 'border' => '#bbf7d0'],
                        2       => ['label' => 'Cancelled', 'icon' => 'ri-close-circle-line',  'color' => '#9ca3af', 'bg' => '#f9fafb', 'border' => '#e5e7eb'],
                        default => ['label' => 'Unknown',   'icon' => 'ri-question-line',      'color' => '#9ca3af', 'bg' => '#f9fafb', 'border' => '#e5e7eb'],
                    };

                    return '
                        <style>
                            .inv-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 230px;
                                max-width: 270px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .inv-card-header {
                                padding: 10px 14px 8px;
                                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                                border-bottom: 1px solid #e5e7eb;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .inv-header-icon {
                                width: 28px;
                                height: 28px;
                                background: #ede9fe;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.85rem;
                                color: #7c3aed;
                                flex-shrink: 0;
                            }
                            .inv-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .inv-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .inv-row-icon {
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
                            .inv-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .inv-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .inv-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .inv-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .inv-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                margin-top: 2px;
                            }
                            .inv-status-footer {
                                padding: 8px 14px 10px;
                                background: ' . $statusMap['bg'] . ';
                                border-top: 1.5px solid ' . $statusMap['border'] . ';
                                display: flex;
                                flex-direction: column;
                                gap: 5px;
                            }
                            .inv-status-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 600;
                                letter-spacing: 0.01em;
                                border: 1.5px solid;
                                width: fit-content;
                            }
                            .inv-due-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 4px;
                                padding: 3px 8px;
                                border-radius: 20px;
                                font-size: 0.62rem;
                                font-weight: 700;
                                border: 1.5px solid;
                                width: fit-content;
                            }
                        </style>
                        <div class="inv-card shadow-lg">

                            <!-- Header: Nomor Invoice -->
                            <div class="inv-card-header">
                                <div class="inv-header-icon"><i class="ri-file-text-line"></i></div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">Nomor Invoice</div>
                                    <div style="font-size:0.8rem;font-weight:700;color:#1e293b;">' . e($row->nomor_invoice ?? '—') . '</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="inv-card-body">

                                <!-- Tanggal Invoice -->
                                <div class="inv-row">
                                    <div class="inv-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-file-add-line"></i>
                                    </div>
                                    <div class="inv-row-content">
                                        <span class="inv-row-label">Tanggal Invoice</span>
                                        <span class="inv-row-value">' . $tglFormatted . '</span>
                                        <span class="del-relative-badge">' . $relative . '</span>
                                    </div>
                                </div>

                                <div class="inv-divider"></div>

                                <!-- Due Date -->
                                <div class="inv-row">
                                    <div class="inv-row-icon" style="background:' . ($isOverdue ? '#fee2e2' : ($isDueToday ? '#fef3c7' : '#dcfce7')) . ';color:' . $dueBadgeColor . ';">
                                        <i class="ri-calendar-close-line"></i>
                                    </div>
                                    <div class="inv-row-content">
                                        <span class="inv-row-label">Tenggat Waktu</span>
                                        <span class="inv-row-value">' . $dueFormatted . '</span>
                                        <span class="del-relative-badge">' . $relative_2 . '</span>
                                        ' . ((int) $row->status_invoice === 0 ? '
                                        <span class="inv-badge" style="background:' . $dueBadgeColor . '18;color:' . $dueBadgeColor . ';border:1px solid ' . $dueBadgeColor . '40;">
                                            ' . ($isOverdue ? '<i class="ri-alarm-warning-line me-1"></i>' : '<i class="ri-time-line me-1"></i>') . $dueBadgeLabel . '
                                        </span>' : '') . '
                                    </div>
                                </div>

                                <!-- Tagihan -->
                                <div class="inv-row">
                                    <div class="inv-row-icon text-success">
                                        <i class="ri-money-dollar-circle-line"></i>
                                    </div>
                                    <div class="inv-row-content">
                                        <span class="inv-row-label">Tagihan</span>
                                        <span class="inv-row-value text-success">Rp ' . number_format($row->invoice_amount ?? 0, 0, ',', '.') . '</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Footer -->
                            <div class="inv-status-footer">
                                <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:2px;">Status Invoice</div>

                                <span class="inv-status-chip" style="background:' . $statusMap['color'] . '18;color:' . $statusMap['color'] . ';border-color:' . $statusMap['color'] . '40;">
                                    <i class="' . $statusMap['icon'] . '" style="font-size:0.85rem;"></i>
                                    ' . $statusMap['label'] . '
                                </span>
                                ' . ($isOverdue ? '
                                <span class="inv-due-chip" style="background:#ef444418;color:#ef4444;border-color:#ef444440;">
                                    <i class="ri-error-warning-line"></i> Overdue
                                </span>' : '') . '
                            </div>

                        </div>
                        ';
                })
                ->addColumn('delivery_details', function ($row) {
                    $deliveredAtDate = Carbon::parse($row->delivered_at);
                    $relative        = $deliveredAtDate->toIndonesianRelative();
                    $deliveredAt     = $row->delivered_at
                        ? Carbon::parse($row->delivered_at)->format('d M Y')
                        : '—';

                    // ── Delivered Status ─────────────────────────────────
                    $deliveredStatus = match ((int) $row->delivered_status) {
                        0       => ['label' => 'Dalam Perjalanan',  'icon' => 'ri-time-line',    'color' => '#f59e0b'],
                        1       => ['label' => 'Sudah Tiba Tujuan', 'icon' => 'ri-truck-line',   'color' => '#0ea5e9'],
                        default => ['label' => 'Unknown',           'icon' => 'ri-question-line', 'color' => '#9ca3af'],
                    };

                    // ── Invoiced Status ───────────────────────────────────
                    $invoicedStatus = match ((int) $row->invoiced_status) {
                        0       => ['label' => 'Belum Di Invoice', 'icon' => 'ri-file-forbid-line', 'color' => '#ef4444'],
                        1       => ['label' => 'Sudah Di Invoice', 'icon' => 'ri-file-check-line',  'color' => '#3b82f6'],
                        default => ['label' => 'Unknown',          'icon' => 'ri-question-line',    'color' => '#9ca3af'],
                    };

                    return '
                        <style>
                            .del-card {
                                display: flex;
                                flex-direction: column;
                                gap: 0;
                                min-width: 220px;
                                max-width: 260px;
                                background: #ffffff;
                                border: 1px solid #e5e7eb;
                                border-radius: 12px;
                                overflow: hidden;
                                box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                font-family: inherit;
                            }
                            .del-card-header {
                                padding: 10px 14px 8px;
                                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                                border-bottom: 1px solid #e5e7eb;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .del-header-icon {
                                width: 28px;
                                height: 28px;
                                background: #dbeafe;
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.85rem;
                                color: #3b82f6;
                                flex-shrink: 0;
                            }
                            .del-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .del-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .del-row-icon {
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
                            .del-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .del-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .del-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .del-relative-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                background: #e0f2fe;
                                color: #0369a1;
                                margin-top: 2px;
                            }
                            .del-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .del-status-footer {
                                padding: 8px 14px 10px;
                                background: #fafafa;
                                border-top: 1px solid #f1f5f9;
                                display: flex;
                                flex-direction: column;
                                gap: 5px;
                            }
                            .del-status-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 600;
                                letter-spacing: 0.01em;
                                border: 1.5px solid;
                                width: fit-content;
                            }
                        </style>
                        <div class="del-card shadow-lg">

                            <!-- Header: Delivery No -->
                            <div class="del-card-header">
                                <div class="del-header-icon"><i class="ri-truck-line"></i></div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">No. Delivery</div>
                                    <div style="font-size:0.8rem;font-weight:700;color:#1e293b;">' . e($row->delivery_no) . '</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="del-card-body">

                                <!-- Qty Delivered -->
                                <div class="del-row">
                                    <div class="del-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-stack-line"></i>
                                    </div>
                                    <div class="del-row-content">
                                        <span class="del-row-label">Qty Delivered</span>
                                        <span class="del-row-value">' . number_format((float) $row->qty_delivered, 0, ',', '.') . ' Unit</span>
                                    </div>
                                </div>

                                <div class="del-divider"></div>

                                <!-- Tiba -->
                                <div class="del-row">
                                    <div class="del-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="del-row-content">
                                        <span class="del-row-label">Tiba</span>
                                        <span class="del-row-value">' . $deliveredAt . '</span>
                                        <span class="del-relative-badge">' . $relative . '</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Status Footer -->
                            <div class="del-status-footer">
                                <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:2px;">Status Delivery</div>

                                <span class="del-status-chip" style="background:' . $deliveredStatus['color'] . '18;color:' . $deliveredStatus['color'] . ';border-color:' . $deliveredStatus['color'] . '40;">
                                    <i class="' . $deliveredStatus['icon'] . '" style="font-size:0.8rem;"></i>
                                    ' . $deliveredStatus['label'] . '
                                </span>

                                <span class="del-status-chip" style="background:' . $invoicedStatus['color'] . '18;color:' . $invoicedStatus['color'] . ';border-color:' . $invoicedStatus['color'] . '40;">
                                    <i class="' . $invoicedStatus['icon'] . '" style="font-size:0.8rem;"></i>
                                    ' . $invoicedStatus['label'] . '
                                </span>
                            </div>

                        </div>
                        ';
                })
                ->addColumn('due_date_timer', function ($row) {
                    // ── STATE 1: PAID ─────────────────────────────────────────
                    if ((int) $row->status_invoice === 1) {
                        return '
                            <style>
                                .ddt-card {
                                    display: inline-flex;
                                    flex-direction: column;
                                    gap: 0;
                                    min-width: 190px;
                                    background: #ffffff;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                    font-family: inherit;
                                }
                                .ddt-card-body {
                                    padding: 10px 14px;
                                    display: flex;
                                    align-items: center;
                                    gap: 10px;
                                }
                                .ddt-icon-wrap {
                                    width: 32px;
                                    height: 32px;
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 1rem;
                                    flex-shrink: 0;
                                }
                                .ddt-label {
                                    font-size: 0.6rem;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    color: #94a3b8;
                                }
                                .ddt-value {
                                    font-size: 0.82rem;
                                    font-weight: 700;
                                    color: #1e293b;
                                    line-height: 1.3;
                                }
                                .ddt-footer {
                                    padding: 5px 14px 8px;
                                    border-top: 1.5px solid;
                                }
                                .ddt-chip {
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
                            <div class="ddt-card shadow-lg">
                                <div class="ddt-card-body">
                                    <div class="ddt-icon-wrap" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-check-double-line"></i>
                                    </div>
                                    <div class="d-flex flex-column gap-0">
                                        <span class="ddt-label">Status Pembayaran</span>
                                        <span class="ddt-value">Lunas</span>
                                    </div>
                                </div>
                                <div class="ddt-footer" style="background:#f0fdf4;border-color:#bbf7d0;">
                                    <span class="ddt-chip" style="background:#10b98118;color:#10b981;border-color:#10b98140;">
                                        <i class="ri-checkbox-circle-fill" style="font-size:0.8rem;"></i>
                                        Sudah Dibayar
                                    </span>
                                </div>
                            </div>';
                    }

                    // ── STATE 2: NO DUE DATE ──────────────────────────────────
                    if (!$row->due_date) {
                        return '
                            <div class="ddt-card shadow-lg">
                                <div class="ddt-card-body">
                                    <div class="ddt-icon-wrap" style="background:#f1f5f9;color:#94a3b8;">
                                        <i class="ri-calendar-2-line"></i>
                                    </div>
                                    <div class="d-flex flex-column gap-0">
                                        <span class="ddt-label">Tenggat Waktu</span>
                                        <span class="ddt-value" style="color:#94a3b8;">Tidak Ditentukan</span>
                                    </div>
                                </div>
                                <div class="ddt-footer" style="background:#f9fafb;border-color:#e5e7eb;">
                                    <span class="ddt-chip" style="background:#9ca3af18;color:#9ca3af;border-color:#9ca3af40;">
                                        <i class="ri-minus-circle-line" style="font-size:0.8rem;"></i>
                                        Tidak Ada Tenggat
                                    </span>
                                </div>
                            </div>';
                    }

                    // ── STATE 3: COUNTDOWN / OVERDUE ─────────────────────────
                    $dueDate          = \Carbon\Carbon::parse($row->due_date);
                    $isOverdue        = $dueDate->isPast();
                    $isDueToday       = $dueDate->isToday();
                    $dueDateFormatted = $dueDate->format('d M Y');
                    $isoTarget        = $dueDate->toISOString();

                    if ($isOverdue) {
                        $headerBg     = 'linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%)';
                        $headerBorder = '#fecaca';
                        $iconBg       = '#fee2e2';
                        $iconColor    = '#ef4444';
                        $headerIcon   = 'ri-alarm-warning-line';
                        $headerLabel  = 'Tenggat Waktu';
                        $headerColor  = '#7f1d1d';
                        $timerColor   = '#ef4444';
                    } elseif ($isDueToday) {
                        $headerBg     = 'linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%)';
                        $headerBorder = '#fde68a';
                        $iconBg       = '#fde68a';
                        $iconColor    = '#d97706';
                        $headerIcon   = 'ri-alarm-line';
                        $headerLabel  = 'Jatuh Tempo';
                        $headerColor  = '#78350f';
                        $timerColor   = '#d97706';
                    } else {
                        $headerBg     = 'linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)';
                        $headerBorder = '#e5e7eb';
                        $iconBg       = '#e0f2fe';
                        $iconColor    = '#0284c7';
                        $headerIcon   = 'ri-calendar-check-line';
                        $headerLabel  = 'Tenggat Waktu';
                        $headerColor  = '#1e293b';
                        $timerColor   = '#0284c7';
                    }

                    return '
                        <div class="ddt-card timer-wrapper shadow-lg" data-target="' . $isoTarget . '">

                            <!-- Header: Due Date -->
                            <div style="padding:10px 14px 8px;background:' . $headerBg . ';border-bottom:1px solid ' . $headerBorder . ';display:flex;align-items:center;gap:8px;">
                                <div style="width:28px;height:28px;background:' . $iconBg . ';border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:0.85rem;color:' . $iconColor . ';flex-shrink:0;">
                                    <i class="' . $headerIcon . '"></i>
                                </div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">' . $headerLabel . '</div>
                                    <div style="font-size:0.78rem;font-weight:700;color:' . $headerColor . ';">' . $dueDateFormatted . '</div>
                                </div>
                            </div>

                            <!-- Countdown Body -->
                            <div style="padding:10px 14px;display:flex;flex-direction:column;gap:2px;">
                                <span style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">
                                    ' . ($isOverdue ? 'Telah Lewat' : 'Sisa Waktu') . '
                                </span>
                                <span class="countdown-display invoice-timer"
                                    style="font-size:0.95rem;font-weight:800;color:' . $timerColor . ';letter-spacing:0.03em;font-variant-numeric:tabular-nums;">
                                    ' . ($isOverdue ? 'JATUH TEMPO' : 'Calculating...') . '
                                </span>
                            </div>
                        </div>';
                })
                ->addColumn('action', function ($row) {
                    // Helper to prevent crash if route is missing (optional safety)
                    $showUrl = Route::has('invoice.show') ? route('invoice.show', $row->invoice_id) : '#';
                    $editUrl = Route::has('invoice.edit') ? route('invoice.edit', $row->invoice_id) : '#';
                    $deleteUrl = Route::has('invoice.destroy') ? route('invoice.destroy', $row->invoice_id) : '#';

                    $user = Auth::user();
                    $canEditDelete = $user && $user->role_id !== 2;

                    $editBtn = $canEditDelete ? '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-label-warning" title="Edit">
                        <i class="ri-pencil-line"></i>
                    </a>' : '';

                    $deleteBtn = $canEditDelete ? '
                    <button type="button" class="btn btn-sm btn-icon btn-label-danger btn-delete-ajax" 
                        data-url="' . $deleteUrl . '" 
                        data-po="No delivery ' . $row->delivery->delivery_no . ' yang terkait PO ' . $row->delivery->po->nama_barang . ' (' . $row->delivery->po->no_po . ')" 
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
                ->orderColumn('delivery_details', 'delivered_at $1')
                ->filterColumn('delivery_details', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));
                    $numericClean  = preg_replace('/[^0-9]/', '', $keyword);

                    if (str_contains('dalam perjalanan', $keyword_lower)) {
                        $data->where('delivered_status', 0);
                        return;
                    }

                    if (str_contains('sudah tiba tujuan', $keyword_lower)) {
                        $data->where('delivered_status', 1);
                        return;
                    }

                    if (str_contains('belum di invoice', $keyword_lower)) {
                        $data->where('invoiced_status', 0);
                        return;
                    }

                    if (str_contains('sudah di invoice', $keyword_lower)) {
                        $data->where('invoiced_status', 1);
                        return;
                    }

                    $data->where(function ($q) use ($keyword, $numericClean) {
                        $q->where('delivery_no', 'like', "%{$keyword}%")
                            ->orWhereRaw("CAST(qty_delivered AS CHAR) like ?", ["%{$numericClean}%"])
                            ->orWhereRaw("CONCAT(qty_delivered, ' Unit') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(delivered_at, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(delivered_at, '%d %M %Y') like ?", ["%{$keyword}%"]);
                    });
                })
                ->orderColumn('invoice_details', 'tgl_invoice $1')
                ->filterColumn('invoice_details', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));
                    $numericClean  = preg_replace('/[^0-9]/', '', $keyword);

                    // ── Invoice Status
                    if (str_contains('unpaid', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 0);
                        return;
                    }

                    if (str_contains('paid', $keyword_lower) && !str_contains('unpaid', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 1);
                        return;
                    }

                    if (str_contains('cancelled', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 2);
                        return;
                    }

                    // ── Due Date State
                    if (str_contains('jatuh tempo hari ini', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 0)
                            ->whereRaw("DATE(tbl_invoice.due_date) = CURDATE()");
                        return;
                    }

                    if (str_contains('overdue', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 0)
                            ->whereRaw("tbl_invoice.due_date < NOW()");
                        return;
                    }

                    if (str_contains('on time', $keyword_lower)) {
                        $data->where('tbl_invoice.status_invoice', 0)
                            ->whereRaw("tbl_invoice.due_date > NOW()");
                        return;
                    }

                    $data->where(function ($q) use ($keyword, $numericClean) {

                        // ── Nomor Invoice
                        $q->where('tbl_invoice.nomor_invoice', 'like', "%{$keyword}%")

                            // ── Tanggal Invoice
                            ->orWhereRaw("DATE_FORMAT(tbl_invoice.tgl_invoice, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_invoice.tgl_invoice, '%d %M %Y') like ?", ["%{$keyword}%"])

                            // ── Due Date
                            ->orWhereRaw("DATE_FORMAT(tbl_invoice.due_date, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_invoice.due_date, '%d %M %Y') like ?", ["%{$keyword}%"]);

                        // ── Invoice Amount — use raw formula instead of alias
                        if (!empty($numericClean)) {
                            $q->orWhereRaw("CAST((tbl_delivery.qty_delivered * tbl_po.harga) AS CHAR) like ?", ["%{$numericClean}%"]);
                        }
                    });
                })
                ->orderColumn('due_date_timer', 'tbl_invoice.status_invoice $1')
                ->filterColumn('due_date_timer', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));

                    // ── Paid / Lunas / Sudah Dibayar
                    if (
                        str_contains($keyword_lower, 'paid') ||
                        str_contains($keyword_lower, 'lunas') ||
                        str_contains($keyword_lower, 'sudah dibayar')
                    ) {
                        $data->where('tbl_invoice.status_invoice', 1);
                        return;
                    }

                    // ── Unpaid / Belum Dibayar
                    if (
                        str_contains($keyword_lower, 'unpaid') ||
                        str_contains($keyword_lower, 'belum dibayar')
                    ) {
                        $data->where('tbl_invoice.status_invoice', 0);
                        return;
                    }

                    // ── Tidak Ada Tenggat / Tidak Ditentukan
                    if (
                        str_contains($keyword_lower, 'tidak ada tenggat') ||
                        str_contains($keyword_lower, 'tidak ditentukan')
                    ) {
                        $data->whereNull('tbl_invoice.due_date');
                        return;
                    }

                    // ── Telah Lewat / Overdue / Jatuh Tempo
                    if (
                        str_contains($keyword_lower, 'telah lewat') ||
                        str_contains($keyword_lower, 'overdue') ||
                        str_contains($keyword_lower, 'jatuh tempo')
                    ) {
                        $data->where('tbl_invoice.status_invoice', 0)
                            ->whereNotNull('tbl_invoice.due_date')
                            ->whereRaw("tbl_invoice.due_date < NOW()");
                        return;
                    }

                    // ── Sisa Waktu
                    if (str_contains($keyword_lower, 'sisa waktu')) {
                        $data->where('tbl_invoice.status_invoice', 0)
                            ->whereNotNull('tbl_invoice.due_date')
                            ->whereRaw("tbl_invoice.due_date >= NOW()");
                        return;
                    }

                    // ── Date fallback — e.g. "23 Jul 2025"
                    $data->where(function ($q) use ($keyword) {
                        $q->whereRaw("DATE_FORMAT(tbl_invoice.due_date, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tbl_invoice.due_date, '%d %M %Y') like ?", ["%{$keyword}%"]);
                    });
                })
                ->rawColumns(['invoice_details', 'delivery_details', 'due_date_timer', 'action'])
                ->make(true);
        }

        // Stats Calculation (unchanged)
        $stats = [
            'total'          => Invoice::count(),
            'unpaid_count'   => Invoice::where('status_invoice', 0)->count(),
            'unpaid_amount'  => Invoice::where('status_invoice', 0)
                ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->join('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->sum('tbl_po.total'),
            'paid_amount'    => Invoice::where('status_invoice', 1)
                ->join('tbl_delivery', 'tbl_invoice.delivery_id', '=', 'tbl_delivery.delivery_id')
                ->join('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->sum('tbl_po.total'),
            'paid_count'     => Invoice::where('status_invoice', 1)->count(),
            'overdue_count'  => Invoice::where('status_invoice', 0)
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('invoice-index', compact('stats'));
    }
    public function create()
    {
        // Only fetch deliveries that DO NOT have an invoice record yet
        $deliveries = Delivery::whereDoesntHave('invoice')
            ->with(['po.customer'])
            ->get();

        return view('invoice-create', compact('deliveries'));
    }

    // InvoiceController.php

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_id' => 'required|exists:tbl_delivery,delivery_id|unique:tbl_invoice,delivery_id',
            'tgl_invoice' => 'required|date',
            'due_date'    => 'required|date|after:tgl_invoice',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $delivery = Delivery::with('po')->findOrFail($request->delivery_id);

            $invoiceNumber = 'INV-' . now('Asia/Jakarta')->format('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'delivery_id'    => $delivery->delivery_id,
                'tgl_invoice'    => $request->tgl_invoice,
                'nomor_invoice'  => $invoiceNumber,
                'due_date'       => $request->due_date,
                'status_invoice' => $request->pay_now == 1 ? 1 : 0,
                'input_date'     => now('Asia/Jakarta'),
                'input_by'       => Auth::id() ?? 1,
            ]);

            $this->logCreate($invoice, 'Invoice dengan nomor invoice ' . $invoiceNumber . ' yang terhubung ke delivery ' . $invoice->delivery->delivery_no . ' dengan PO ' . $invoice->delivery->po->no_po . ' (' . $invoice->delivery->po->nama_barang . ') ' . ' Di tambahkan');

            // ← create payment if pay_now checkbox is checked
            if ($request->has('pay_now') && $request->pay_now == 1) {
                Payment::create([
                    'invoice_id'               => $invoice->invoice_id,
                    'payment_date'             => now('Asia/Jakarta'),
                    'payment_date_estimation'  => $request->due_date,
                    'payment_status'           => $request->pay_now == 1 ? 1 : 0,
                    'amount'                   => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'             => null,
                    'bukti_bayar'              => null,
                    'description'              => 'Pembayaran langsung saat pembuatan invoice ' . $invoiceNumber,
                    'input_by'                 => Auth::id() ?? 1,
                    'input_date'               => now('Asia/Jakarta'),
                ]);
            }

            $invoice->syncInvoiceStatus();
            $invoice->delivery->syncInvoicedStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Invoice created successfully',
                'redirect_url' => route('invoice.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice: ' . $e->getMessage()
            ], 500);
        }
    }
    public function edit($id)
    {
        $item = Invoice::findOrFail($id);
        $currentDelivery = Delivery::findOrFail($item->delivery_id);
        $deliveries = Delivery::with('po')->get();

        return view('invoice-edit', compact('item', 'currentDelivery', 'deliveries'));
    }

    public function update(Request $request, $id)
    {
        $item = Invoice::findOrFail($id);

        $request->validate([
            'delivery_id' => 'sometimes|exists:tbl_delivery,delivery_id',
            'tgl_invoice' => 'required|date',
            'due_date'    => 'required|date|after:tgl_invoice',
        ]);

        $oldInvoice    = $item->toArray();
        $oldDeliveryModel = $item->delivery;

        $item->update([
            'delivery_id'    => $request->filled('delivery_id') ? $request->delivery_id : $item->delivery_id,
            'tgl_invoice'    => $request->tgl_invoice,
            'due_date'       => $request->due_date,
            'status_invoice' => $request->pay_now == 1 ? 1 : 0,
            'edit_by'        => Auth::id() ?? 1,
            'edit_date'      => now('Asia/Jakarta'),
        ]);

        $oldDeliveryModel->syncInvoicedStatus();

        if ($request->pay_now == 0) {
            $oldDeliveryModel->invoice->payment->delete();
        }

        if ($request->pay_now == 1) {
            if ($item->payment()->exists()) {
                $delivery = $item->fresh()->delivery;
                $delivery->syncInvoicedStatus();
                $payment = $item->fresh()->payment;

                $payment->update([
                    'payment_date'            => now('Asia/Jakarta'),
                    'payment_date_estimation' => $request->due_date,
                    'payment_status'          => $request->pay_now == 1 ? 1 : 0,
                    'amount'                  => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'            => null,
                    'bukti_bayar'             => null,
                    'description'             => 'Pembayaran langsung saat update invoice ' . $item->nomor_invoice,
                    'input_by'                => Auth::id() ?? 1,
                    'input_date'              => now('Asia/Jakarta'),
                ]);

                $item->fresh()->syncInvoiceStatus();
            }

            if (!$item->payment()->exists()) {
                $delivery = $item->fresh()->delivery;
                $delivery->syncInvoicedStatus();
                $invoiceNumber = 'INV-' . now('Asia/Jakarta')->format('Ymd') . '-' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);

                Payment::create([
                    'invoice_id'               => $item->fresh()->invoice_id,
                    'payment_date'             => now('Asia/Jakarta'),
                    'payment_date_estimation'  => $item->fresh()->due_date,
                    'payment_status'           => $item->fresh()->pay_now == 1 ? 1 : 0,
                    'amount'                   => $delivery->po->harga * $delivery->qty_delivered ?? 0,
                    'metode_bayar'             => null,
                    'bukti_bayar'              => null,
                    'description'              => 'Pembayaran langsung saat pembuatan invoice ' . $invoiceNumber,
                    'input_by'                 => Auth::id() ?? 1,
                    'input_date'               => now('Asia/Jakarta'),
                ]);

                $item->fresh()->syncInvoiceStatus();
            }
        }

        $newInvoice = $item->fresh();
        $this->logUpdate($newInvoice, $oldInvoice, 'Invoice dengan nomor invoice ' . $item->nomor_invoice . ' yang terhubung ke delivery ' . $item->delivery->delivery_no . ' dengan PO ' . $item->delivery->po->no_po . ' (' . $item->delivery->po->nama_barang . ') Di update');

        return response()->json([
            'success'      => true,
            'message'      => 'Invoice berhasil diperbarui.',
            'redirect_url' => route('invoice.index'),
        ]);
    }

    public function destroy($id)
    {
        $invoice = Invoice::findOrFail($id);
        $delivery = $invoice->delivery; // ← save before deletion

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $invoice->payment()->delete(); // ← payments() hasMany, safe even if none exist
        $invoice->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ← only sync if delivery still exists
        if ($delivery) {
            $delivery->syncInvoicedStatus();
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice berhasil dihapus.',
        ]);
    }
    public function show($invoice_id)
    {
        $invoice = Invoice::with([
            'delivery.po.customer',
            'payment' // Singular
        ])->findOrFail($invoice_id);

        return view('invoice-show', compact('invoice'));
    }
}
