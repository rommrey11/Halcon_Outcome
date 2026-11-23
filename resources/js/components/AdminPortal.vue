<template>
    <div class="min-h-screen bg-slate-100 text-slate-800 flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-slate-900 text-white shadow-md">
            <div class="max-w-6xl mx-auto px-6 py-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500 text-slate-950 font-extrabold flex items-center justify-center text-lg">
                        H
                    </div>
                    <div>
                        <span class="text-xl font-bold tracking-wider">HALCON</span>
                        <span class="ml-2 text-xs uppercase tracking-widest text-slate-400">Admin Portal</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button
                        @click="$emit('go-public')"
                        class="text-xs text-slate-300 hover:text-white underline cursor-pointer"
                    >
                        Public Order Tracking
                    </button>

                    <div v-if="currentUser" class="flex items-center gap-3 border-l border-slate-700 pl-4">
                        <div class="text-right">
                            <p class="text-sm font-semibold leading-tight">{{ currentUser.name }}</p>
                            <span class="inline-block text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-amber-500/20 text-amber-300">
                                {{ currentUser.role?.name }}
                            </span>
                        </div>
                        <button
                            @click="logout"
                            class="bg-red-600 hover:bg-red-500 text-white text-xs font-semibold px-3 py-2 rounded-lg transition cursor-pointer"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- LOGIN SCREEN -->
        <main v-if="!currentUser" class="flex-grow flex items-center justify-center px-6 py-12">
            <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
                <h1 class="text-2xl font-bold text-slate-900 mb-1">Employee Login</h1>
                <p class="text-sm text-slate-500 mb-6">
                    Authorized HALCON personnel only. Customers cannot register or access this portal.
                </p>

                <form @submit.prevent="handleLogin" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                            Username or Email
                        </label>
                        <input
                            v-model="loginForm.login"
                            type="text"
                            required
                            placeholder="admin@halcon.com"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1">
                            Password
                        </label>
                        <input
                            v-model="loginForm.password"
                            type="password"
                            required
                            placeholder="••••••••"
                            class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                        />
                    </div>

                    <div v-if="loginError" class="rounded-lg bg-red-50 border border-red-200 p-3 text-xs text-red-700">
                        {{ loginError }}
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 rounded-lg text-sm transition cursor-pointer"
                    >
                        {{ loading ? 'Signing in...' : 'Login' }}
                    </button>
                </form>
            </div>
        </main>

        <!-- ADMINISTRATIVE DASHBOARD -->
        <main v-else class="max-w-6xl w-full mx-auto px-6 py-8 flex-grow space-y-6">
            <!-- Welcome & Navigation Header -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-wider text-slate-400">Administrative Dashboard</p>
                    <h1 class="text-2xl font-bold text-slate-900">Welcome, {{ currentUser.name }}</h1>
                    <p class="text-sm text-slate-600 mt-1">
                        Assigned Role:
                        <span class="font-bold text-slate-900">{{ currentUser.role?.name }}</span>
                        &mdash; {{ roleDescription(currentUser.role?.name) }}
                    </p>
                </div>

                <!-- Module Navigation Pills -->
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="activeTab = 'overview'"
                        :class="activeTab === 'overview' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition cursor-pointer"
                    >
                        Dashboard
                    </button>

                    <button
                        @click="openOrdersModule"
                        :class="activeTab === 'orders' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition cursor-pointer"
                    >
                        Order Management
                    </button>

                    <button
                        v-if="canManageOrders"
                        @click="openDeletedOrdersModule"
                        :class="activeTab === 'deleted-orders' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition cursor-pointer"
                    >
                        Deleted Orders
                    </button>

                    <button
                        v-if="isAdmin"
                        @click="openUsersModule"
                        :class="activeTab === 'users' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                        class="px-4 py-2 rounded-lg text-xs font-semibold transition cursor-pointer"
                    >
                        User Management
                    </button>
                </div>
            </div>

            <!-- 1. OVERVIEW TAB -->
            <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div
                    v-for="mod in availableModules"
                    :key="mod.title"
                    class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col justify-between shadow-xs"
                >
                    <div>
                        <span class="inline-block text-[11px] font-bold uppercase tracking-wider px-2.5 py-1 rounded bg-slate-100 text-slate-700 mb-3">
                            {{ mod.badge }}
                        </span>
                        <h3 class="text-lg font-bold text-slate-900 mb-1">{{ mod.title }}</h3>
                        <p class="text-sm text-slate-600">{{ mod.description }}</p>
                    </div>

                    <button
                        @click="mod.action"
                        class="mt-5 w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 rounded-lg transition cursor-pointer"
                    >
                        Open Module
                    </button>
                </div>
            </div>

            <!-- 2. ORDER MANAGEMENT TAB (ACTIVE ORDERS) -->
            <div v-if="activeTab === 'orders'" class="space-y-6">
                <!-- Create Order Form (Sales & Admin) -->
                <div v-if="canManageOrders" class="bg-white rounded-2xl border border-slate-200 p-6">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Create New Order</h2>
                            <p class="text-xs text-slate-500">All newly created orders start automatically with status <span class="font-bold text-blue-700">Ordered</span>.</p>
                        </div>
                        <button
                            type="button"
                            @click="fetchNextInvoice"
                            class="text-xs text-amber-700 hover:underline font-semibold cursor-pointer"
                        >
                            Refresh Consecutive Invoice #
                        </button>
                    </div>

                    <form @submit.prevent="createOrder" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Consecutive Invoice #</label>
                            <input
                                v-model="orderForm.invoice_number"
                                type="text"
                                required
                                placeholder="INV-2026-002"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-slate-50 font-mono"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Unique Customer Number</label>
                            <input
                                v-model="orderForm.customer_number"
                                type="text"
                                required
                                placeholder="e.g. CUST-2001"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Customer / Company Name</label>
                            <input
                                v-model="orderForm.company_name"
                                type="text"
                                required
                                placeholder="Constructora Alfa S.A."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Fiscal Information (RFC / Tax ID)</label>
                            <input
                                v-model="orderForm.fiscal_data"
                                type="text"
                                required
                                placeholder="CAL950412XYZ"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Order Date &amp; Time</label>
                            <input
                                v-model="orderForm.order_date"
                                type="datetime-local"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Status</label>
                            <input
                                type="text"
                                value="Ordered"
                                disabled
                                class="w-full rounded-lg border border-blue-200 bg-blue-50 text-blue-800 font-bold px-3 py-2 text-sm"
                            />
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Delivery Address</label>
                            <input
                                v-model="orderForm.delivery_address"
                                type="text"
                                required
                                placeholder="Blvd. Manuel Avila Camacho 120, Naucalpan"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Notes / Additional Info</label>
                            <input
                                v-model="orderForm.notes"
                                type="text"
                                placeholder="Deliver before 4:00 PM"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div class="sm:col-span-2 lg:col-span-3 flex items-center justify-between pt-2">
                            <div>
                                <span v-if="orderFeedback" class="text-xs font-semibold text-emerald-700">{{ orderFeedback }}</span>
                                <span v-if="orderError" class="text-xs font-semibold text-red-600">{{ orderError }}</span>
                            </div>
                            <button
                                type="submit"
                                class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2.5 px-6 rounded-lg text-sm transition cursor-pointer"
                            >
                                Save New Order
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Search & Filter Bar -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h2 class="text-base font-bold text-slate-900 mb-3">Search &amp; Filter Active Orders</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Invoice Number</label>
                            <input
                                v-model="orderFilters.invoice_number"
                                @input="fetchActiveOrders"
                                type="text"
                                placeholder="Search invoice..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Customer Number</label>
                            <input
                                v-model="orderFilters.customer_number"
                                @input="fetchActiveOrders"
                                type="text"
                                placeholder="Search customer..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Order Date</label>
                            <input
                                v-model="orderFilters.date"
                                @change="fetchActiveOrders"
                                type="date"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Status</label>
                            <select
                                v-model="orderFilters.status"
                                @change="fetchActiveOrders"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white"
                            >
                                <option value="">All Statuses</option>
                                <option value="Ordered">Ordered</option>
                                <option value="In process">In process</option>
                                <option value="In route">In route</option>
                                <option value="Delivered">Delivered</option>
                            </select>
                        </div>
                        <button
                            @click="clearOrderFilters"
                            class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold py-2 px-4 rounded-lg text-sm cursor-pointer"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Active Orders Table -->
                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Active Orders</h2>
                        <span class="text-xs text-slate-500">{{ activeOrders.length }} active orders</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3">Order Date</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-if="activeOrders.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-slate-400">
                                        No active orders match your current filter.
                                    </td>
                                </tr>
                                <tr v-for="ord in activeOrders" :key="ord.invoice_number" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-900">{{ ord.invoice_number }}</td>
                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-slate-800">{{ ord.customer?.company_name || 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ ord.customer_number }}</p>
                                    </td>
                                    <td class="px-6 py-3 text-slate-600 text-xs">{{ formatDate(ord.order_date) }}</td>
                                    <td class="px-6 py-3">
                                        <span :class="statusBadgeClass(ord.status)" class="px-2.5 py-1 rounded-full text-xs font-bold">
                                            {{ ord.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <button
                                            @click="openOrderModal(ord)"
                                            class="px-3 py-1 rounded bg-slate-900 hover:bg-slate-700 text-white text-xs font-semibold cursor-pointer"
                                        >
                                            Details / Edit
                                        </button>
                                        <button
                                            v-if="canManageOrders"
                                            @click="logicalDeleteOrder(ord)"
                                            class="px-3 py-1 rounded bg-red-100 hover:bg-red-200 text-red-700 text-xs font-semibold cursor-pointer"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. DELETED ORDERS TAB -->
            <div v-if="activeTab === 'deleted-orders' && canManageOrders" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <h2 class="text-base font-bold text-slate-900 mb-3">Search Deleted Orders</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Invoice Number</label>
                            <input
                                v-model="deletedFilters.invoice_number"
                                @input="fetchDeletedOrders"
                                type="text"
                                placeholder="Search deleted invoice..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 mb-1">Customer Number</label>
                            <input
                                v-model="deletedFilters.customer_number"
                                @input="fetchDeletedOrders"
                                type="text"
                                placeholder="Search customer..."
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>
                        <button
                            @click="deletedFilters = { invoice_number: '', customer_number: '' }; fetchDeletedOrders()"
                            class="bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold py-2 px-4 rounded-lg text-sm cursor-pointer"
                        >
                            Reset Search
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Logically Deleted Orders</h2>
                            <p class="text-xs text-slate-500">These orders are hidden from active operations and public tracking until restored.</p>
                        </div>
                        <span class="text-xs text-slate-500">{{ deletedOrders.length }} deleted orders</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Invoice #</th>
                                    <th class="px-6 py-3">Customer</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-if="deletedOrders.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                                        No deleted orders found.
                                    </td>
                                </tr>
                                <tr v-for="ord in deletedOrders" :key="ord.invoice_number" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono font-bold text-slate-700">{{ ord.invoice_number }}</td>
                                    <td class="px-6 py-3">
                                        <p class="font-semibold text-slate-800">{{ ord.customer?.company_name || 'N/A' }}</p>
                                        <p class="text-xs text-slate-500">{{ ord.customer_number }}</p>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span :class="statusBadgeClass(ord.status)" class="px-2.5 py-1 rounded-full text-xs font-bold">
                                            {{ ord.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <button
                                            @click="openOrderModal(ord)"
                                            class="px-3 py-1 rounded bg-slate-200 hover:bg-slate-300 text-slate-800 text-xs font-semibold cursor-pointer"
                                        >
                                            View / Edit
                                        </button>
                                        <button
                                            @click="restoreOrder(ord)"
                                            class="px-3 py-1 rounded bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold cursor-pointer"
                                        >
                                            Restore Order
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 4. USER MANAGEMENT TAB (ADMINISTRATOR ONLY) -->
            <div v-if="activeTab === 'users' && isAdmin" class="space-y-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ editingUserId ? 'Edit Employee' : 'Register New Employee' }}
                        </h2>
                        <button
                            v-if="editingUserId"
                            @click="resetUserForm"
                            class="text-xs text-slate-500 hover:text-slate-800 underline cursor-pointer"
                        >
                            Cancel Edit
                        </button>
                    </div>

                    <form @submit.prevent="saveUser" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
                            <input
                                v-model="userForm.name"
                                type="text"
                                required
                                placeholder="Carlos Perez"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                            <input
                                v-model="userForm.email"
                                type="email"
                                required
                                placeholder="carlos@halcon.com"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">
                                Password {{ editingUserId ? '(optional)' : '' }}
                            </label>
                            <input
                                v-model="userForm.password"
                                type="password"
                                :required="!editingUserId"
                                minlength="6"
                                placeholder="Min 6 chars"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Assigned Role</label>
                            <select
                                v-model="userForm.role_id"
                                required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white"
                            >
                                <option value="" disabled>Select role...</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                        </div>

                        <button
                            type="submit"
                            class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold py-2 px-4 rounded-lg text-sm transition cursor-pointer"
                        >
                            {{ editingUserId ? 'Update User' : 'Register Employee' }}
                        </button>
                    </form>

                    <p v-if="userFeedback" class="mt-3 text-xs font-medium text-emerald-700">{{ userFeedback }}</p>
                    <p v-if="userError" class="mt-3 text-xs font-medium text-red-600">{{ userError }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-slate-900">Registered Employees</h2>
                        <span class="text-xs text-slate-500">{{ users.length }} total users</span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3">Name</th>
                                    <th class="px-6 py-3">Email</th>
                                    <th class="px-6 py-3">Role</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <tr v-for="u in users" :key="u.id" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-semibold text-slate-900">{{ u.name }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ u.email }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800">
                                            {{ u.role?.name || 'Unassigned' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <span
                                            :class="u.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                                            class="px-2.5 py-1 rounded-full text-xs font-bold"
                                        >
                                            {{ u.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right space-x-2">
                                        <button
                                            @click="startEditUser(u)"
                                            class="px-3 py-1 rounded bg-slate-200 hover:bg-slate-300 text-xs font-semibold text-slate-800 cursor-pointer"
                                        >
                                            Edit
                                        </button>
                                        <button
                                            v-if="u.id !== currentUser.id"
                                            @click="toggleUserStatus(u)"
                                            :class="u.is_active ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-emerald-100 hover:bg-emerald-200 text-emerald-700'"
                                            class="px-3 py-1 rounded text-xs font-semibold cursor-pointer"
                                        >
                                            {{ u.is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

        <!-- ORDER DETAILS & EDIT MODAL -->
        <div
            v-if="selectedOrder"
            class="fixed inset-0 bg-slate-950/60 flex items-center justify-center p-4 z-50"
        >
            <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200 space-y-5 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-slate-400">Order Details &amp; Editing</p>
                        <h3 class="text-xl font-bold text-slate-900 font-mono">{{ selectedOrder.invoice_number }}</h3>
                    </div>
                    <button
                        @click="selectedOrder = null"
                        class="text-slate-400 hover:text-slate-800 text-sm font-bold px-2 py-1 cursor-pointer"
                    >
                        ✕ Close
                    </button>
                </div>

                <form @submit.prevent="updateSelectedOrder" class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Customer Number</label>
                        <input
                            :value="selectedOrder.customer_number"
                            disabled
                            class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2 text-slate-600"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Company Name</label>
                        <input
                            v-model="editOrderForm.company_name"
                            :disabled="!canManageOrders"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 disabled:bg-slate-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Fiscal Data (Tax ID)</label>
                        <input
                            v-model="editOrderForm.fiscal_data"
                            :disabled="!canManageOrders"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 disabled:bg-slate-100"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Order Status</label>
                        <select
                            v-model="editOrderForm.status"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 bg-white font-semibold"
                        >
                            <option value="Ordered">Ordered</option>
                            <option value="In process">In process</option>
                            <option value="In route">In route</option>
                            <option value="Delivered">Delivered</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Delivery Address</label>
                        <input
                            v-model="editOrderForm.delivery_address"
                            :disabled="!canManageOrders"
                            type="text"
                            required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 disabled:bg-slate-100"
                        />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Notes</label>
                        <textarea
                            v-model="editOrderForm.notes"
                            rows="2"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2"
                        ></textarea>
                    </div>

                    <div class="sm:col-span-2 flex items-center justify-between pt-2 border-t border-slate-100">
                        <span v-if="modalFeedback" class="text-xs font-semibold text-emerald-700">{{ modalFeedback }}</span>
                        <span v-else class="text-xs text-slate-400">Changes reflect immediately in public tracking.</span>

                        <div class="space-x-2">
                            <button
                                type="button"
                                @click="selectedOrder = null"
                                class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300 text-xs font-semibold cursor-pointer"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold cursor-pointer"
                            >
                                Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

defineEmits(['go-public']);

const currentUser = ref(null);
const activeTab = ref('overview');
const loading = ref(false);
const loginError = ref('');

const loginForm = ref({
    login: '',
    password: '',
});

// Users & Roles state
const roles = ref([]);
const users = ref([]);
const editingUserId = ref(null);
const userFeedback = ref('');
const userError = ref('');
const userForm = ref({
    name: '',
    email: '',
    password: '',
    role_id: '',
});

// Orders state
const activeOrders = ref([]);
const deletedOrders = ref([]);
const orderFeedback = ref('');
const orderError = ref('');

const nowLocalInput = () => {
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    return now.toISOString().slice(0, 16);
};

const orderForm = ref({
    invoice_number: '',
    customer_number: '',
    company_name: '',
    fiscal_data: '',
    order_date: nowLocalInput(),
    delivery_address: '',
    notes: '',
});

const orderFilters = ref({
    invoice_number: '',
    customer_number: '',
    date: '',
    status: '',
});

const deletedFilters = ref({
    invoice_number: '',
    customer_number: '',
});

const selectedOrder = ref(null);
const modalFeedback = ref('');
const editOrderForm = ref({
    company_name: '',
    fiscal_data: '',
    delivery_address: '',
    status: 'Ordered',
    notes: '',
});

const isAdmin = computed(() => currentUser.value?.role?.name === 'Administrator');
const canManageOrders = computed(() =>
    ['Administrator', 'Sales'].includes(currentUser.value?.role?.name)
);

const roleDescription = (roleName) => {
    const descriptions = {
        Administrator: 'Full access to users, roles, and order administration.',
        Sales: 'Create, edit, logically delete, and restore customer orders.',
        Purchasing: 'Support orders that require purchasing missing materials.',
        Warehouse: 'Process and prepare customer orders.',
        Route: 'Manage routed orders and complete deliveries.',
    };
    return descriptions[roleName] || 'Standard employee access.';
};

const availableModules = computed(() => {
    const role = currentUser.value?.role?.name;
    const modules = [
        {
            badge: 'Operations',
            title: 'Order Management (Active Orders)',
            description: 'Create new orders in Ordered status, search/filter active orders, view details, and edit.',
            action: () => openOrdersModule(),
        },
    ];

    if (role === 'Administrator' || role === 'Sales') {
        modules.push({
            badge: 'Archive',
            title: 'Deleted Orders & Restoration',
            description: 'Inspect logically deleted orders, edit details, and restore them back to active status.',
            action: () => openDeletedOrdersModule(),
        });
    }

    if (role === 'Administrator') {
        modules.push({
            badge: 'Administration',
            title: 'User & Role Management',
            description: 'Register employees, edit credentials, assign roles, and activate or deactivate accounts.',
            action: () => openUsersModule(),
        });
    }

    return modules;
});

onMounted(() => {
    const saved = localStorage.getItem('halcon_user');
    if (saved) {
        currentUser.value = JSON.parse(saved);
    }
});

const authHeaders = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-User-Id': currentUser.value?.id,
});

const handleLogin = async () => {
    loading.value = true;
    loginError.value = '';

    try {
        const res = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify(loginForm.value),
        });

        const data = await res.json();

        if (!res.ok) {
            loginError.value = data.message || 'Login failed.';
            return;
        }

        currentUser.value = data.user;
        localStorage.setItem('halcon_user', JSON.stringify(data.user));
        activeTab.value = 'overview';
        loginForm.value = { login: '', password: '' };
    } catch (e) {
        loginError.value = 'Network error connecting to authentication server.';
    } finally {
        loading.value = false;
    }
};

const logout = () => {
    currentUser.value = null;
    localStorage.removeItem('halcon_user');
    activeTab.value = 'overview';
};

// ORDER METHODS
const openOrdersModule = async () => {
    activeTab.value = 'orders';
    await Promise.all([fetchActiveOrders(), fetchNextInvoice()]);
};

const fetchNextInvoice = async () => {
    const res = await fetch('/api/orders/next-invoice', { headers: authHeaders() });
    if (res.ok) {
        const data = await res.json();
        orderForm.value.invoice_number = data.next_invoice;
    }
};

const fetchActiveOrders = async () => {
    const params = new URLSearchParams();
    if (orderFilters.value.invoice_number) params.append('invoice_number', orderFilters.value.invoice_number);
    if (orderFilters.value.customer_number) params.append('customer_number', orderFilters.value.customer_number);
    if (orderFilters.value.date) params.append('date', orderFilters.value.date);
    if (orderFilters.value.status) params.append('status', orderFilters.value.status);

    const res = await fetch(`/api/orders?${params.toString()}`, { headers: authHeaders() });
    if (res.ok) {
        activeOrders.value = await res.json();
    }
};

const clearOrderFilters = () => {
    orderFilters.value = { invoice_number: '', customer_number: '', date: '', status: '' };
    fetchActiveOrders();
};

const createOrder = async () => {
    orderFeedback.value = '';
    orderError.value = '';

    const res = await fetch('/api/orders', {
        method: 'POST',
        headers: authHeaders(),
        body: JSON.stringify(orderForm.value),
    });

    const data = await res.json();

    if (!res.ok) {
        orderError.value = data.message || 'Error creating order.';
        return;
    }

    orderFeedback.value = `Order ${data.invoice_number} created with status "Ordered".`;
    orderForm.value = {
        invoice_number: '',
        customer_number: '',
        company_name: '',
        fiscal_data: '',
        order_date: nowLocalInput(),
        delivery_address: '',
        notes: '',
    };
    await Promise.all([fetchActiveOrders(), fetchNextInvoice()]);
};

const openOrderModal = (ord) => {
    selectedOrder.value = ord;
    modalFeedback.value = '';
    editOrderForm.value = {
        company_name: ord.customer?.company_name || '',
        fiscal_data: ord.customer?.fiscal_data || '',
        delivery_address: ord.delivery_address || '',
        status: ord.status || 'Ordered',
        notes: ord.notes || '',
    };
};

const updateSelectedOrder = async () => {
    modalFeedback.value = '';
    const res = await fetch(`/api/orders/${selectedOrder.value.invoice_number}`, {
        method: 'PUT',
        headers: authHeaders(),
        body: JSON.stringify(editOrderForm.value),
    });

    if (res.ok) {
        modalFeedback.value = 'Order updated successfully!';
        await Promise.all([fetchActiveOrders(), fetchDeletedOrders()]);
        setTimeout(() => {
            selectedOrder.value = null;
        }, 600);
    }
};

const logicalDeleteOrder = async (ord) => {
    const res = await fetch(`/api/orders/${ord.invoice_number}`, {
        method: 'DELETE',
        headers: authHeaders(),
    });
    if (res.ok) {
        await fetchActiveOrders();
    }
};

const openDeletedOrdersModule = async () => {
    activeTab.value = 'deleted-orders';
    await fetchDeletedOrders();
};

const fetchDeletedOrders = async () => {
    const params = new URLSearchParams();
    if (deletedFilters.value.invoice_number) params.append('invoice_number', deletedFilters.value.invoice_number);
    if (deletedFilters.value.customer_number) params.append('customer_number', deletedFilters.value.customer_number);

    const res = await fetch(`/api/orders/archived?${params.toString()}`, { headers: authHeaders() });
    if (res.ok) {
        deletedOrders.value = await res.json();
    }
};

const restoreOrder = async (ord) => {
    const res = await fetch(`/api/orders/${ord.invoice_number}/restore`, {
        method: 'PATCH',
        headers: authHeaders(),
    });
    if (res.ok) {
        await Promise.all([fetchDeletedOrders(), fetchActiveOrders()]);
    }
};

// USER METHODS
const openUsersModule = async () => {
    if (!isAdmin.value) return;
    activeTab.value = 'users';
    await Promise.all([fetchRoles(), fetchUsers()]);
};

const fetchRoles = async () => {
    const res = await fetch('/api/roles', { headers: authHeaders() });
    if (res.ok) roles.value = await res.json();
};

const fetchUsers = async () => {
    const res = await fetch('/api/users', { headers: authHeaders() });
    if (res.ok) users.value = await res.json();
};

const saveUser = async () => {
    userFeedback.value = '';
    userError.value = '';

    const url = editingUserId.value ? `/api/users/${editingUserId.value}` : '/api/users';
    const method = editingUserId.value ? 'PUT' : 'POST';

    const res = await fetch(url, {
        method,
        headers: authHeaders(),
        body: JSON.stringify(userForm.value),
    });

    const data = await res.json();

    if (!res.ok) {
        userError.value = data.message || 'Error saving employee.';
        return;
    }

    userFeedback.value = editingUserId.value ? 'Employee updated successfully.' : 'Employee registered successfully.';
    resetUserForm();
    await fetchUsers();
};

const startEditUser = (u) => {
    editingUserId.value = u.id;
    userFeedback.value = '';
    userError.value = '';
    userForm.value = {
        name: u.name,
        email: u.email,
        password: '',
        role_id: u.role_id || '',
    };
};

const resetUserForm = () => {
    editingUserId.value = null;
    userForm.value = { name: '', email: '', password: '', role_id: '' };
};

const toggleUserStatus = async (u) => {
    const res = await fetch(`/api/users/${u.id}/toggle-status`, {
        method: 'PATCH',
        headers: authHeaders(),
    });
    if (res.ok) {
        await fetchUsers();
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