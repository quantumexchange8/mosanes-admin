<script setup>
import {IconUserUp} from "@tabler/icons-vue"
import {ref} from "vue";
import Dialog from "primevue/dialog";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const props = defineProps({
    member: Object
})

const visible = ref(false);
const availableUpline = ref(false);

const getAvailableUplineData = async () => {
    try {
        const response = await axios.get('/member/getAvailableUplineData');
        availableUpline.value = response.data.availableUpline;
    } catch (error) {
        console.error('Error changing locale:', error);
    }
}

getAvailableUplineData();
</script>

<template>
    <div
        class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer"
        @click="visible = true"
    >
        <IconUserUp size="20" stroke-width="1.25" color="#667085" />
        <span class="text-gray-950 text-sm font-medium">{{ $t('public.upgrade_to_agent') }}</span>
    </div>
    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.upgrade_to_agent')"
        class="dialog-xs md:dialog-md"
    >
        <div class="flex items-center gap-3 self-stretch">
            <!-- member -->
            <div class="flex flex-col gap-2 items-start self-stretch w-full">
                <div class="text-xs text-gray-500">
                    {{ $t('public.member') }}
                </div>
                <div class="flex gap-3 items-center self-stretch">
                    <div class="w-9 h-9 rounded-full overflow-hidden">
                        <template v-if="member.profile_photo">
                            <img :src="member.profile_photo" alt="profile_photo">
                        </template>
                        <template v-else>
                            <DefaultProfilePhoto />
                        </template>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="text-gray-950 text-sm font-medium max-w-[200px] truncate">{{ member.name }}</span>
                        <span class="text-gray-500 text-xs truncate">{{ member.id_number }}</span>
                    </div>
                </div>
            </div>

            <!-- agent -->
            <div class="flex flex-col gap-2 items-start self-stretch w-full">
                <div class="text-xs text-gray-500">
                    {{ $t('public.upline') }}
                </div>
                <div class="flex gap-3 items-center self-stretch">
                    <div class="w-9 h-9 rounded-full overflow-hidden">
                        <template v-if="member.profile_photo">
                            <img :src="member.profile_photo" alt="profile_photo">
                        </template>
                        <template v-else>
                            <DefaultProfilePhoto />
                        </template>
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="text-gray-950 text-sm font-medium max-w-[200px] truncate">{{ member.name }}</span>
                        <span class="text-gray-500 text-xs truncate">{{ member.id_number }}</span>
                    </div>
                </div>
            </div>
        </div>
    </Dialog>
</template>
