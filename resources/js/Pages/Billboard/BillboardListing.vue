<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {h, ref, watchEffect} from "vue";
import BillboardProfile from "@/Pages/Billboard/BillboardProfile/BillboardProfile.vue";
import {usePage} from "@inertiajs/vue3";

const props = defineProps({
    profileCount: Number
})

const getPendingCounts = async () => {
    try {
        const response = await axios.get('/getPendingCounts');
        tabs.value[1].rowCount = response.data.pendingBonusWithdrawal
    } catch (error) {
        console.error('Error pending counts:', error);
    }
};

getPendingCounts();

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getPendingCounts();
    }
});
</script>

<template>
    <AuthenticatedLayout :title="$t('public.billboard')">
        <div class="flex flex-col gap-5 md:gap-8">
            <BillboardProfile/>
        </div>
    </AuthenticatedLayout>
</template>
