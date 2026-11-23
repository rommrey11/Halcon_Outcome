<template>
    <AdminPortal v-if="view === 'admin'" @go-public="switchView('public')" />
    <OrderTracking v-else @go-admin="switchView('admin')" />
</template>

<script setup>
import { ref, onMounted } from 'vue';
import OrderTracking from './components/OrderTracking.vue';
import AdminPortal from './components/AdminPortal.vue';

const view = ref('public');

onMounted(() => {
    if (window.location.pathname.startsWith('/admin') || localStorage.getItem('halcon_user')) {
        view.value = 'admin';
    }
});

const switchView = (target) => {
    view.value = target;
    window.history.pushState({}, '', target === 'admin' ? '/admin' : '/');
};
</script>