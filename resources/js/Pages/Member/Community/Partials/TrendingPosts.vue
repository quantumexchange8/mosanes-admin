<script setup>
import Button from '@/Components/Button.vue';
import { IconRefresh } from '@tabler/icons-vue';
import { ref, onMounted, render } from "vue";
import { router } from "@inertiajs/vue3";
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import Dialog from 'primevue/dialog';
import ViewPost from './ViewPost.vue';
import Skeleton from 'primevue/skeleton';

const props = defineProps({
    user: Object,
    renderHashtags: Function,
});

const emit = defineEmits(['hashtagFilter']);

const posts = ref([]);
const loading = ref(false);
const selectedPost = ref(null);
const showViewDialog = ref(false);

const fetchPosts = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('member.getTrendingPosts'));
        posts.value = response.data;
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        loading.value = false;
    }
};
onMounted(() => {
    fetchPosts();
});

const viewPost = (post) => {
  selectedPost.value = post
  showViewDialog.value = true;
};

const clickHashtag = (event) => {
    const target = event.target;
    if (
        target.classList.contains('hashtag-span') &&
        target.dataset.hashtag
    ) {
        emit('hashtagFilter', target.dataset.hashtag);
    }
}

</script>

<template>
    <div class="flex p-6 flex-col items-center gap-3 self-stretch bg-white shadow-toast rounded-2xl">
        <div class="flex h-9 items-center self-stretch">
            <div class="text-gray-950 font-semibold text-base flex-[1_0_0]">
                {{ $t('public.trending_posts') }}
            </div>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                @click="fetchPosts"
                :disabled="loading"
            >
                <IconRefresh size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </div>
        <div v-if="loading" class="flex flex-col items-center self-stretch">
            <div
                v-for="post in 5"
                :key="post.id"
                class="flex py-2 items-start gap-3 self-stretch"
                @click="viewPost(post)"
            >
                <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">                    
                    <DefaultProfilePhoto />
                </div>
                <div class="flex flex-col items-start gap-1 flex-[1_0_0]">
                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton> 
                </div>
            </div>
        </div>

        <div v-else-if="!loading && !posts.length" class="text-gray-400 text-sm py-4">
            No trending posts yet.
        </div>

        <div v-else class="flex flex-col items-center self-stretch">
            <div
                v-for="post in posts"
                :key="post.id"
                class="flex py-2 items-start gap-3 self-stretch cursor-pointer"
                @click="viewPost(post)"
            >
                <div class="w-8 h-8 rounded-full overflow-hidden grow-0 shrink-0">
                    <template v-if="post.user.profile_photo">
                        <img :src="post.user.profile_photo" alt="profile_photo">
                    </template>
                    <template v-else>
                        <DefaultProfilePhoto />
                    </template>
                </div>
                <div class="flex flex-col items-start gap-1 flex-[1_0_0]">
                    <div class="text-sm font-semibold text-gray-950">
                        {{ post.user.name }}
                    </div>
                        <div class="text-sm text-gray-700 overflow-hidden line-clamp-1 text-ellipsis" 
                            v-html="renderHashtags(post.content)" @click.stop="clickHashtag"></div>
                    <div class="text-gray-400 text-xs">
                        {{ post.likes_count }} likes
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ViewPost
        :user="user"
        :post="selectedPost"
        :renderHashtags="renderHashtags"
        :clickHashtag="clickHashtag"
        :visible="showViewDialog"
        @close="showViewDialog = false"
    />
</template>