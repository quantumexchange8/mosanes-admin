<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {IconSearch, IconCircleXFilled, IconUserFilled} from "@tabler/icons-vue";
import InputText from "primevue/inputtext";
import Button from "@/Components/Button.vue";
import {ref, watch} from "vue";
import InputSwitch from 'primevue/inputswitch';
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {transactionFormat} from "@/Composables/index.js";

const search = ref('');
const checked = ref(true);
const upline = ref(null);
const parent = ref([]);
const children = ref([]);
const upline_id = ref();
const parent_id = ref();
const selectedParent = ref(null);
const selectedDownlineId = ref(null);
const { formatAmount } = transactionFormat();

const getNetwork = async (filterUplineId = upline_id.value, filterParentId = parent_id.value) => {
    try {
        let url = `/member/getDownlineData`;

        if (filterUplineId) {
            url += `?upline_id=${filterUplineId}`;
        }

        if (filterParentId) {
            url += `&parent_id=${filterParentId}`;
        }

        const response = await axios.get(url);

        upline.value = response.data.upline;
        parent.value = response.data.parent;
        children.value = response.data.direct_children;
    } catch (error) {
        console.error('Error get network:', error);
    }
};

getNetwork();

const selectDownline = (downlineId) => {
    upline_id.value = parent.value.id;
    parent_id.value = downlineId;

    getNetwork(upline_id.value, parent_id.value)
}
</script>

<template>
    <AuthenticatedLayout title="Member Network">
        <div class="flex flex-col items-center gap-5">
            <div class="flex flex-col md:flex-row gap-3 items-center self-stretch">
                <div class="relative w-full md:w-60">
                    <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                        <IconSearch size="20" stroke-width="1.25" />
                    </div>
                    <InputText v-model="search" placeholder="Keyword Search" class="font-normal pl-12 w-full md:w-60" />
                    <div
                        v-if="search"
                        class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                        @click=""
                    >
                        <IconCircleXFilled size="16" />
                    </div>
                </div>
                <div class="grid grid-cols-2 w-full gap-3">
                    <div class="flex items-center gap-2">
                        <div class="p-2.5 flex items-center hover:bg-gray-100 rounded-full">
                            <InputSwitch v-model="checked" />
                        </div>
                        <div class="text-gray-950 text-sm font-medium">
                            Show Upline
                        </div>
                    </div>
                    <div class="w-full flex justify-end">
                        <Button
                            variant="gray-flat"
                            @click="exportCSV($event)"
                            class="w-full md:w-auto"
                        >
                            Collapse All
                        </Button>
                    </div>
                </div>
            </div>

            <div class="flex flex-col items-center gap-5 w-full">
                <!-- Upline Section -->
                <div v-if="checked && upline" class="flex flex-col items-center gap-5 w-full">
                    <div class="rounded flex items-center self-stretch py-2 px-3 bg-gray-100">
                        <span class="text-xs font-semibold text-gray-700 uppercase">{{ $t('public.level' ) }} {{ upline.level ?? 0 }}</span>
                    </div>
                    <div class="flex gap-5 justify-center">
                        <div
                            class="rounded-xl pt-3 flex flex-col items-center shadow-toast w-[148px]"
                            :class="{
                                      'bg-gradient-to-r from-gray-900 to-gray-500': upline.role === 'agent' && upline.level === 0,
                                      'bg-gradient-to-r from-warning-500 to-[#FDEF5B]': upline.role === 'agent',
                                      'bg-gradient-to-r from-primary-700 to-[#0BA5EC]': upline.role === 'member',
                                    }"
                        >
                            <div class="py-2 px-3 bg-white flex items-center justify-between w-full gap-3">
                                <div class="flex flex-col flex-grow w-[84px]">
                                    <div class="w-full text-xs font-semibold text-gray-950 truncate">
                                        {{ upline.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ upline.id_number }}
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-full shrink-0 grow-0 overflow-hidden">
                                    <img :src="upline.profile_photo" alt="Profile Photo" />
                                </div>
                            </div>
                            <div class="pb-3 px-3 bg-white rounded-b-[10.8px] flex items-center justify-between self-stretch">
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-warning-50 text-warning-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(upline.total_agent_count, 0) }}
                                    </div>
                                </div>
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-primary-50 text-primary-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(upline.total_member_count, 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Parent Section -->
                <div v-if="parent" class="flex flex-col items-center gap-5 w-full">
                    <div class="rounded flex items-center self-stretch py-2 px-3 bg-gray-100">
                        <span class="text-xs font-semibold text-gray-700 uppercase">{{ $t('public.level' ) }} {{ parent.level ?? 0 }}</span>
                    </div>
                    <div class="flex gap-5 justify-center">
                        <div
                            class="rounded-xl pt-3 flex flex-col items-center shadow-toast w-[148px]"
                            :class="{
                                      'bg-gradient-to-r from-gray-900 to-gray-500': parent.role === 'agent' && parent.level === 0,
                                      'bg-gradient-to-r from-warning-500 to-[#FDEF5B]': parent.role === 'agent',
                                      'bg-gradient-to-r from-primary-700 to-[#0BA5EC]': parent.role === 'member',
                                    }"
                        >
                            <div class="py-2 px-3 bg-white flex items-center justify-between w-full gap-3">
                                <div class="flex flex-col flex-grow w-[84px]">
                                    <div class="w-full text-xs font-semibold text-gray-950 truncate">
                                        {{ parent.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ parent.id_number }}
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-full shrink-0 grow-0 overflow-hidden">
                                    <img :src="parent.profile_photo" alt="Profile Photo" />
                                </div>
                            </div>
                            <div class="pb-3 px-3 bg-white rounded-b-[10.8px] flex items-center justify-between self-stretch">
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-warning-50 text-warning-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(parent.total_agent_count, 0) }}
                                    </div>
                                </div>
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-primary-50 text-primary-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(parent.total_member_count, 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Children Section -->
                <div v-if="children.length" class="flex flex-col items-center gap-5 w-full">
                    <div class="rounded flex items-center self-stretch py-2 px-3 bg-gray-100">
                        <span class="text-xs font-semibold text-gray-700 uppercase">{{ $t('public.level' ) }} {{ children[0].level ?? 0 }}</span>
                    </div>
                    <div class="flex gap-5 justify-center">
                        <div
                            v-for="downline in children"
                            :key="downline.id"
                            class="rounded-xl pt-3 flex flex-col items-center shadow-toast w-[148px] border border-gray-25 select-none cursor-pointer"
                            :class="{
                                      'agent-bg hover:border-warning-500': downline.role === 'agent',
                                      'member-bg hover:border-primary-500': downline.role === 'member',
                                    }"
                            @click="selectDownline(downline.id)"
                        >
                            <div class="py-2 px-3 bg-white flex items-center justify-between w-full gap-3">
                                <div class="flex flex-col flex-grow w-[84px]">
                                    <div class="w-full text-xs font-semibold text-gray-950 truncate">
                                        {{ downline.name }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ downline.id_number }}
                                    </div>
                                </div>
                                <div class="w-7 h-7 rounded-full shrink-0 grow-0 overflow-hidden">
                                    <img :src="downline.profile_photo" alt="Profile Photo" />
                                </div>
                            </div>
                            <div class="pb-3 px-3 bg-white rounded-b-[10px] flex items-center justify-between self-stretch">
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-warning-50 text-warning-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(downline.total_agent_count, 0) }}
                                    </div>
                                </div>
                                <div class="flex gap-2 items-center w-full">
                                    <div class="flex items-center justify-center w-4 h-4 rounded-full grow-0 shrink-0 bg-primary-50 text-primary-500">
                                        <IconUserFilled size="10" />
                                    </div>
                                    <div class="text-xs text-gray-950 font-medium">
                                        {{ formatAmount(downline.total_member_count, 0) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.agent-bg {
    background: linear-gradient(to right, #F79009, #FDEF5B);
}

.agent-bg:hover {
    background: linear-gradient(90deg, #F79009, #FDEF5B, #F79009, #FDEF5B);
    background-size: 400%;
    animation: agent-gradient 3s ease infinite;
}

@keyframes agent-gradient {
    0% {
        background-position: 0 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0 50%;
    }
}

.member-bg {
    background: linear-gradient(to right, #004EEB, #0BA5EC);
}

.member-bg:hover {
    background: linear-gradient(90deg, #004EEB, #0BA5EC, #004EEB, #0BA5EC);
    background-size: 400%;
    animation: member-gradient 3s ease infinite;
}

@keyframes member-gradient {
    0% {
        background-position: 0 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0 50%;
    }
}
</style>
