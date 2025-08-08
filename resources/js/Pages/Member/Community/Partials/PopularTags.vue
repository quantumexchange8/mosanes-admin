<script setup>
import { ref, onMounted } from "vue";
import Button from '@/Components/Button.vue';
import { IconRefresh } from '@tabler/icons-vue';
import axios from 'axios';
import Skeleton from "primevue/skeleton";

const props = defineProps({
    handleHashtagFilter: Function,
});

const tags = ref([]);
const loading = ref(false);

const fetchPopularTags = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/member/getPopularTags');
        tags.value = response.data;
    } catch (error) {
        console.error("Error fetching tags:", error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchPopularTags();
});

</script>
<template>
    <div class="flex p-6 flex-col items-center gap-3 self-stretch bg-white shadow-toast rounded-2xl">
        <div class="flex h-9 items-center self-stretch">
            <div class="text-gray-950 font-semibold text-base flex-1">
                {{ $t('public.popular_tags') }}
            </div>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                @click="fetchPopularTags"
            >
                <IconRefresh size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </div>
        
        <div v-if="loading" class="flex flex-col items-start self-stretch">
            <div v-for="tag in 5" :key="tag.name" class="flex py-2 flex-col items-start gap-1 self-stretch">
                <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>

            </div>
        </div>
        <div v-else-if="!loading && !tags.length" class="flex items-center text-gray-400 text-sm py-4">
            No popular tags yet.
        </div>
        <div v-else class="flex flex-col items-start self-stretch">
            <div v-for="tag in tags" :key="tag.name" class="flex py-2 flex-col items-start gap-1 self-stretch">
                <div class="flex items-center text-primary-500 hover:underline cursor-pointer"
                    @click="props.handleHashtagFilter && props.handleHashtagFilter(tag.name)"
                >
                    #{{ tag.name }}
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-xs text-gray-400">{{ tag.count}} posts</div> 
                    <!-- <div class="text-xs text-gray-400">•</div>
                    <div class="text-xs text-gray-400">4,210 views</div>  -->
                </div>
            </div>
        </div>
    </div>
</template>