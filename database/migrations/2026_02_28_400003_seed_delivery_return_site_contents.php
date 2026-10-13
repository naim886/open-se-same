<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            [
                'key'   => 'delivery_policy',
                'label' => 'Delivery Info',
                'type'  => 'html',
                'value' => '
<div class="row g-4">
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-truck text-danger me-2"></i>Delivery Charges</h6>
        <table class="table table-sm mb-0">
            <tr><td>Inside Dhaka</td><td><strong>৳70</strong></td></tr>
            <tr><td>Outside Dhaka</td><td><strong>৳130</strong></td></tr>
            <tr>
                <td><i class="fas fa-bolt text-danger me-1"></i> Express (Inside Dhaka)</td>
                <td><strong>৳150</strong> <small class="text-muted">order before 11 AM</small></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-clock text-danger me-2"></i>Delivery Time</h6>
        <ul style="padding-left:18px; line-height:2.2; margin:0;">
            <li>Inside Dhaka: 1–2 business days</li>
            <li>Outside Dhaka: 2–5 business days</li>
            <li>Express: Same day (order before 11 AM)</li>
            <li>Counted from order confirmation</li>
        </ul>
    </div>
</div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key'   => 'return_policy',
                'label' => 'Return Policy',
                'type'  => 'html',
                'value' => '
<div class="row g-4">
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-undo text-danger me-2"></i>Return Conditions</h6>
        <ul style="padding-left:18px; line-height:2.4; margin:0;">
            <li>Return request must be made within <strong>3 days</strong> of delivery</li>
            <li>Product must be <strong>unused</strong> and in original packaging</li>
            <li>Tags and labels must be intact</li>
            <li>Damaged or washed items are not eligible for return</li>
            <li>Customized or sale items cannot be returned</li>
        </ul>
    </div>
    <div class="col-md-6">
        <h6 class="fw-bold mb-3"><i class="fas fa-exchange-alt text-danger me-2"></i>How to Return</h6>
        <ol style="padding-left:18px; line-height:2.4; margin:0;">
            <li>Contact us with your order number to initiate a return</li>
            <li>Send a clear photo of the product issue</li>
            <li>We will confirm eligibility and guide you through the process</li>
            <li>Refund/exchange is processed within <strong>3–5 business days</strong></li>
        </ol>
    </div>
</div>',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('site_contents')->updateOrInsert(['key' => $row['key']], $row);
        }
    }

    public function down(): void
    {
        DB::table('site_contents')->whereIn('key', ['delivery_policy', 'return_policy'])->delete();
    }
};
