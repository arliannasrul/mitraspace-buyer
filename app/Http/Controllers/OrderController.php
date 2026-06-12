<?php

namespace App\Http\Controllers;

use App\Models\PaymentLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Tampilkan riwayat belanja milik user yang sedang login.
     */
    public function index()
    {
        $orders = auth()->user()->payments()->latest()->get()->map(function ($log) {
            $payload = json_decode($log->order_payload, true) ?? [];
            
            $invoiceNumber = $log->invoice_number;
            $amount = $log->amount;
            $createdAt = $log->created_at;
            $status = $log->status;
            
            $items = [];
            $courier = '';
            $courierService = '';
            $shippingCost = 0;
            $destinationCity = '';
            
            // Format 1: Format data lokal (yang disimpan oleh CheckoutController)
            if (isset($payload['items'])) {
                $items = $payload['items'];
                $courier = $payload['courier'] ?? '';
                $courierService = $payload['courier_service'] ?? '';
                $shippingCost = $payload['shipping_cost'] ?? 0;
                $destinationCity = $payload['shipping_address']['city'] ?? '';
            } 
            // Format 2: Format webhook DOKU murni (jika local log tidak ada sebelumnya)
            elseif (isset($payload['order'])) {
                $courier = 'jne';
                $courierService = 'REG';
                $destinationCity = 'Jakarta';
                $shippingCost = 15000;
                $items = [
                    [
                        'name' => 'Produk Pembelian (Doku webhook)',
                        'quantity' => 1,
                        'price' => $amount - 15000,
                        'subtotal' => $amount - 15000,
                    ]
                ];
            }

            return [
                'id' => $log->id,
                'invoice_number' => $invoiceNumber,
                'seller_order_number' => $log->seller_order_number,
                'amount' => $amount,
                'status' => $status,
                'created_at' => $createdAt,
                'items' => $items,
                'courier' => $courier,
                'courier_service' => $courierService,
                'shipping_cost' => $shippingCost,
                'destination_city' => $destinationCity,
            ];
        });

        return view('orders.index', compact('orders'));
    }
}
