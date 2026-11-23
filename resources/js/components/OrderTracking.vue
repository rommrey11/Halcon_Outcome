<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col">
        <!-- Header -->
        <header class="bg-slate-900 text-white shadow-md">
            <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-950 font-extrabold flex items-center justify-center text-lg">
                        H
                    </div>
                    <span class="text-xl font-bold tracking-wider">HALCON</span>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-xs uppercase tracking-widest text-slate-400">Order Tracking System</span>
                    <button
                        @click="$emit('go-admin')"
                        class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-3 py-1.5 rounded-lg transition cursor-pointer"
                    >
                        Employee Login
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow max-w-3xl w-full mx-auto px-6 py-12">
            <!-- Search Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 mb-8">
                <h1 class="text-2xl font-bold text-slate-900 mb-2">Track Your Order</h1>
                <p class="text-slate-500 text-sm mb-6">
                    Enter your Customer Number and Invoice Number below to check the current status of your order.
                </p>

                <form @submit.prevent="trackOrder" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                Customer Number
                            </label>
                            <input
                                v-model="customerNumber"
                                type="text"
                                required
                                placeholder="e.g. CUST-5202"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                                Invoice Number
                            </label>
                            <input
                                v-model="invoiceNumber"
                                type="text"
                                required
                                placeholder="e.g. INV-82489"
                                class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            />
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-slate-900 hover:bg-slate-800 disabled:bg-slate-400 text-white font-semibold py-3 rounded-lg text-sm transition cursor-pointer"
                    >
                        {{ loading ? 'Searching...' : 'Check Order Status' }}
                    </button>
                </form>

                <!-- Error Alert -->
                <div
                    v-if="errorMessage"
                    class="mt-6 rounded-lg bg-red-50 border border-red-200 p-4 text-sm text-red-700"
                >
                    {{ errorMessage }}
                </div>
            </div>

            <!-- Order Result Card -->
            <div
                v-if="order"
                class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden"
            >
                <div class="bg-slate-50 px-8 py-5 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-slate-400">Invoice Number</span>
                        <h2 class="text-xl font-bold text-slate-900">{{ order.invoice_number }}</h2>
                    </div>

                    <span
                        :class="statusBadgeClass(order.status)"
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider"
                    >
                        {{ order.status }}
                    </span>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Progress Stepper -->
                    <div class="grid grid-cols-4 gap-2 text-center text-xs font-semibold">
                        <div
                            v-for="(step, idx) in steps"
                            :key="step"
                            class="flex flex-col items-center gap-2"
                        >
                            <div
                                :class="idx <= currentStepIndex ? 'bg-amber-500 text-slate-950' : 'bg-slate-200 text-slate-500'"
                                class="w-8 h-8 rounded-full flex items-center justify-center font-bold"
                            >
                                {{ idx + 1 }}
                            </div>
                            <span :class="idx <= currentStepIndex ? 'text-slate-900' : 'text-slate-400'">
                                {{ step }}
                            </span>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100 text-sm">
                        <div>
                            <p class="text-slate-400 text-xs uppercase">Customer</p>
                            <p class="font-semibold text-slate-800">
                                {{ order.customer?.name || order.customer_number }}
                            </p>
                        </div>

                        <div>
                            <p class="text-slate-400 text-xs uppercase">Order Date</p>
                            <p class="font-semibold text-slate-800">{{ formatDate(order.order_date) }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <p class="text-slate-400 text-xs uppercase">Delivery Address</p>
                            <p class="font-semibold text-slate-800">{{ order.delivery_address }}</p>
                        </div>

                        <div v-if="order.notes" class="sm:col-span-2">
                            <p class="text-slate-400 text-xs uppercase">Notes</p>
                            <p class="text-slate-700">{{ order.notes }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';

defineEmits(['go-admin']);

const customerNumber = ref('');
const invoiceNumber = ref('');
const order = ref(null);
const errorMessage = ref('');
const loading = ref(false);

const steps = ['Ordered', 'In process', 'In route', 'Delivered'];

const currentStepIndex = computed(() => {
    if (!order.value) return -1;
    return steps.indexOf(order.value.status);
});

const statusBadgeClass = (status) => {
    switch (status) {
        case 'Ordered':
            return 'bg-blue-100 text-blue-800';
        case 'In process':
            return 'bg-amber-100 text-amber-800';
        case 'In route':
            return 'bg-purple-100 text-purple-800';
        case 'Delivered':
            return 'bg-emerald-100 text-emerald-800';
        default:
            return 'bg-slate-100 text-slate-800';
    }
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleString();
};

const trackOrder = async () => {
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
        errorMessage.value = 'Unable to connect to the server. Please try again.';
    } finally {
        loading.value = false;
    }
};
</script>