<?php

namespace App\Http\Controllers;

use App\Services\SellerApiService;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function __construct(protected SellerApiService $sellerApi) {}

    public function index()
    {
        return view('tracking.index');
    }

    public function show(Request $request, string $orderNumber = '')
    {
        if (empty($orderNumber)) {
            $request->validate(['order_number' => 'required|string|max:100']);
            $orderNumber = $request->input('order_number');
        }

        $orderNumber = strtoupper(trim($orderNumber));
        $queryNumber = $orderNumber;

        // Map Invoice Number (starts with INV-) to the Seller Order Number (ORD-)
        if (str_starts_with($orderNumber, 'INV-')) {
            $log = \App\Models\PaymentLog::where('invoice_number', $orderNumber)->first();
            if ($log) {
                if ($log->seller_order_number) {
                    $queryNumber = $log->seller_order_number;
                } else {
                    return view('tracking.index')
                        ->with('error', 'Pesanan dengan invoice ini belum diteruskan ke penjual. Coba hubungi customer service atau tunggu beberapa saat.')
                        ->with('searched', $orderNumber);
                }
            } else {
                return view('tracking.index')
                    ->with('error', 'Nomor invoice tidak ditemukan.')
                    ->with('searched', $orderNumber);
            }
        }

        $result = $this->sellerApi->trackOrder($queryNumber);

        if (!$result['success']) {
            return view('tracking.index')->with('error', $result['message'])->with('searched', $orderNumber);
        }

        $tracking = $result['data'];

        return view('tracking.show', compact('tracking', 'orderNumber'));
    }

    public function search(Request $request)
    {
        $request->validate(['order_number' => 'required|string|max:100']);
        $orderNumber = strtoupper(trim($request->input('order_number')));

        return redirect()->route('tracking.show', ['orderNumber' => $orderNumber]);
    }
}
