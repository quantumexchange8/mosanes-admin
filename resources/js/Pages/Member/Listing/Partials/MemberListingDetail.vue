<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Button from '@/Components/Button.vue';
import { IconChevronRight } from '@tabler/icons-vue';
import { Edit01Icon, Download02Icon } from '@/Components/Icons/outline';
import { ref, h } from "vue";
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import Dialog from 'primevue/dialog';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputText from 'primevue/inputtext';
import { useForm } from '@inertiajs/vue3';
import MemberFinancialInfo from '@/Pages/Member/Listing/Partials/MemberFinancialInfo.vue';
import MemberTradingAccounts from '@/Pages/Member/Listing/Partials/MemberTradingAccounts.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import Dropdown from "primevue/dropdown";

const form = useForm({
    email: '',
    dial_code: '',
    phone: '',
    crypto: '',
    wallet_address: '',
});

function copyToClipboard(text) {
    const textToCopy = text;

    const textArea = document.createElement('textarea');
    document.body.appendChild(textArea);

    textArea.value = textToCopy;
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            console.log('Copied to clipboard:', textToCopy);
        } else {
            console.error('Unable to copy to clipboard.');
        }
    } catch (err) {
        console.error('Copy to clipboard failed:', err);
    }

    document.body.removeChild(textArea);
}

const tabs = ref([
      { title: 'Financial Info', component: h(MemberFinancialInfo), props: {  } },
      { title: 'Trading Accounts', component: h(MemberTradingAccounts), props: {  } },
]);

const dialogs = ref({
    contactInfo: { visible: false, data: null, updateUrl: 'member.updateContactInfo' },
    cryptoWallet: { visible: false, data: null, updateUrl: 'member.updateCryptoWalletInfo' },
    kyc: { visible: false, updateUrl: 'member.updateKYCStatus' },
});

const openDialog = (dialogName, data = null) => {
    form.reset();
    dialogs.value[dialogName].visible = true;
    if (data !== null) {
        dialogs.value[dialogName].data = data;
    }
};

const closeDialog = (dialogName) => {
    dialogs.value[dialogName].visible = false;
    form.reset();
};

const updateDialogData = (dialogName) => {
    form.post(route(dialogs.value[dialogName].updateUrl), {
        onSuccess: () => {
            closeDialog(dialogName);
        },
    })
};

const selectedDialCode = ref();
// const dialCodes = ref([
//     { country: 'United States', dial_code: '+1' },
//     { country: 'Italy', dial_code: '+39' },
//     { country: 'United Kingdom', dial_code: '+44' },
//     { country: 'Turkey', dial_code: '+90' },
//     { country: 'France', dial_code: '+33' }
// ]);

const dialCodes = ref([]);

function loadCountries(query, setOptions) {
    fetch('/member/loadCountries?query=' + query)
        .then(response => response.json())
        .then(results => {
            setOptions(
                results.map(user => {
                    return {
                        country: user.country,
                        dial_code: user.dial_code,
                    };
                })
            );
        });
}

function fetchDialCodes() {
    loadCountries('', (options) => {
        dialCodes.value = options;
    });
}

// Fetch initial data
fetchDialCodes();

const navigateToListing = () =>{
    window.location.href = route('member.listing');
};

</script>

<template>
    <AuthenticatedLayout title="Member Listing">
        <div class="flex flex-col gap-5">
            <div class="max-w-[1440px] flex flex-wrap md:flex-nowrap items-center content-center md:content-start gap-2 self-stretch">
                <Button variant="primary-text" size="sm" @click="navigateToListing">Member Listing</Button>
                <IconChevronRight 
                    :size="16"
                    stroke-width="1.25"
                />
                <span class="flex px-4 py-2 text-gray-400 text-center text-sm font-medium">William Clark - View Member Details</span>
            </div>

            <div class="flex flex-col h-[418px] md:h-auto md:min-w-[400px] px-5 md:px-8 py-6 items-center gap-6 md:gap-5 self-stretch rounded-2xl bg-white shadow-toast xl:hidden">
                <div class="flex flex-col md:flex-row pb-6 md:pb-5 items-start gap-4 self-stretch border-b border-gray-200">
                    <!-- below xs profile -->
                    <div class="flex justify-between items-start self-stretch md:hidden">
                        <img class="w-[52px] h-[52px] rounded-full" src="https://via.placeholder.com/52x52" />
                        <Button iconOnly size="sm" variant="gray-text" @click="openDialog('contactInfo', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                    </div>
                    <div class="flex flex-col items-start gap-1.5 self-stretch md:hidden">
                        <div class="flex items-center gap-3 self-stretch">
                            <div class="text-gray-950 font-semibold truncate">William Clark</div>
                            <StatusBadge value="member">Member</StatusBadge>
                            <StatusBadge value="active">Active</StatusBadge>
                        </div>
                        <div class="text-gray-700 text-sm">MID01972</div>
                    </div>
                    <!-- xs to xl profile -->
                    <div class="hidden md:flex items-center gap-5 flex-1 truncate">
                        <img class="w-16 h-16 rounded-full" src="https://via.placeholder.com/64x64" />
                        <div class="flex flex-col items-start gap-1.5 flex-1 truncate">
                            <div class="flex items-center gap-3 self-stretch">
                                <div class="text-gray-950 text-lg font-semibold truncate">William Clark</div>
                                <StatusBadge value="member">Member</StatusBadge>
                                <StatusBadge value="active">Active</StatusBadge>
                            </div>
                            <div class="text-gray-700 text-sm">MID01972</div>
                        </div>
                    </div>
                    <Button iconOnly size="sm" variant="gray-text" class="hidden md:flex" @click="openDialog('contactInfo', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                </div>
                <div class="flex flex-col justify-center items-center gap-5 md:gap-3 w-full">
                    <div class="flex justify-start items-start md:items-center gap-5 w-full">
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Email Address</div>
                            <div class="truncate text-gray-950 text-sm font-medium md:flex md:flex-col md:h-5 md:justify-center w-full">williamclark@gmail.com</div>
                        </div>
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Phone Number</div>
                            <div class="truncate text-gray-950 text-sm font-medium md:flex md:flex-col md:h-5 md:justify-center w-full">+60 167293782</div>
                        </div>
                    </div>
                    <div class="flex justify-start items-center gap-5 w-full">
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Group</div>
                            <StatusBadge value="member" > Member</StatusBadge>
                        </div>
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Upline</div>
                            <div class="flex items-center gap-2 w-full">
                                <img class="w-[26px] h-[26px] rounded-full" src="https://via.placeholder.com/26x26" />
                                <div class="truncate text-gray-950 text-sm font-medium w-full">Alice Johnson</div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-start items-center gap-5 w-full">
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Total Referred Member</div>
                            <div class="truncate text-gray-950 text-sm font-medium w-full">5</div>
                        </div>
                        <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                            <div class="text-gray-500 text-xs w-full truncate">Total Referred Agent</div>
                            <div class="truncate text-gray-950 text-sm font-medium w-full">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- below xs kyc and crypto wallet -->
            <div class="flex flex-col px-5 pt-4 pb-6 items-start gap-3 self-stretch rounded-2xl bg-white shadow-toast md:hidden">
                <div class="flex h-9 items-center gap-7 self-stretch">
                    <div class="flex flex-grow text-gray-950 text-sm font-bold">KYC Verification</div>
                </div>
                <Button variant="gray-tonal" class="flex p-3 items-center gap-5 self-stretch rounded-xl bg-gray-50 w-full" @click="openDialog('kyc')">
                    <img class="w-12 h-9 relative" src="https://via.placeholder.com/48x36" />
                    <div class="truncate text-gray-950 font-medium w-full">william_clark_ic.jpg</div>
                </Button>
                <div class="flex items-center gap-3">
                    <span class="text-gray-500 text-xs">Uploaded 2024/01/01 09:00:00</span>
                    <span class="text-gray-500 text-xs">.</span>
                    <span class="text-gray-500 text-xs">4MB</span>
                </div>
            </div>

            <div class="flex flex-col px-5 pt-4 pb-6 items-start gap-3 self-stretch rounded-2xl bg-white shadow-toast md:hidden">
                <div class="flex justify-between items-center self-stretch">
                    <div class="text-gray-950 text-sm font-bold">Crypto Wallet Information</div>
                    <Button iconOnly size="sm" variant="gray-text" @click="openDialog('cryptoWallet', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                </div>
                <div class="flex flex-col items-start gap-3 self-stretch">
                    <div class="flex flex-col justify-center items-start gap-2 self-stretch">
                        <div class="text-gray-500 text-xs">Cryptocurrency Network</div>
                        <div class="text-gray-950 text-sm font-medium">TRC20</div>
                    </div>
                    <div class="flex flex-col justify-center items-start gap-2 self-stretch w-full">
                        <div class="text-gray-500 text-xs">Wallet Address</div>
                        <div class="flex h-5 justify-center items-center self-stretch w-full">
                            <div @click="copyToClipboard('TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs')" class="text-gray-950 truncate text-sm font-medium w-full">TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- between xs to xl kyc and crypto wallet -->
            <div class="hidden md:flex xl:hidden flex-grow items-center gap-5 self-stretch w-full">
                <div class="flex flex-col px-8 pt-4 pb-6 items-start gap-3 flex-grow self-stretch rounded-2xl bg-white shadow-toast w-1/2">
                    <div class="flex h-9 items-center gap-7 self-stretch">
                        <div class="flex flex-grow text-gray-950 text-sm font-bold">KYC Verification</div>
                    </div>
                    <Button variant="gray-tonal" class="flex p-3 items-center gap-5 self-stretch rounded-xl bg-gray-50 w-full" @click="openDialog('kyc')">
                        <img class="w-12 h-9 relative" src="https://via.placeholder.com/48x36" />
                        <div class="truncate text-gray-950 font-medium w-full">william_clark_ic.jpg</div>
                    </Button>
                    <div class="flex items-center gap-3">
                        <span class="text-gray-500 text-xs">Uploaded 2024/01/01 09:00:00</span>
                        <span class="text-gray-500 text-xs">.</span>
                        <span class="text-gray-500 text-xs">4MB</span>
                    </div>
                </div>
                <div class="flex flex-col px-8 pt-4 pb-6 items-start gap-3 flex-grow rounded-2xl bg-white shadow-toast w-1/2">
                    <div class="flex justify-between items-center self-stretch">
                        <div class="text-gray-950 text-sm font-bold">Crypto Wallet Information</div>
                        <Button iconOnly size="sm" variant="gray-text" @click="openDialog('cryptoWallet', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                    </div>
                    <div class="flex flex-col items-start gap-3 self-stretch">
                        <div class="flex flex-col justify-center items-start gap-2 self-stretch">
                            <div class="text-gray-500 text-xs">Cryptocurrency Network</div>
                            <div class="text-gray-950 text-sm font-medium">TRC20</div>
                        </div>
                        <div class="flex flex-col justify-center items-start gap-2 self-stretch w-full">
                            <div class="text-gray-500 text-xs">Wallet Address</div>
                            <div class="flex h-5 justify-center items-center self-stretch w-full">
                                <div @click="copyToClipboard('TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs')" class="text-gray-950 truncate text-sm font-medium w-full">TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- above xl profile, kyc and crypto wallet -->
            <div class="hidden xl:flex max-w-[1440px] items-center gap-5 self-stretch">
                <div class="flex flex-col min-w-[540px] px-8 py-6 items-center gap-6 flex-grow rounded-2xl bg-white shadow-toast">
                    <div class="flex flex-col pb-6 items-start gap-4 self-stretch border-b border-gray-200">
                        <div class="flex justify-between items-start self-stretch">
                            <img class="w-16 h-16 rounded-full" src="https://via.placeholder.com/64x64" />
                            <Button iconOnly size="sm" variant="gray-text" @click="openDialog('contactInfo', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                        </div>
                        <div class="flex flex-col items-start gap-1.5 self-stretch">
                            <div class="flex items-center gap-3 self-stretch">
                                <div class="truncate text-gray-950 text-lg font-semibold">William Clark</div>
                                <StatusBadge value="member">Member</StatusBadge>
                                <StatusBadge value="active">Active</StatusBadge>
                            </div>
                            <div class="text-gray-700">MID01972</div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center items-center gap-5 self-stretch">
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Email Address</div>
                                <div class="truncate text-gray-950 text-sm font-medium w-full">williamclark@gmail.com</div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Phone Number</div>
                                <div class="truncate text-gray-950 text-sm font-medium w-full">+60 167293782</div>
                            </div>
                        </div>
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Group</div>
                                <StatusBadge value="member" > Member</StatusBadge>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Upline</div>
                                <div class="flex items-center gap-2 w-full">
                                    <img class="w-[26px] h-[26px] rounded-full" src="https://via.placeholder.com/26x26" />
                                    <div class="truncate text-gray-950 text-sm font-medium w-full">Alice Johnson</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-center items-center gap-5 self-stretch">
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Total Referred Member</div>
                                <div class="truncate text-gray-950 text-sm font-medium w-full">5</div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2 w-1/2">
                                <div class="text-gray-500 text-xs w-full truncate">Total Referred Agent</div>
                                <div class="truncate text-gray-950 text-sm font-medium w-full">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col items-center gap-5 flex-grow self-stretch">
                    <div class="flex flex-col items-start px-8 pt-4 pb-6 gap-3 flex-grow self-stretch rounded-2xl bg-white shadow-toast">
                        <div class="flex h-9 items-center gap-7 self-stretch">
                            <div class="flex flex-grow text-gray-950 text-sm font-bold">KYC Verification</div>
                        </div>
                        <Button variant="gray-tonal" class="flex p-3 items-center gap-5 self-stretch rounded-xl bg-gray-50 w-full" @click="openDialog('kyc')">
                            <img class="w-12 h-9 relative" src="https://via.placeholder.com/48x36" />
                            <div class="truncate text-gray-950 font-medium w-full">william_clark_ic.jpg</div>
                        </Button>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-xs">Uploaded 2024/01/01 09:00:00</span>
                            <span class="text-gray-500 text-xs">.</span>
                            <span class="text-gray-500 text-xs">4MB</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-start px-8 pt-4 pb-6 gap-3 flex-grow self-stretch rounded-2xl bg-white shadow-toast">
                        <div class="flex justify-between items-center self-stretch">
                            <div class="text-gray-950 text-sm font-bold">Crypto Wallet Information</div>
                            <Button iconOnly size="sm" variant="gray-text" @click="openDialog('cryptoWallet', myData)"><Edit01Icon class="w-4 h-4 text-gray-500"/></Button>
                        </div>
                        <div class="flex flex-col items-start gap-3 self-stretch">
                            <div class="flex flex-col justify-center items-start gap-2 self-stretch">
                                <div class="text-gray-500 text-xs">Cryptocurrency Network</div>
                                <div class="text-gray-950 text-sm font-medium">TRC20</div>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-2 self-stretch w-full">
                                <div class="text-gray-500 text-xs">Wallet Address</div>
                                <div class="flex h-5 justify-center items-center self-stretch w-full">
                                    <div @click="copyToClipboard('TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs')" class="text-gray-950 truncate text-sm font-medium w-full">TAzY2emMte5Zs4vJu2La8KmXwkzoE78qgs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <TabView class="flex flex-col gap-5">
                <TabPanel v-for="(tab, index) in tabs" :key="index" :header="tab.title">
                    <component :is="tab.component" v-bind="tab.props" />
                </TabPanel>
            </TabView>
        </div>

        <Dialog v-model:visible="dialogs.contactInfo.visible" modal header="Contact Information" class="dialog-xs md:dialog-sm">
            <form @submit.prevent="updateDialogData('contactInfo')">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1">
                        <InputLabel for="email" value="Email" />
                        <InputText 
                            id="email"
                            type="email"
                            class="block w-full"
                            v-model="form.email"
                            placeholder="Enter your email"
                            :invalid="!!form.errors.email"
                            autocomplete="email"
                        />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div class="flex flex-col gap-1 items-start self-stretch">
                        <InputLabel for="phone" value="Phone Number" />
                        <div class="flex items-center gap-2 self-stretch">
                            <Dropdown v-model="selectedDialCode" editable :options="dialCodes" optionLabel="dial_code" optionValue="dial_code" placeholder="+60" class="min-w-[100px]">
                                <!-- <template #value="slotProps">
                                    <div v-if="slotProps.value">
                                        <div>{{ slotProps }}</div>
                                    </div>
                                    <span v-else>
                                        {{ slotProps.placeholder }}
                                    </span>
                                </template> -->
                                <template #option="slotProps">
                                    <div>
                                        <div>{{ slotProps.option.country }}
                                            <span class="text-gray-500">{{ slotProps.option.dial_code }}</span>
                                        </div>
                                    </div>
                                </template>
                            </Dropdown>

                            <InputText 
                            id="phone"
                            type="phone"
                            class="block w-full"
                            v-model="form.phone"
                            placeholder="Enter your phone"
                            :invalid="!!form.errors.phone"
                            autocomplete="phone"
                        />
                        <InputError :message="form.errors.phone" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-end items-center pt-10 md:pt-7 gap-4 self-stretch">
                    <Button variant="gray-tonal" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="closeDialog('contactInfo')">Cancel</Button>
                    <Button variant="primary-flat" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="updateDialogData('contactInfo')">Save</Button>
                </div>
            </form>
        </Dialog>

        <Dialog v-model:visible="dialogs.kyc.visible" modal header="KYC Verification" class="dialog-xs md:dialog-lg">
            <template #header>
                <div class="flex flex-1 justify-between items-center self-stretch">
                    <div class="text-gray-950 font-bold md:text-lg">KYC Verification</div>
                    <Button iconOnly size="sm" variant="gray-text" pill class="focus:ring-0"><Download02Icon class="w-4 h-4 text-gray-500"/></Button>
                </div>
            </template>
            <div class="flex flex-col items-center self-stretch">
                <img class="w-full h-[216px] md:h-[496px] self-stretch relative" src="https://via.placeholder.com/1024x496" />
            </div>
            <div class="flex flex-col justify-end items-start pt-7 gap-5 self-stretch">
                <div class="flex flex-col items-start gap-1 self-stretch">
                    <div class="text-gray-950 text-sm md:text-base font-bold">Incomplete KYC Document?</div>
                    <div class="self-stretch text-gray-500 text-xs md:text-sm">If the uploaded KYC image doesn't meet the requirements, you can request a new upload from user.</div>
                </div>
                <Button variant="primary-flat" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="updateDialogData('kyc')">Ask to Submit Again</Button>
            </div>
        </Dialog>

        <Dialog v-model:visible="dialogs.cryptoWallet.visible" modal header="Crypto Wallet Information" class="dialog-xs md:dialog-sm">
            <form @submit.prevent="updateDialogData('cryptoWallet')">
                <div class="flex flex-col gap-5">
                    <div class="flex flex-col gap-1">
                        <InputLabel for="crypto" value="Cryptocurrency Network" />
                        <InputText 
                            id="crypto"
                            type="text"
                            class="block w-full"
                            v-model="form.crypto"
                            placeholder="Enter your crypto"
                            :invalid="!!form.errors.crypto"
                            autocomplete="crypto"
                        />
                        <InputError :message="form.errors.crypto" />
                    </div>
                    <div class="flex flex-col gap-1">
                        <InputLabel for="wallet_address" value="Wallet Address" />
                        <div class="flex items-center gap-2 self-stretch">
                            <InputText 
                            id="wallet_address"
                            type="text"
                            class="block w-full"
                            v-model="form.wallet_address"
                            placeholder="Enter your wallet address"
                            :invalid="!!form.errors.wallet_address"
                            autocomplete="wallet_address"
                        />
                        <InputError :message="form.errors.wallet_address" />
                        </div>
                    </div>
                </div>
                <div class="flex justify-end items-center pt-10 md:pt-7 gap-4 self-stretch">
                    <Button variant="gray-tonal" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="closeDialog('cryptoWallet')">Cancel</Button>
                    <Button variant="primary-flat" class="flex flex-1 md:flex-none md:w-[120px]" :class="{ 'opacity-25': form.processing }" :disabled="form.processing" @click.prevent="updateDialogData('cryptoWallet')">Save</Button>
                </div>
            </form>
        </Dialog>

    </AuthenticatedLayout>
</template>
