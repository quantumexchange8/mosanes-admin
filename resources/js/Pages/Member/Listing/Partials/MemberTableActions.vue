<script setup>
import {
    IconDotsVertical,
    IconId,
    IconUserUp,
} from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import {computed, h, ref} from "vue";
import TieredMenu from "primevue/tieredmenu";
import InputSwitch from "primevue/inputswitch";
import {Link, useForm} from "@inertiajs/vue3";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import Dialog from "primevue/dialog";
import UpgradeToAgent from "@/Pages/Member/Listing/Partials/UpgradeToAgent.vue";

const props = defineProps({
    member: Object,
})

const menu = ref();
const visible = ref(false)
const dialogType = ref('')

const items = ref([
    {
        label: trans('public.member_details'),
        icon: h(IconId),
        command: () => {
            window.location.href = `/member/detail/${props.member.id_number}`;
        },
    },
    {
        label: trans('public.upgrade_to_agent'),
        icon: h(IconUserUp),
        command: () => {
            visible.value = true;
            dialogType.value = 'upgrade_to_agent';
        },
        role: 'member', // Add a custom property to check the role
    },
    // {
    //     separator: true,
    // },
]);

const filteredItems = computed(() => {
    return items.value.filter(item => {
        return !(item.role && item.role === 'member' && props.member.role === 'agent');

    });
});

const checked = ref(props.member.status === 'active')
const confirm = useConfirm();

const form = useForm({
    id: props.member.id
})
const requireConfirmation = () => {
    confirm.require({
        group: props.member.status === 'active' ? 'headless-gray' : 'headless-primary',
        header: props.member.status === 'active' ? trans('public.deactivate_member') : trans('public.activate_member'),
        message: props.member.status === 'active' ? trans('public.deactivate_member_caption') : trans('public.activate_member_caption'),
        acceptButton: props.member.status === 'active' ? trans('public.deactivate') : trans('public.confirm'),
        accept: () => {
            form.post(route('member.updateMemberStatus'), {
                onSuccess: () => {
                    checked.value = !checked.value;
                }
            })
        },
    });
};

const toggle = (event) => {
    menu.value.toggle(event);
};
</script>

<template>
    <div class="flex gap-3 items-center justify-center">
        <InputSwitch
            v-model="checked"
            readonly
            :disabled="form.processing"
            @click="requireConfirmation"
        />
        <Button
            variant="gray-text"
            size="sm"
            type="button"
            iconOnly
            pill
            @click="toggle"
            aria-haspopup="true"
            aria-controls="overlay_tmenu"
        >
            <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
        </Button>
        <TieredMenu ref="menu" id="overlay_tmenu" :model="filteredItems" popup>
            <template #item="{ item, props, hasSubmenu }">
                <div
                    class="flex items-center gap-3 self-stretch"
                    v-bind="props.action"
                >
                    <component :is="item.icon" size="20" stroke-width="1.25" color="#667085" />
                    <span class="font-medium">{{ item.label }}</span>
                </div>
            </template>
        </TieredMenu>
    </div>

    <Dialog
        v-model:visible="visible"
        modal
        :header="$t(`public.${dialogType}`)"
        class="dialog-xs sm:dialog-md"
    >
        <template v-if="dialogType === 'upgrade_to_agent'">
            <UpgradeToAgent
                :member="member"
                @update:visible="visible = false"
            />
        </template>
    </Dialog>
<!--    <OverlayPanel ref="op">-->
<!--        <div class="py-2 flex flex-col items-center">-->
<!--            <Link :href="route('member.detail', member.id_number)" class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer">-->
<!--                <IconId size="20" stroke-width="1.25" color="#667085" />-->
<!--                <div class="text-gray-950 text-sm font-medium">-->
<!--                    Member Details-->
<!--                </div>-->
<!--            </Link>-->
<!--            <UpgradeToAgent-->
<!--                :member="member"-->
<!--            />-->
<!--        </div>-->
<!--    </OverlayPanel>-->
</template>
