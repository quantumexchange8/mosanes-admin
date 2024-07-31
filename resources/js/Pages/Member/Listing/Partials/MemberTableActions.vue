<script setup>
import {IconDotsVertical, IconId} from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import OverlayPanel from "primevue/overlaypanel";
import InputSwitch from "primevue/inputswitch";
import {Link, useForm} from "@inertiajs/vue3";
import {useConfirm} from "primevue/useconfirm";
import {trans} from "laravel-vue-i18n";
import UpgradeToAgent from "@/Pages/Member/Listing/Partials/UpgradeToAgent.vue";

const props = defineProps({
    member: Object,
})

const op = ref();
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
    op.value.toggle(event);
}
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
        >
            <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
        </Button>
    </div>

    <OverlayPanel ref="op">
        <div class="py-2 flex flex-col items-center">
            <Link :href="route('member.detail', member.id_number)" class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer">
                <IconId size="20" stroke-width="1.25" color="#667085" />
                <div class="text-gray-950 text-sm font-medium">
                    Member Details
                </div>
            </Link>
            <UpgradeToAgent
                :member="member"
            />
        </div>
    </OverlayPanel>
</template>
