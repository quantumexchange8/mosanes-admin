<script setup>
import Button from '@/Components/Button.vue';
import { IconCircleXFilled, IconSearch } from '@tabler/icons-vue';
import InputText from 'primevue/inputtext';
import { ref, watch } from 'vue';

const props = defineProps({
    hashtag: String
});

const emit = defineEmits(['filterChange']);

const search = ref('');
const selectedFilter = ref('');

watch([search, selectedFilter], () => {
    emit('filterChange', { 
        search: search.value, 
        filters: selectedFilter.value ? [selectedFilter.value] : [] 
    });
});

// Watch for changes to the hashtag prop and update search input
watch(() => props.hashtag, (val) => {
    if (val) search.value = val;
});

const filters = [
    { key: 'with-media', label: 'With Media' },
    { key: 'text-only', label: 'Text Only' },
    { key: 'my-posts', label: 'My Posts' },
    { key: 'liked-posts', label: 'Liked Posts' },
]

const toggleFilter = (key) => {
    selectedFilter.value = selectedFilter.value === key ? '' : key;
};

const clearSearch = () => {
    search.value = '';
}

</script>

<template>
    <div class="flex p-6 flex-col items-start gap-5 self-stretch bg-white shadow-toast rounded-2xl">
        <div class="relative w-full">
            <div class="absolute top-2/4 -mt-[9px] left-4 text-gray-400">
                <IconSearch size="20" stroke-width="1.25" />
            </div>
            <InputText v-model="search" :placeholder="$t('public.keyword_search')" class="font-normal pl-12 w-full" />
            <div
                v-if="search"
                class="absolute top-2/4 -mt-2 right-4 text-gray-300 hover:text-gray-400 select-none cursor-pointer"
                @click="clearSearch"
            >
                <IconCircleXFilled size="16" />
            </div>
        </div>
        <div class="text-gray-400 text-sm">
            Filter By:
        </div>
        <div class="flex items-start content-start gap-2 self-stretch flex-wrap">
            <Button
                v-for="filter in filters"
                    class="!px-3 !py-[6px]"
                    :key="filter.key"
                    type="button"
                    size="sm"
                    variant="gray-outlined"
                    :class="selectedFilter === filter.key ? 'hover:bg-primary-25 border border-primary-500 text-primary-500' : 'hover:bg-gray-50 border border-gray-300 text-gray-950'"
                    @click="toggleFilter(filter.key)"
                >
                <div class="text-sm font-medium"> {{filter.label}} </div>
            </Button>
        </div>
    </div>
</template>