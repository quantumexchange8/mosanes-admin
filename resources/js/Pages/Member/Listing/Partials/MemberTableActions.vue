<script setup>
import {IconDotsVertical, IconId} from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import {ref} from "vue";
import OverlayPanel from "primevue/overlaypanel";
import {Link} from "@inertiajs/vue3";

const props = defineProps({
    member: Object,
})

const op = ref();

const toggle = (event) => {
    op.value.toggle(event);
}
</script>

<template>
    <Button
        variant="gray-text"
        size="sm"
        type="button"
        iconOnly
        @click="toggle"
    >
        <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
    </Button>

    <OverlayPanel ref="op">
        <div class="py-2 flex flex-col items-center">
            <Link :href="route('member.detail', member.id_number)" class="p-3 flex items-center gap-3 self-stretch hover:bg-gray-100 hover:cursor-pointer">
                <IconId size="20" stroke-width="1.25" color="#667085" />
                <div class="text-gray-950 text-sm font-medium">
                    Member Details
                </div>
            </Link>

        </div>
    </OverlayPanel>
</template>
