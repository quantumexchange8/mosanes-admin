<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import {h, ref, watchEffect, onMounted, onUnmounted} from "vue";
import {IconCheck, IconCircleXFilled, IconCopy, IconSearch, IconUserCheck, IconX} from "@tabler/icons-vue";
import Loader from "@/Components/Loader.vue";
import DataTable from "primevue/datatable";
import InputText from "primevue/inputtext";
import Column from "primevue/column";
import Button from "@/Components/Button.vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import {wTrans} from "laravel-vue-i18n";
import {FilterMatchMode} from "primevue/api";
import {useForm, usePage} from "@inertiajs/vue3";
import dayjs from 'dayjs'
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import Dialog from "primevue/dialog";
import InputLabel from "@/Components/InputLabel.vue";
import Chip from "primevue/chip";
import Textarea from "primevue/textarea";
import Empty from "@/Components/Empty.vue";
import Tag from "primevue/tag";
import InputError from "@/Components/InputError.vue";
import {useConfirm} from "primevue/useconfirm";

const loading = ref(false);
const dt = ref();
const pendingKycs = ref();
const filteredValue = ref();
const paginator_caption = wTrans('public.paginator_caption');

const getResults = async () => {
    loading.value = true;

    try {
        const response = await axios.get('/pending/getPendingKycData');
        pendingKycs.value = response.data.pendingKycs;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

getResults();

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
    user_name: { value: null, matchMode: FilterMatchMode.CONTAINS },
    user_email: { value: null, matchMode: FilterMatchMode.CONTAINS },
    from: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const clearFilterGlobal = () => {
    filters.value['global'].value = null;
    filteredValue.value = null;
}

watchEffect(() => {
    if (usePage().props.toast !== null) {
        getResults();
    }
});

const visible = ref(false);
const pendingData = ref({});
const approvalAction = ref('');

const confirm = useConfirm();

const requireAccountConfirmation = (accountType, userId, userName) => {
    const messages = {
        verification: {
            group: 'headless',
            color: 'primary',
            icon: h(IconCheck),
            header: wTrans('public.approve_kyc'),
            message: wTrans('public.approve_kyc_message', {name: `${userName}`}),
            cancelButton: wTrans('public.cancel'),
            acceptButton: wTrans('public.approve'),
            action: () => {
                submit(userId);
            }
        }
    };

    const { group, color, icon, header, message, actionType, cancelButton, acceptButton, action } = messages[accountType];

    confirm.require({
        group,
        color,
        icon,
        header,
        actionType,
        message,
        cancelButton,
        acceptButton,
        accept: action
    });
}

const handleApproval = (action, data = null) => {
    approvalAction.value = '';
    if (data) {
        pendingData.value = data;
    }
    visible.value = true;
    approvalAction.value = action;
    if (approvalAction.value === 'approve') {
        requireAccountConfirmation('verification', pendingData.value.id, pendingData.value.name);
        visible.value = false;
    }
}

const closeDialog = () => {
    visible.value = false;
    approvalAction.value = '';
}

const form = useForm({
    user_id: '',
    action: '',
    remarks: '',
    type: 'verification',
})

const submit = (userId) => {
    form.user_id = userId;
    form.action = approvalAction.value;
    form.post(route('pending.kycApproval'), {
        onSuccess: () => {
            closeDialog();
            approvalAction.value = '';
            form.reset();
        },
    });
};

const handleFilter = (e) => {
    filteredValue.value = e.filteredValue;
};

const activeTag = ref(null);
const tooltipText = ref('copy');
const copyToClipboard = (addressType, text) => {
    const textToCopy = text;

    const textArea = document.createElement('textarea');
    document.body.appendChild(textArea);

    textArea.value = textToCopy;
    textArea.select();

    try {
        const successful = document.execCommand('copy');

        tooltipText.value = 'copied';
        activeTag.value = addressType;
        setTimeout(() => {
            tooltipText.value = 'copy';
            activeTag.value = null;
        }, 1500);
    } catch (err) {
        console.error('Copy to clipboard failed:', err);
    }

    document.body.removeChild(textArea);
}

const exportXLSX = () => {
    // Retrieve the array from the reactive proxy
    const data = filteredValue.value;

    // Specify the headers
    const headers = [
        wTrans('public.name'),
        wTrans('public.email'),
        wTrans('public.date'),
    ];

    // Map the array data to XLSX rows
    const rows = data.map(obj => {
        return [
            obj.name !== undefined ? obj.name : '',
            obj.email !== undefined ? obj.email : '',
            obj.submitted_at !== undefined ? dayjs(obj.submitted_at).format('YYYY/MM/DD') : '',
        ];
    });

    // Combine headers and rows into a single data array
    const sheetData = [headers, ...rows];

    // Create the XLSX content
    let csvContent = "data:text/xlsx;charset=utf-8,";
    
    sheetData.forEach((rowArray) => {
        const row = rowArray.join("\t"); // Use tabs for column separation
        csvContent += row + "\r\n"; // Add a new line after each row
    });

    // Create a temporary link element
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "export.xlsx");

    // Append the link to the document and trigger the download
    document.body.appendChild(link);
    link.click();

    // Clean up by removing the link
    document.body.removeChild(link);
};

const isMobile = ref(false)
const updateScreenSize = () => isMobile.value = window.innerWidth < 768;

onMounted(() => {
  updateScreenSize(); // set initially
  window.addEventListener('resize', updateScreenSize); // watch resize
});

onUnmounted(() => {
  window.removeEventListener('resize', updateScreenSize);
});

const visiblePhoto = ref(false);
const selectedKycVerification = ref(null);
const openPhotoDialog = (verification) => {
    visiblePhoto.value = true;
    selectedKycVerification.value = verification;
}

const rowClicked = (data) => {
    approvalAction.value = '';
    pendingData.value = data;
    visible.value = true;
    form.reset();
}

</script>

<template>
    <AuthenticatedLayout :title="$t('public.pending_kyc')">
        <div class="p-6 flex flex-col items-center justify-center self-stretch gap-6 border border-gray-200 bg-white shadow-table rounded-2xl">
            <DataTable
                v-model:filters="filters"
                :value="pendingKycs"
                :paginator="pendingKycs?.length > 0 && filteredValue > 0"
                removableSort
                :rows="50"
                :rowsPerPageOptions="[10, 20, 50, 100]"
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                :currentPageReportTemplate="paginator_caption"
                :globalFilterFields="['user_name', 'user_email', 'from']"
                ref="dt"
                :selectionMode="isMobile ? 'single' : null"
                @row-click="isMobile && rowClicked($event.data)"
                :loading="loading"
                @filter="handleFilter"
            >
                <template #header>
                    <div class="flex flex-col md:flex-row gap-3 md:justify-between items-center self-stretch md:pb-6">
                        <div class="relative w-full md:w-60">
                            <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                                <IconSearch size="20" stroke-width="1.25" />
                            </div>
                            <InputText v-model="filters['global'].value" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full md:w-60" />
                            <div
                                v-if="filters['global'].value !== null"
                                class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                                @click="clearFilterGlobal"
                            >
                                <IconCircleXFilled size="16" />
                            </div>
                        </div>
                        <Button
                            variant="primary-outlined"
                            @click="exportCSV($event)"
                            class="w-full md:w-auto"
                        >
                            {{ $t('public.export') }}
                        </Button>
                    </div>
                </template>
                <template #empty><Empty :title="$t('public.empty_pending_request_title')" :message="$t('public.empty_pending_request_message')" /></template>
                <template #loading>
                    <div class="flex flex-col gap-2 items-center justify-center">
                        <Loader />
                        <span class="text-sm text-gray-700">{{ $t('public.loading') }}</span>
                    </div>
                </template>
                <template v-if="pendingKycs?.length > 0 && filteredValue?.length > 0">
                    <Column field="name" sortable :header="$t('public.member')" style="width: 25%" class="hidden md:table-cell">
                        <template #body="slotProps">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                    <div v-if="slotProps.data.user_profile_photo">
                                        <img :src="slotProps.data.user_profile_photo" alt="Profile Photo" />
                                    </div>
                                    <div v-else>
                                        <DefaultProfilePhoto />
                                    </div>
                                </div>
                                <div class="flex flex-col items-start">
                                    <div class="font-medium">
                                        {{ slotProps.data.user_name }}
                                    </div>
                                    <div class="text-gray-500 text-xs">
                                        {{ slotProps.data.user_email }}
                                    </div>
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="submitted_at" header="" sortable style="width: 25%" class="hidden md:table-cell">
                        <template #header>
                            <span class="hidden md:block items-center justify-center">{{ $t('public.submitted_at') }}</span>
                        </template>
                        <template #body="slotProps">
                                {{ dayjs(slotProps.data.submitted_at).format('YYYY/MM/DD') }}
                        </template>
                    </Column>
                    <Column field="kyc_files" :header="$t('public.uploaded')" style="width: 25%" class="hidden md:table-cell">
                        <template #body="slotProps">
                            <div class="flex flex-row gap-3">
                                <div v-for="file in slotProps.data.kyc_files" :key="file.id" @click="openPhotoDialog(file)" 
                                    class="flex items-center bg-white rounded border border-gray-200 cursor-pointer hover:bg-gray-100"
                                >
                                    <img :src="file.original_url" :alt="file.file_name" class="w-16 h-12 rounded" />
                                </div>
                            </div>
                        </template>
                    </Column>
                    <Column field="action" :header="$t('public.action')" style="width: 25%" class="hidden md:table-cell">
                        <template #body="slotProps">
                            <div class="flex flex-row gap-3">
                                <Button
                                    variant="primary-text"
                                    size="sm"
                                    type="button"
                                    iconOnly
                                    pill
                                    @click="handleApproval('approve', slotProps.data)"
                                >
                                    <IconCheck size="16" stroke-width="1.25" />
                                </Button>
                                <Button
                                    variant="error-text"
                                    size="sm"
                                    type="button"
                                    iconOnly
                                    pill
                                    @click="handleApproval('reject', slotProps.data)"
                                >
                                    <IconX size="16" stroke-width="1.25" />
                                </Button>
                            </div>
                        </template>
                    </Column>
                    <Column class="md:hidden">
                        <template #body="slotProps">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-7 h-7 rounded-full overflow-hidden grow-0 shrink-0">
                                        <DefaultProfilePhoto />
                                    </div>
                                    <div class="flex flex-col items-start">
                                        <div class="text-sm font-semibold">
                                            {{ slotProps.data.user_name }}
                                        </div>
                                        <div class="text-gray-500 text-xs">
                                            {{ slotProps.data.user_email }}
                                        </div>
                                    </div>
                                </div>
                                <div class="overflow-hidden text-right text-ellipsis font-semibold">
                                    {{ dayjs(slotProps.data.submitted_at).format('YYYY/MM/DD') }}
                                </div>
                            </div>
                        </template>
                    </Column>
                </template>
            </DataTable>

            <Dialog
                v-model:visible="visible"
                modal
                :header="$t('public.pending_kyc', { action: approvalAction ? $t(`public.${approvalAction}`) : '' })"
                class="dialog-xs md:dialog-md"
                :dismissableMask="true"
            >
                <template
                    v-if="!approvalAction"
                >
                    <div class="flex flex-col items-center gap-3 self-stretch py-4 md:py-6">
                        <div class="flex flex-col md:flex-row items-center p-3 gap-3 self-stretch w-full bg-gray-50">
                            <div class="min-w-[140px] flex flex-col items-start w-full">
                                <span class="self-stretch text-gray-950 font-medium flex gap-1 relative" @click="copyToClipboard('name', pendingData.name)">
                                    {{ pendingData?.user_name || '-' }}
                                    <IconCopy 
                                        v-if="pendingData?.user_name"
                                        size="20" 
                                        stroke-width="1.25" 
                                        class="text-gray-500 inline-block cursor-pointer grow-0 shrink-0" 
                                        v-tooltip.top="$t(`public.${tooltipText}`)" 
                                        @click="copyToClipboard('user_name', pendingData.user_name)"
                                    />
                                    <Tag
                                        v-if="activeTag === 'name' && tooltipText === 'copied'"
                                        class="absolute -top-7 -right-3"
                                        severity="contrast"
                                        :value="$t(`public.${tooltipText}`)"
                                    ></Tag>
                                </span>
                                <span class="text-gray-500 text-sm">{{ pendingData?.user_email || '-' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col items-center p-3 gap-3 self-stretch w-full bg-gray-50">
                            <div class="flex flex-col md:flex-row md:items-center gap-1 self-stretch">
                                <div class="w-[140px] text-gray-500 text-sm">
                                    {{ $t('public.submission_date') }}
                                </div>
                                <div class="text-gray-950 text-sm font-medium">
                                    {{ dayjs(pendingData.submitted_at).format('YYYY/MM/DD HH:mm:ss') }}
                                </div>
                            </div>
                            <div class="flex flex-col gap-1 self-stretch">
                                <div class="w-[140px] text-gray-500 text-sm">
                                    {{ $t('public.uploaded_files') }}
                                </div>
                                <div class="flex flex-col md:flex-row gap-1">
                                    <div v-for="file in pendingData.kyc_files" :key="file.id" @click="openPhotoDialog(file)" 
                                        class="flex items-center gap-3 w-full p-2 bg-white rounded border border-gray-200 cursor-pointer hover:bg-gray-100"
                                    >
                                        <img :src="file.original_url" :alt="file.file_name" class="w-16 h-12 rounded" />
                                        <span class="text-sm text-gray-700 truncate">{{ file.file_name }}</span>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="flex justify-end items-center gap-4 self-stretch w-full">
                        <Button
                            type="button"
                            variant="error-flat"
                            class="w-full"
                            @click="handleApproval('reject')"
                        >
                            {{ $t('public.reject') }}
                        </Button>
                        <Button
                            variant="success-flat"
                            class="w-full"
                            @click="handleApproval('approve')"
                        >
                            {{ $t('public.approve') }}
                        </Button>
                    </div>
                </template>

                <template
                    v-if="approvalAction === 'reject'"
                >
                    <div class="flex flex-col items-center gap-3 self-stretch py-4 md:py-6">
                        <div class="flex flex-col md:flex-row items-center p-3 gap-3 self-stretch w-full bg-gray-50">
                            <div class="min-w-[140px] flex flex-col items-start w-full">
                                <span class="self-stretch text-gray-950 font-medium flex gap-1 relative" @click="copyToClipboard('name', pendingData.name)">
                                    {{ pendingData?.user_name || '-' }}
                                    <IconCopy 
                                        v-if="pendingData?.user_name"
                                        size="20" 
                                        stroke-width="1.25" 
                                        class="text-gray-500 inline-block cursor-pointer grow-0 shrink-0" 
                                        v-tooltip.top="$t(`public.${tooltipText}`)" 
                                        @click="copyToClipboard('user_name', pendingData.user_name)"
                                    />
                                    <Tag
                                        v-if="activeTag === 'name' && tooltipText === 'copied'"
                                        class="absolute -top-7 -right-3"
                                        severity="contrast"
                                        :value="$t(`public.${tooltipText}`)"
                                    ></Tag>
                                </span>
                                <span class="text-gray-500 text-sm">{{ pendingData?.user_email || '-' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col items-start gap-2 self-stretch">
                            <InputLabel for="remarks" :value="$t('public.remarks')"/>
                            <Textarea
                                id="remarks"
                                type="text"
                                class="h-20 flex self-stretch"
                                v-model="form.remarks"
                                placeholder="Enter reject reason..."
                                :invalid="!!form.errors.remarks"
                                rows="5"
                                cols="30"
                            />
                            <InputError :message="form.errors.remarks" />
                        </div>
                    </div>

                    <div class="flex justify-end items-center pt-6 gap-4 self-stretch w-full">
                        <Button
                            type="button"
                            variant="gray-tonal"
                            class="w-full"
                            @click="closeDialog"
                        >
                            {{ $t('public.cancel') }}
                        </Button>
                        <Button
                            variant="primary-flat"
                            class="w-full"
                            @click="submit(pendingData.id)"
                        >
                            {{ $t('public.confirm') }}
                        </Button>
                    </div>
                </template>
            </Dialog>
    
            <Dialog v-model:visible="visiblePhoto" modal headless class="dialog-xs md:dialog-md" :dismissableMask="true">
                <img
                    :src="selectedKycVerification?.original_url || '/img/member/kyc_sample_illustration.png'"
                    class="w-full"
                    alt="kyc_verification"
                />
            </Dialog>
        </div>
    </AuthenticatedLayout>
</template>
