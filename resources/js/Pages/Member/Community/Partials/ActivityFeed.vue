<script setup>
import {router, usePage} from "@inertiajs/vue3";
import {ref, onMounted, h} from "vue";
import Dialog from "primevue/dialog";
import Button from "@/Components/Button.vue";
import axios from 'axios';
import { IconRefresh } from "@tabler/icons-vue";
import Skeleton from "primevue/skeleton";
import ViewPost from "./ViewPost.vue";

const props = defineProps({
    user: Object,
    posts: Object,
    renderHashtags: Function,
});

const emit = defineEmits(['hashtagFilter']);

const activityFeeds = ref([]);
const loading = ref(false);
const selectedPost = ref(null);
const showViewDialog = ref(false);

const fetchActivityFeed = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('member.getActivityFeed'));
        activityFeeds.value = response.data.notifications;
    } catch (error) {
        console.error("Error fetching activity feed:", error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchActivityFeed();
});

const viewPost = async (activityFeed) => {
    try {
        //Mark notification as read if unread
        if (!activityFeed.read_at) {
            await markAsRead(activityFeed.id);
            // Update local state to reflect the read status
            activityFeed.read_at = new Date().toISOString();
        }
        
        //Get the post 
        const postId = activityFeed.data?.post_id;
        selectedPost.value = props.posts?.find(post => post.id === postId);
        showViewDialog.value = true;

    } catch (error) {
        console.error("Error handling notification:", error);
    }
};

const markAsRead = async (notificationId) => {
    try {
        await axios.post(route('member.markFeedAsRead'), {
            id: notificationId
        });
    } catch (error) {
        console.error("Error marking notification as read:", error);
    }
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
    <div class="flex py-6 flex-col items-center gap-3 self-stretch bg-white shadow-toast rounded-2xl">
        <div class="flex h-9 px-6 items-center self-stretch">
            <div class="text-gray-950 font-semibold text-base flex-1">
                {{ $t('public.activity_feed') }}
            </div>
            <Button
                variant="gray-text"
                size="sm"
                type="button"
                iconOnly
                @click="fetchActivityFeed"
                :disabled="loading"
            >
                <IconRefresh size="16" stroke-width="1.25" color="#667085" />
            </Button>
        </div>
        
        <div v-if="loading" class="flex px-3 flex-col items-center self-stretch">
            <div
                v-for="notification in 12"
                :key="notification.id"
                class="flex p-3 items-start gap-3 self-stretch rounded-lg hover:bg-primary-25"
            >
                <Skeleton width="100%" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
            </div>
        </div>

        <div v-else-if="!loading && !activityFeeds.length" class="text-gray-400 text-sm py-4">
            No activities yet.
        </div> 
        
        <div v-else class="flex px-3 flex-col items-center self-stretch">
            <div
                v-for="activityFeed in activityFeeds"
                :key="activityFeed.id"
                class="flex p-3 items-start gap-3 self-stretch rounded-lg cursor-pointer"
                :class="{ 'bg-primary-25': !activityFeed.read_at }"
                @click="viewPost(activityFeed)"
                >
                <div class="flex justify-center items-center rounded-full">
                    <img
                        :src="activityFeed.data.type === 'like'
                        ? '/img/member/post_like.svg'
                        : activityFeed.data.type === 'comment' || activityFeed.data.type === 'reply'
                        ? '/img/member/post_comment.svg'
                        : activityFeed.data.type === 'hashtag'
                        ? '/img/member/post_hash.svg'
                        : ''"
                        alt="icon"
                        class="w-6 h-6"
                    />
                </div>
                <div class="min-h-6 flex-[1_0_0] line-clamp-2 text-sm text-gray-950">
                    <template v-if="activityFeed.data && activityFeed.data.type === 'like'">
                        {{ activityFeed.data.user_name }} liked your post.
                    </template>
                    <template v-else-if="activityFeed.data?.type === 'comment'">
                        <span v-html="`${activityFeed.data.user_name} commented: ${activityFeed.data.comment}`"></span>
                    </template>
                    <template v-else-if="activityFeed.data?.type === 'reply'">
                        <span v-html="`${activityFeed.data.user_name} replied your comment: ${activityFeed.data.comment}`"></span>
                    </template>
                    <template v-else-if="activityFeed.data?.type === 'hashtag'">
                        1 new post in {{ activityFeed.hashtag_name }}
                    </template>
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