<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function getActor(Request $request): ?User
    {
        $actorId = $request->header('X-User-Id');
        if (!$actorId) {
            return null;
        }
        $user = User::with('role')->find($actorId);
        return ($user && $user->is_active) ? $user : null;
    }

    // Búsqueda pública para clientes (Phase 1)
    public function track(Request $request)
    {
        $validated = $request->validate([
            'customer_number' => 'required|string',
            'invoice_number'  => 'required|string',
        ]);

        $order = Order::with(['customer'])
            ->where('customer_number', trim($validated['customer_number']))
            ->where('invoice_number', trim($validated['invoice_number']))
            ->where('is_deleted', false)
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'No order found matching that Customer Number and Invoice Number.'
            ], 404);
        }

        return response()->json($order, 200);
    }

    // Generar siguiente número de factura consecutivo
    public function nextInvoice()
    {
        $count = Order::count() + 1;
        do {
            $candidate = 'INV-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            $exists = Order::where('invoice_number', $candidate)->exists();
            $count++;
        } while ($exists);

        return response()->json(['next_invoice' => $candidate], 200);
    }

    // Listar órdenes activas con filtros
    public function index(Request $request)
    {
        $query = Order::with('customer')->where('is_deleted', false);

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . trim($request->invoice_number) . '%');
        }
        if ($request->filled('customer_number')) {
            $query->where('customer_number', 'like', '%' . trim($request->customer_number) . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date')) {
            $query->whereDate('order_date', $request->date);
        }

        return response()->json($query->orderByDesc('created_at')->get(), 200);
    }

    // Crear nueva orden (inicia obligatoriamente en "Ordered")
    public function store(Request $request)
    {
        $actor = $this->getActor($request);
        $role = $actor?->role?->name;

        if (!in_array($role, ['Administrator', 'Sales'])) {
            return response()->json([
                'message' => 'Unauthorized: Only Sales or Administrator can create orders.'
            ], 403);
        }

        $validated = $request->validate([
            'invoice_number'   => 'required|string|unique:orders,invoice_number',
            'customer_number'  => 'required|string',
            'company_name'     => 'required|string|max:255',
            'fiscal_data'      => 'required|string',
            'order_date'       => 'required|date',
            'delivery_address' => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        $order = DB::transaction(function () use ($validated) {
            Customer::updateOrCreate(
                ['customer_number' => trim($validated['customer_number'])],
                [
                    'company_name'     => trim($validated['company_name']),
                    'fiscal_data'      => trim($validated['fiscal_data']),
                    'delivery_address' => trim($validated['delivery_address']),
                ]
            );

            return Order::create([
                'invoice_number'   => trim($validated['invoice_number']),
                'customer_number'  => trim($validated['customer_number']),
                'order_date'       => $validated['order_date'],
                'delivery_address' => trim($validated['delivery_address']),
                'status'           => 'Ordered',
                'notes'            => $validated['notes'] ?? null,
                'is_deleted'       => false,
            ]);
        });

        return response()->json($order->load('customer'), 201);
    }

    // Ver detalle de una orden
    public function show($id)
    {
        $order = Order::with('customer')->findOrFail($id);
        return response()->json($order, 200);
    }

    // Editar orden (activa o eliminada)
    public function update(Request $request, $id)
    {
        $actor = $this->getActor($request);
        if (!$actor) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order = Order::with('customer')->findOrFail($id);

        $validated = $request->validate([
            'order_date'       => 'sometimes|required|date',
            'delivery_address' => 'sometimes|required|string',
            'status'           => 'sometimes|required|in:Ordered,In process,In route,Delivered',
            'notes'            => 'nullable|string',
            'company_name'     => 'sometimes|required|string|max:255',
            'fiscal_data'      => 'sometimes|required|string',
        ]);

        DB::transaction(function () use ($order, $validated) {
            $orderFields = array_intersect_key(
                $validated,
                array_flip(['order_date', 'delivery_address', 'status', 'notes'])
            );
            $order->update($orderFields);

            if ($order->customer && (isset($validated['company_name']) || isset($validated['fiscal_data']))) {
                $customerFields = [];
                if (isset($validated['company_name'])) {
                    $customerFields['company_name'] = trim($validated['company_name']);
                }
                if (isset($validated['fiscal_data'])) {
                    $customerFields['fiscal_data'] = trim($validated['fiscal_data']);
                }
                if (isset($validated['delivery_address'])) {
                    $customerFields['delivery_address'] = trim($validated['delivery_address']);
                }
                $order->customer->update($customerFields);
            }
        });

        return response()->json($order->fresh('customer'), 200);
    }

    // Borrado lógico (Logical Deletion)
    public function destroy(Request $request, $id)
    {
        $actor = $this->getActor($request);
        $role = $actor?->role?->name;

        if (!in_array($role, ['Administrator', 'Sales'])) {
            return response()->json([
                'message' => 'Unauthorized: Only Sales or Administrator can delete orders.'
            ], 403);
        }

        $order = Order::findOrFail($id);
        $order->update(['is_deleted' => true]);

        return response()->json(['message' => 'Order logically deleted successfully.'], 200);
    }

    // Listar órdenes eliminadas (Deleted Orders)
    public function archived(Request $request)
    {
        $query = Order::with('customer')->where('is_deleted', true);

        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . trim($request->invoice_number) . '%');
        }
        if ($request->filled('customer_number')) {
            $query->where('customer_number', 'like', '%' . trim($request->customer_number) . '%');
        }

        return response()->json($query->orderByDesc('updated_at')->get(), 200);
    }

    // Restaurar orden eliminada
    public function restore(Request $request, $id)
    {
        $actor = $this->getActor($request);
        $role = $actor?->role?->name;

        if (!in_array($role, ['Administrator', 'Sales'])) {
            return response()->json([
                'message' => 'Unauthorized: Only Sales or Administrator can restore orders.'
            ], 403);
        }

        $order = Order::findOrFail($id);
        $order->update(['is_deleted' => false]);

        return response()->json($order->load('customer'), 200);
    }
}