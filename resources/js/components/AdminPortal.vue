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
            <!-- Welcome Banner -->
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
                        Dashboard Overview
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

            <!-- OVERVIEW TAB: Role-Based Modules -->
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
                        v-if="mod.action"
                        @click="mod.action"
                        class="mt-5 w-full bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold py-2.5 rounded-lg transition cursor-pointer"
                    >
                        Open Module
                    </button>
                    <div v-else class="mt-5 text-xs font-medium text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2">
                        Ready for Order Workflow (Phase 2 &amp; 4)
                    </div>
                </div>
            </div>

            <!-- USER MANAGEMENT TAB (ADMINISTRATOR ONLY) -->
            <div v-if="activeTab === 'users' && isAdmin" class="space-y-6">
                <!-- Register / Edit Employee Form -->
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

                <!-- Registered Employees Table -->
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

const isAdmin = computed(() => currentUser.value?.role?.name === 'Administrator');

const roleDescription = (roleName) => {
    const descriptions = {
        Administrator: 'Manage company users, roles, and system access.',
        Sales: 'Create and manage customer orders.',
        Purchasing: 'Support orders that require purchasing missing materials.',
        Warehouse: 'Process and prepare customer orders.',
        Route: 'Manage routed orders and upload delivery evidence.',
    };
    return descriptions[roleName] || 'Standard employee access.';
};

const availableModules = computed(() => {
    const role = currentUser.value?.role?.name;
    const modules = [];

    if (role === 'Administrator') {
        modules.push({
            badge: 'Administration',
            title: 'User & Role Management',
            description: 'Register employees, edit credentials, assign departmental roles, and activate or deactivate accounts.',
            action: () => openUsersModule(),
        });
    }

    if (role === 'Administrator' || role === 'Sales') {
        modules.push({
            badge: 'Sales Department',
            title: 'Customer Orders (Create & Manage)',
            description: 'Create new orders starting in Ordered status, search active orders, and manage customer details.',
        });
    }

    if (role === 'Administrator' || role === 'Warehouse') {
        modules.push({
            badge: 'Warehouse',
            title: 'Order Preparation',
            description: 'Process incoming Ordered shipments and move them into In process status.',
        });
    }

    if (role === 'Administrator' || role === 'Purchasing') {
        modules.push({
            badge: 'Purchasing',
            title: 'Material Restock Support',
            description: 'Review orders flagged for missing materials and coordinate restocking.',
        });
    }

    if (role === 'Administrator' || role === 'Route') {
        modules.push({
            badge: 'Route & Delivery',
            title: 'Dispatch & Delivery Evidence',
            description: 'Manage orders In route, upload delivery evidence, and mark orders as Delivered.',
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

const authHeaders = () => ({
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'X-User-Id': currentUser.value?.id,
});

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
    userForm.value = {
        name: '',
        email: '',
        password: '',
        role_id: '',
    };
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
</script>