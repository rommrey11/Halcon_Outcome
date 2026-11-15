<template>
    <div class="min-h-screen bg-slate-100 flex flex-col justify-between text-slate-800">
        <!-- Header -->
        <header class="bg-slate-900 text-white shadow-md">
            <div class="max-w-4xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-950 font-extrabold flex items-center justify-center text-lg">
                        H
                    </div>
                    <span class="text-xl font-bold tracking-wider">HALCON</span>
                </div>
                <span class="text-xs uppercase tracking-widest text-slate-400">Order Tracking System</span>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-2xl w-full mx-auto px-6 py-12 flex-grow">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Track Your Order</h1>
                <p class="text-sm text-slate-600 mb-6">
                    Enter your Customer Number and Invoice Number below to check the current status of your order.
                </p>

                <!-- Search Form -->
                <form @submit.prevent="checkOrderStatus" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                Customer Number
                            </label>
                            <input
                                id="customer_number"
                                v-model="customerNumber"
                                type="text"
                                required
                                placeholder="e.g. CUST-1001"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            />
                        </div>

                        <div>
                            <label for="invoice_number" class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                Invoice Number
                            </label>
                            <input
                                id="invoice_number"
                                v-model="invoiceNumber"
                                type="text"
                                required
                                placeholder="e.g. INV-2026-001"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-slate-900 hover:bg-slate-800 disabled:bg-slate-400 text-white font-semibold py-3 px-5 rounded-lg transition cursor-pointer text-sm"
                    >
                        {{ loading ? 'Searching Order...' : 'Check Order Status' }}
                    </button>
                </form>

                <!-- Error Alert -->
                <div v-if="errorMessage" class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700">
                    {{ errorMessage }}
                </div>

                <!-- Order Details Result -->
                <div v-if="order" class="mt-8 border-t border-slate-200 pt-6 space-y-5">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-500">Invoice</p>
                            <p class="text-lg font-bold text-slate-900">{{ order.invoice_number }}</p>
                        </div>

                        <span
                            :class="statusBadgeClass(order.status)"
                            class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wide"
                        >
                            {{ order.status }}
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 rounded-xl p-4 text-sm">
                        <div>
                            <p class="text-xs text-slate-500">Customer</p>
                            <p class="font-semibold text-slate-800">
                                {{ order.customer?.company_name || order.customer_number }}
                            </p>
                            <p class="text-xs text-slate-500">ID: {{ order.customer_number }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-slate-500">Order Date</p>
                            <p class="font-semibold text-slate-800">{{ formatDate(order.order_date) }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-xs text-slate-500">Delivery Address</p>
                            <p class="font-medium text-slate-800">{{ order.delivery_address }}</p>
                        </div>

                        <div v-if="order.notes" class="sm:col-span-2">
                            <p class="text-xs text-slate-500">Notes</p>
                            <p class="text-slate-700">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="text-center py-6 text-xs text-slate-500">
            HALCON Logistics &amp; Distribution &bull; Order Tracking Portal
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const customerNumber = ref('');
const invoiceNumber = ref('');
const order = ref(null);
const errorMessage = ref('');
const loading = ref(false);

const checkOrderStatus = async () => {
    loading.value = true;
    errorMessage.value = '';
    order.value = null;

    try {
        const params = new URLSearchParams({
            customer_number: customerNumber.value,
            invoice_number: invoiceNumber.value,
        });

        const response = await fetch(`/api/orders/track?${params.toString()}`, {
            headers: {
                Accept: 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok) {
            errorMessage.value = data.message || 'Order not found.';
            return;
        }

        order.value = data;
    } catch (error) {
        errorMessage.value = 'Network error while connecting to the server.';
    } finally {
        loading.value = false;
    }
};

const statusBadgeClass = (status) => {
    const classes = {
        Ordered: 'bg-blue-100 text-blue-800',
        'In process': 'bg-amber-100 text-amber-800',
        'In route': 'bg-purple-100 text-purple-800',
        Delivered: 'bg-emerald-100 text-emerald-800',
    };
    return classes[status] || 'bg-slate-200 text-slate-800';
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleString();
};
</script>