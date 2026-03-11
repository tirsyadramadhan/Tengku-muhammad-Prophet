<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\Po;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // 1. Join with tbl_po to access PO fields for sorting/searching
            $data = Delivery::query()
                ->leftJoin('tbl_po', 'tbl_delivery.po_id', '=', 'tbl_po.po_id')
                ->select(
                    'tbl_delivery.*',
                    'tbl_po.no_po',
                    'tbl_po.tgl_po',
                    'tbl_po.nama_barang',
                    'tbl_po.qty',
                    'tbl_po.status'
                );

            return DataTables::of($data)
                ->addIndexColumn()
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
                ->editColumn('detail_po', function ($row) {
                    $statusVal = $row->status;
                    $statusMap = [
                        1 => ['label' => 'Open',                                    'class' => 'status-open',       'icon' => 'ri-mail-send-line',        'color' => '#f59e0b'],
                        2 => ['label' => 'Partially Delivered',                     'class' => 'status-partial',    'icon' => 'ri-truck-line',            'color' => '#3b82f6'],
                        3 => ['label' => 'Fully Delivered',                         'class' => 'status-delivered',  'icon' => 'ri-checkbox-circle-line',  'color' => '#3b82f6'],
                        4 => ['label' => 'Partially Del. & Partially Inv.',         'class' => 'status-mixed',      'icon' => 'ri-exchange-box-line',     'color' => '#8b5cf6'],
                        5 => ['label' => 'Fully Del. & Partially Inv.',             'class' => 'status-mixed',      'icon' => 'ri-draft-line',            'color' => '#8b5cf6'],
                        6 => ['label' => 'Partially Del. & Fully Inv.',             'class' => 'status-mixed',      'icon' => 'ri-file-warning-line',     'color' => '#8b5cf6'],
                        7 => ['label' => 'Fully Delivered & Fully Invoiced',        'class' => 'status-complete',   'icon' => 'ri-check-double-line',     'color' => '#10b981'],
                        8 => ['label' => 'Closed',                                  'class' => 'status-closed',     'icon' => 'ri-verified-badge-fill',   'color' => '#10b981'],
                    ];
                    $default = ['label' => 'Unknown', 'class' => 'status-unknown', 'icon' => 'ri-question-line', 'color' => '#9ca3af'];
                    $map = $statusMap[$statusVal] ?? $default;

                    $tglPo    = Carbon::parse($row->tgl_po);
                    $relative = $tglPo->toIndonesianRelative();
                    $formated = $tglPo->format('d M Y');

                    return '
                        <style>
                            .po-card {
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
                            .po-card-header {
                                padding: 10px 14px 8px;
                                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                                border-bottom: 1px solid #e5e7eb;
                                display: flex;
                                align-items: center;
                                gap: 8px;
                            }
                            .po-card-header .po-number {
                                font-size: 0.8rem;
                                font-weight: 700;
                                color: #1e293b;
                                letter-spacing: 0.02em;
                            }
                            .po-card-header .po-icon {
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
                            .po-card-body {
                                padding: 10px 14px;
                                display: flex;
                                flex-direction: column;
                                gap: 8px;
                            }
                            .po-row {
                                display: flex;
                                align-items: flex-start;
                                gap: 8px;
                            }
                            .po-row-icon {
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
                            .po-row-content {
                                display: flex;
                                flex-direction: column;
                                gap: 1px;
                            }
                            .po-row-label {
                                font-size: 0.6rem;
                                font-weight: 600;
                                text-transform: uppercase;
                                letter-spacing: 0.06em;
                                color: #94a3b8;
                            }
                            .po-row-value {
                                font-size: 0.82rem;
                                font-weight: 600;
                                color: #1e293b;
                                line-height: 1.3;
                            }
                            .po-relative-badge {
                                display: inline-block;
                                font-size: 0.62rem;
                                font-weight: 600;
                                padding: 1px 7px;
                                border-radius: 20px;
                                background: #e0f2fe;
                                color: #0369a1;
                                margin-top: 2px;
                            }
                            .po-divider {
                                height: 1px;
                                background: #f1f5f9;
                                margin: 0 -14px;
                            }
                            .po-status-row {
                                padding: 8px 14px 10px;
                                background: #fafafa;
                                border-top: 1px solid #f1f5f9;
                            }
                            .po-status-chip {
                                display: inline-flex;
                                align-items: center;
                                gap: 5px;
                                padding: 4px 10px 4px 7px;
                                border-radius: 20px;
                                font-size: 0.72rem;
                                font-weight: 600;
                                letter-spacing: 0.01em;
                                border: 1.5px solid;
                            }
                        </style>
                        <div class="po-card shadow-lg">
                            <!-- Header: No PO -->
                            <div class="po-card-header">
                                <div class="po-icon"><i class="ri-file-list-3-line"></i></div>
                                <div>
                                    <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;">No. PO</div>
                                    <div class="po-number">' . e($row->no_po) . '</div>
                                </div>
                            </div>

                            <!-- Body -->
                            <div class="po-card-body">

                                <!-- Tanggal PO -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-calendar-check-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Tanggal PO</span>
                                        <span class="po-row-value">' . $formated . '</span>
                                        <span class="po-relative-badge">' . $relative . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Nama Barang -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#ede9fe;color:#7c3aed;">
                                        <i class="ri-box-3-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Nama Barang</span>
                                        <span class="po-row-value">' . e($row->nama_barang) . '</span>
                                    </div>
                                </div>

                                <div class="po-divider"></div>

                                <!-- Kuantitas -->
                                <div class="po-row">
                                    <div class="po-row-icon" style="background:#e0f2fe;color:#0284c7;">
                                        <i class="ri-stack-line"></i>
                                    </div>
                                    <div class="po-row-content">
                                        <span class="po-row-label">Kuantitas</span>
                                        <span class="po-row-value">' . number_format((int) $row->qty, 0, ',', '.') . ' Unit</span>
                                    </div>
                                </div>

                            </div>

                            <!-- Status Footer -->
                            <div class="po-status-row">
                                <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#94a3b8;margin-bottom:5px;">Status PO</div>
                                <span class="po-status-chip" style="background:' . $map['color'] . '18;color:' . $map['color'] . ';border-color:' . $map['color'] . '40;">
                                    <i class="' . $map['icon'] . '" style="font-size:0.8rem;"></i>
                                    ' . $map['label'] . '
                                </span>
                            </div>
                        </div>
                        ';
                })
                ->addColumn('status', function ($row) {
                    if ($row->delivered_status == 1) {
                        return '
                            <style>
                                .status-card {
                                    display: inline-flex;
                                    flex-direction: column;
                                    gap: 0;
                                    min-width: 180px;
                                    background: #ffffff;
                                    border: 1px solid #e5e7eb;
                                    border-radius: 12px;
                                    overflow: hidden;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
                                    font-family: inherit;
                                }
                                .status-card-body {
                                    padding: 10px 14px;
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                }
                                .status-icon-wrap {
                                    width: 32px;
                                    height: 32px;
                                    border-radius: 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 1rem;
                                    flex-shrink: 0;
                                }
                                .status-chip-label {
                                    font-size: 0.6rem;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    color: #94a3b8;
                                }
                                .status-chip-value {
                                    font-size: 0.8rem;
                                    font-weight: 700;
                                    color: #1e293b;
                                    line-height: 1.3;
                                }
                                .status-footer-bar {
                                    padding: 5px 14px 7px;
                                    border-top: 1px solid #f1f5f9;
                                    background: #f0fdf4;
                                }
                                .status-arrived-chip {
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 3px 10px 3px 7px;
                                    border-radius: 20px;
                                    font-size: 0.72rem;
                                    font-weight: 600;
                                    background: #10b98118;
                                    color: #10b981;
                                    border: 1.5px solid #10b98140;
                                }
                            </style>
                            <div class="status-card shadow-lg">
                                <div class="status-card-body">
                                    <div class="status-icon-wrap" style="background:#dcfce7;color:#16a34a;">
                                        <i class="ri-map-pin-2-fill"></i>
                                    </div>
                                    <div class="d-flex flex-column gap-0">
                                        <span class="status-chip-label">Status Pengiriman</span>
                                        <span class="status-chip-value">Sudah Tiba Tujuan</span>
                                    </div>
                                </div>
                                <div class="status-footer-bar">
                                    <span class="status-arrived-chip">
                                        <i class="ri-checkbox-circle-fill" style="font-size:0.8rem;"></i>
                                        Terkirim
                                    </span>
                                </div>
                            </div>';
                    } else {
                        $estimasi = \Carbon\Carbon::parse($row->delivery_time_estimation)->format('Y-m-d');
                        $estimasiFormatted = \Carbon\Carbon::parse($row->delivery_time_estimation)->format('d M Y');
                        $deliverUrl = route('delivery.autoDeliver', $row->delivery_id);
                        $csrfToken  = csrf_token();
                        $deliveryId = $row->delivery_id;
                        $textDelivery      = \Carbon\Carbon::parse($row->delivery_time_estimation)->isPast()
                            ? '<span class="badge bg-label-danger">Sudah lewat estimasi</span>'
                            : '<span class="badge bg-label-warning">Sedang dalam perjalanan</span>';
                        $iconDelivery = \Carbon\Carbon::parse($row->delivery_time_estimation)->isPast()
                            ? 'ri-timer-line text-danger'
                            : 'ri-navigation-line text-warning';

                        return <<<HTML
                            <style>
                                .timer-card {
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
                                .timer-card-header {
                                    padding: 8px 14px;
                                    background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
                                    border-bottom: 1px solid #fde68a;
                                    display: flex;
                                    align-items: center;
                                    gap: 8px;
                                }
                                .timer-header-icon {
                                    width: 26px;
                                    height: 26px;
                                    background: #fde68a;
                                    border-radius: 7px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 0.8rem;
                                    color: #d97706;
                                    flex-shrink: 0;
                                }
                                .timer-card-body {
                                    padding: 10px 14px;
                                    display: flex;
                                    flex-direction: column;
                                    gap: 4px;
                                }
                                .timer-label {
                                    font-size: 0.6rem;
                                    font-weight: 600;
                                    text-transform: uppercase;
                                    letter-spacing: 0.06em;
                                    color: #94a3b8;
                                }
                                .timer-countdown {
                                    font-size: 0.95rem;
                                    font-weight: 800;
                                    color: #d97706;
                                    letter-spacing: 0.03em;
                                    font-variant-numeric: tabular-nums;
                                }
                                .timer-estimasi {
                                    font-size: 0.72rem;
                                    color: #64748b;
                                    font-weight: 500;
                                }
                                .timer-card-footer {
                                    padding: 5px 14px 8px;
                                    background: #fafafa;
                                    border-top: 1px solid #f1f5f9;
                                }
                                .timer-on-the-way {
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 5px;
                                    padding: 3px 10px 3px 7px;
                                    border-radius: 20px;
                                    font-size: 0.72rem;
                                    font-weight: 600;
                                    background: #f59e0b18;
                                    color: #d97706;
                                    border: 1.5px solid #f59e0b40;
                                }
                                .btn-deliver-now {
                                    display: inline-flex;
                                    align-items: center;
                                    justify-content: center;
                                    gap: 5px;
                                    width: 100%;
                                    padding: 7px 14px;
                                    border: none;
                                    border-top: 1px solid #e0f2fe;
                                    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
                                    color: #ffffff;
                                    font-size: 0.72rem;
                                    font-weight: 700;
                                    letter-spacing: 0.04em;
                                    cursor: pointer;
                                    transition: filter 0.18s ease, transform 0.15s ease;
                                }
                                .btn-deliver-now:hover  { filter: brightness(1.1); transform: translateY(-1px); }
                                .btn-deliver-now:active { filter: brightness(0.95); transform: translateY(0); }
                                .btn-deliver-now i { font-size: 0.85rem; }
                                .btn-deliver-now:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
                            </style>

                            <div class="timer-card timer-wrapper shadow-lg"
                                data-target="{$estimasi}"
                                data-id="{$deliveryId}">

                                <!-- Header -->
                                <div class="timer-card-header">
                                    <div class="timer-header-icon"><i class="ri-truck-line"></i></div>
                                    <div>
                                        <div style="font-size:0.6rem;font-weight:600;text-transform:uppercase;letter-spacing:0.06em;color:#92400e;">Estimasi Tiba</div>
                                        <div style="font-size:0.75rem;font-weight:700;color:#78350f;">{$estimasiFormatted}</div>
                                    </div>
                                </div>

                                <!-- Countdown Body -->
                                <div class="timer-card-body">
                                    <span class="timer-label">Sisa Waktu</span>
                                    <span class="timer-countdown countdown-display delivery-timer">CALCULATING...</span>
                                </div>

                                <!-- Footer -->
                                <div class="timer-card-footer">
                                    <span class="timer-on-the-way">
                                        <i class="{$iconDelivery}" style="font-size:0.8rem;"></i>
                                        {$textDelivery}
                                    </span>
                                </div>

                                <!-- Deliver Button -->
                                <button
                                    class="btn-deliver-now deliver-now-btn"
                                    data-id="{$deliveryId}"
                                    data-url="{$deliverUrl}"
                                    data-token="{$csrfToken}">
                                    <i class="ri-send-plane-fill"></i>
                                    Deliver Sekarang
                                </button>

                            </div>
                        HTML;
                    }
                })
                ->addColumn('action', function ($row) {
                    $showUrl = Route::has('delivery.show') ? route('delivery.show', $row->delivery_id) : '#';
                    $editUrl = Route::has('delivery.edit') ? route('delivery.edit', $row->delivery_id) : '#';
                    $deleteUrl = Route::has('delivery.destroy') ? route('delivery.destroy', $row->delivery_id) : '#';

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
                ->orderColumn('detail_po', 'tgl_po $1') // order by Tanggal PO
                ->filterColumn('detail_po', function ($data, $keyword) {
                    $statusSearch = [
                        'incoming'                              => 0,
                        'open'                                  => 1,
                        'partially delivered'                   => 2,
                        'fully delivered'                       => 3,
                        'partially delivered & partially invoiced' => 4,
                        'fully delivered & partially invoiced'  => 5,
                        'partially delivered & fully invoiced'  => 6,
                        'fully delivered & fully invoiced'      => 7,
                        'closed'                                => 8,
                    ];

                    $keyword_lower = strtolower(trim($keyword));
                    $matchedStatus = null;

                    foreach ($statusSearch as $label => $val) {
                        if (str_contains($label, $keyword_lower)) {
                            $matchedStatus = $val;
                            break;
                        }
                    }

                    $data->where(function ($q) use ($keyword, $matchedStatus) {
                        $q->where('no_po', 'like', "%{$keyword}%")
                            ->orWhere('nama_barang', 'like', "%{$keyword}%")
                            ->orWhere('qty', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(qty, ' Unit') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(tgl_po, '%d %b %Y') like ?", ["%{$keyword}%"]);

                        if (!is_null($matchedStatus)) {
                            $q->orWhere('status', $matchedStatus);
                        }
                    });
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
                ->orderColumn('status', 'delivered_status $1')
                ->filterColumn('status', function ($data, $keyword) {
                    $keyword_lower = strtolower(trim($keyword));

                    // ── Delivered Status
                    if (str_contains('sudah tiba tujuan', $keyword_lower)) {
                        $data->where('delivered_status', 1);
                        return;
                    }

                    if (str_contains('dalam perjalanan', $keyword_lower)) {
                        $data->where('delivered_status', 0);
                        return;
                    }

                    // ── Invoiced Status
                    if (str_contains('sudah di invoice', $keyword_lower)) {
                        $data->where('invoiced_status', 1);
                        return;
                    }

                    if (str_contains('belum di invoice', $keyword_lower)) {
                        $data->where('invoiced_status', 0);
                        return;
                    }

                    // ── Estimation overdue / on the way
                    if (str_contains('sudah lewat estimasi', $keyword_lower)) {
                        $data->where('delivered_status', 0)
                            ->whereRaw("delivery_time_estimation < NOW()");
                        return;
                    }

                    if (str_contains('sedang dalam perjalanan', $keyword_lower)) {
                        $data->where('delivered_status', 0)
                            ->whereRaw("delivery_time_estimation >= NOW()");
                        return;
                    }

                    // ── Date search — e.g. "11 Mar 2026" or "2026-03-11"
                    $data->where(function ($q) use ($keyword) {
                        $q->whereRaw("DATE_FORMAT(delivery_time_estimation, '%d %b %Y') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(delivery_time_estimation, '%Y-%m-%d') like ?", ["%{$keyword}%"])
                            ->orWhereRaw("DATE_FORMAT(delivery_time_estimation, '%d %M %Y') like ?", ["%{$keyword}%"]);
                    });
                })
                ->rawColumns(['detail_po', 'status', 'action', 'delivery_details'])
                ->make(true);
        }

        // 5. Stats for the view (unchanged)
        $stats = [
            'total'     => Delivery::count(),
            'transit'   => Delivery::where('delivered_status', 0)->count(),
            'delivered' => Delivery::where('delivered_status', 1)->count(),
            'inventory' => Delivery::sum('qty_delivered'),
        ];

        return view('delivery-index', compact('stats'));
    }

    public function autoDeliver($id)
    {
        $delivery = Delivery::find($id);
        if ($delivery) {
            $delivery->delivered_status = 1;
            $delivery->delivered_at = $delivery->delivery_time_estimation;
            $delivery->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
    public function create()
    {
        // Get POs where status is NOT 0 and remaining quantity > 0
        $pos = Po::select('tbl_po.*')
            ->selectSub(function ($query) {
                $query->from('tbl_delivery')
                    ->whereColumn('po_id', 'tbl_po.po_id')
                    ->selectRaw('COALESCE(SUM(qty_delivered), 0)');
            }, 'total_delivered')
            ->where('tbl_po.status', '!=', 0) // Excludes "0: Open"
            ->havingRaw('tbl_po.qty - total_delivered > 0')
            ->get();

        // Add a display string for the dropdown
        foreach ($pos as $po) {
            $po->remaining = $po->qty - $po->total_delivered;
            $po->display_text = $po->no_po . ' (Available: ' . $po->remaining . '/' . $po->qty . ')';
        }

        return view('delivery-create', compact('pos'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'po_id'                    => 'required|exists:tbl_po,po_id',
            'delivery_time_estimation' => 'required|date|after_or_equal:today',
            'qty_delivered'            => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->po_id);

            $totalDeliveredSoFar = $po->deliveries()->sum('qty_delivered');
            $newTotal = $totalDeliveredSoFar + $request->qty_delivered;
            if ($newTotal > $po->qty) {
                return response()->json([
                    'errors' => ['qty_delivered' => ['Total delivered would exceed PO quantity.']]
                ], 422);
            }

            $deliveryNo = 'DLV-' . date('Ymd') . '-' . strtoupper(uniqid());

            $deliveredStatus   = (int) $request->input('deliver_now', 0) === 1;
            $deliveredAt     = $deliveredStatus ? now() : null;

            $delivery = Delivery::create([
                'delivery_no'              => $deliveryNo,
                'po_id'                    => $request->po_id,
                'qty_delivered'            => $request->qty_delivered,
                'delivery_time_estimation' => $request->delivery_time_estimation,
                'invoiced_status'          => 0,
                'delivered_status'         => $deliveredStatus,  // ← was hardcoded 0
                'delivered_at'             => $deliveredAt,       // ← optional, if column exists
                'input_by'                 => Auth::id() ?? 1,
                'input_date'               => now(),
            ]);

            $this->logCreate($delivery, $delivery->qty_delivered . ' Delivery Dibuat dengan Nomor Delivery ' . $deliveryNo . ' Yang terhubung ke PO ' . $delivery->po->no_po . ' (' . $delivery->po->nama_barang . ')');

            $po->syncStatus();

            return response()->json([
                'success'      => true,
                'message'      => 'Delivery recorded. PO status updated.',
                'redirect_url' => route('delivery.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing delivery: ' . $e->getMessage()
            ], 500);
        }
    }
    public function destroy($id)
    {
        $delivery = Delivery::findOrFail($id);
        $po = $delivery->po;

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // ← safely handle invoice and payment, both may not exist
        if ($delivery->invoice) {
            if ($delivery->invoice->payment) {
                $delivery->invoice->payment->delete();
            }
            $delivery->invoice->delete();
        }

        $delivery->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $po->syncStatus();

        return response()->json([
            'success' => true,
            'message' => 'Delivery berhasil dihapus.',
        ]);
    }
    public function edit($delivery_id)
    {
        $delivery = Delivery::findOrFail($delivery_id);

        // Fetch ONLY the current PO (no status filtering – we want it even if status is excluded)
        $purchaseOrders = Po::select('tbl_po.*')
            ->selectSub(function ($query) {
                $query->from('tbl_delivery')
                    ->whereColumn('po_id', 'tbl_po.po_id')
                    ->selectRaw('COALESCE(SUM(qty_delivered), 0)');
            }, 'total_delivered')
            ->where('po_id', $delivery->po_id)  // ← only the current PO
            ->get();

        foreach ($purchaseOrders as $po) {
            // Overall remaining quantity (after ALL deliveries)
            $po->overall_remaining = $po->qty - $po->total_delivered;

            // Quantity that can still be allocated to THIS delivery
            // (add back the current delivery's qty because we're editing it)
            $po->available_for_edit = $po->overall_remaining;

            // Display text uses overall_remaining
            $po->display_text = "{$po->no_po} (Available: " . number_format($po->overall_remaining) . " / " . number_format($po->qty) . ")";
        }

        // No need for $currentPo – $purchaseOrders already contains it
        return view('delivery-edit', compact('delivery', 'purchaseOrders'));
    }

    public function update(Request $request, $delivery_id)
    {
        // 1. Fetch the existing delivery
        $delivery = Delivery::findOrFail($delivery_id);

        // 2. Validation
        $validator = Validator::make($request->all(), [
            'po_id'                    => 'required|exists:tbl_po,po_id',
            'delivery_time_estimation' => 'required|date',
            'qty_delivered'            => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $po = Po::findOrFail($request->po_id);

            // 3. LOGIC FIX: Calculate total delivered by OTHERS (exclude this delivery's current qty)
            $totalDeliveredByOthers = $po->deliveries()
                ->where('delivery_id', '!=', $delivery_id) // Do not count the record we are currently editing
                ->sum('qty_delivered');

            $newTotal = $totalDeliveredByOthers + $request->qty_delivered;

            if ($newTotal > $po->qty) {
                $maxAllowed = $po->qty - $totalDeliveredByOthers;
                return response()->json([
                    'errors' => ['qty_delivered' => ["Total exceeds PO quantity. Max additional allowed: $maxAllowed"]]
                ], 422);
            }

            $oldDelivery = $delivery->toArray();

            $deliveredStatus   = (int) $request->input('deliver_now', 0) === 1;
            $deliveredAt     = $deliveredStatus ? now() : null;

            // 4. Update the existing delivery record
            $delivery->update([
                'po_id'                    => $request->po_id,
                'qty_delivered'            => $request->qty_delivered,
                'delivery_time_estimation' => $request->delivery_time_estimation,
                'delivered_at'             => $deliveredAt,
                'delivered_status'         => $deliveredStatus,
                'edit_by'                  => Auth::id() ?? 1,
                'edit_date'                => now(),
            ]);

            $newDelivery = $delivery->fresh();
            $this->logUpdate($newDelivery, $oldDelivery, 'Delivery dengan nomor ' . $delivery->delivery_no . ' Yang terhubung ke PO ' . $delivery->po->no_po . ' (' . $delivery->po->nama_barang . ') ' . ' Di update');

            // 5. Sync PO status
            if (method_exists($po, 'syncStatus')) {
                $po->syncStatus();
            }

            return response()->json([
                'success' => true,
                'message' => 'Pengiriman berhasil diperbarui.',
                'redirect_url' => route('delivery.index')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($delivery_id)
    {
        // Fetch the PO with its customer relationship
        $delivery = Delivery::findOrFail($delivery_id);

        return view('delivery-show', compact('delivery'));
    }
}
