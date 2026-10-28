<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Listar ordenes no archivadas
    public function index()
    {
        $orders = Order::where('is_deleted', false)->get();
        return response()->json($orders, 200);
    }

    // Crear orden
    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'   => 'required|string',
            'customer_number'  => 'required|string',
            'order_date'       => 'required|date',
            'delivery_address' => 'required|string',
            'status'           => 'nullable|in:Ordered,In process,In route,Delivered',
            'notes'            => 'nullable|string',
        ]);

        $order = Order::create($validated);

        return response()->json($order, 201);
    }

    // Obtener orden por clave/ID
    public function show($id)
    {
        $order = Order::findOrFail($id);
        return response()->json($order, 200);
    }

    // Buscar por invoice_number directamente
    public function getByInvoice($invoice)
    {
        $order = Order::where('invoice_number', $invoice)->firstOrFail();
        return response()->json($order, 200);
    }

    // Actualizar estado u orden
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $order->update($request->all());
        return response()->json($order, 200);
    }

    // Archivar orden (Archive order / soft delete)
    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['is_deleted' => true]);
        return response()->json(['message' => 'Order archived successfully'], 200);
    }

    // Listar ordenes archivadas
    public function archived()
    {
        $orders = Order::where('is_deleted', true)->get();
        return response()->json($orders, 200);
    }
}