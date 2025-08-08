<script setup>
import Button from '@/Components/Button.vue';
import { router, usePage } from '@inertiajs/vue3';
import { IconDotsVertical, IconMessageCircle, IconPencilMinus, IconThumbUp, IconTrash } from '@tabler/icons-vue';
import {onMounted, nextTick, ref} from 'vue';
import {trans, transChoice} from "laravel-vue-i18n";
import { useConfirm } from 'primevue/useconfirm';
import EditPost from './EditPost.vue';
import Dialog from "primevue/dialog";
import ViewPost from './ViewPost.vue';
import 'vue3-emoji-picker/css'
import PostAction from './PostAction.vue';
import Skeleton from 'primevue/skeleton';
import PostTieredMenu from './PostTieredMenu.vue';
import toast from '@/Composables/toast';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';

const props = defineProps({
    user: Object,
    posts: Object,
    loading: Boolean,
    renderHashtags: Function,
});

const emit = defineEmits(['hashtagFilter', 'post-updated', 'post-deleted']);

const dialogType = ref('');

const activePost = ref(false);
const selectedPost = ref(null);
const showEditDialog = ref(false);
const showViewDialog = ref(false);
const activeCommentPostId = ref(null);
const expandedPosts = ref({});

const viewPost = (post) => {
  selectedPost.value = post
  showViewDialog.value = true;
};

const confirm = useConfirm();

const requireConfirmation = (action_type, post) => {
    const messages = {
        delete: {
            group: 'headless-error',
            header: trans('public.delete_community_post'),
            text: trans('public.delete_community_post_desc'),
            cancelButton: trans('public.cancel'),
            acceptButton: trans('public.delete_confirm'),
            action: async () => {
                const response = await axios.delete(route('member.deleteCommunityPost'), {
                    data: {
                        id: post.id,
                    },
                });
                if (response.data.success) {
                    emit('post-deleted', post.id);
                    toast.add({
                        title: trans('public.toast_delete_post_success'),
                        type: 'success'
                    });
                }
            }
        },
    };

    const { group, header, text, dynamicText, suffix, actionType, cancelButton, acceptButton, action } = messages[action_type];

    confirm.require({
        group,
        header,
        actionType,
        text,
        dynamicText,
        suffix,
        cancelButton,
        acceptButton,
        accept: action
    });
}

const editPost = (post) => {
    selectedPost.value = post;
    activePost.value = post; 
    dialogType.value = 'edit_post';
    showEditDialog.value = true;
};

const deletePost = (post) => {
    requireConfirmation('delete', post);
};

const handlePostComment = (postId) => {
    // Toggle comment - if clicking on already active post, close it
    activeCommentPostId.value = activeCommentPostId.value === postId ? null : postId;
};

const handlePostUpdated = (updatedPost) => {
    emit('post-updated', updatedPost);

};

const imageClasses = ref({});

const checkImage = (event, postId) => {
  const img = event.target;

  img.decode()
    .then(() => {
      const width = img.naturalWidth;
      const height = img.naturalHeight;

      if (height > width) {
        imageClasses.value[postId] = 'aspect-square object-contain'; // Portrait
      } else {
        imageClasses.value[postId] = ' ';   // Landscape
      }
    })
    .catch(() => {
      console.error('Image decode failed');
    });
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

const toggleExpand = (postId) => {
  expandedPosts.value[postId] = !expandedPosts.value[postId];
};

const showMoreButton = (postId, content) => {
  return content && content.length > 150;
};
</script>
<template>
    <div v-if="loading" class="flex flex-col gap-3 md:gap-4 w-full">
        <div v-for="post in 5" class="flex flex-col w-full items-center self-stretch bg-white shadow-toast rounded-2xl">
            <div class="flex px-6 pt-6 justify-between items-center self-stretch">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 md:w-[42px] md:h-[42px] rounded-full overflow-hidden grow-0 shrink-0">
                        <DefaultProfilePhoto />
                    </div>
                    <div class="flex flex-col justify-center items-start">
                        <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                        <Skeleton width="9rem" height="0.5rem" class="my-1" borderRadius="2rem"></Skeleton>
                    </div>
                </div>
                <IconDotsVertical size="16" stroke-width="1.25" color="#667085" />
            </div>
            <div class="flex px-6 py-3 flex-col items-start gap-2 self-stretch">
                <Skeleton width="100%" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
            </div>
            
            <div class="flex px-6 py-3 justify-between items-center self-stretch">
                <div class="flex items-center gap-2">
                    <img src="/img/member/post_like.svg" class="w-6 h-6">
                    <Skeleton width="2rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                </div>
                <Skeleton width="6rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
            </div>

            <div class="flex px-6 py-3 items-center gap-3 self-stretch border-t border-t-gray-200">
                <!-- Like Post -->
                <Button
                    variant="gray-text"
                    type="button"
                    iconOnly
                    class="flex-1 py-2 px-3 !gap-2"
                    disabled
                >
                    <IconThumbUp class="w-5 h-5" />
                    <span>Like</span>
                </Button>

                <!-- Comment Button -->
                <Button
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    class="flex-1 py-2 px-3 !gap-2"
                    disabled
                >
                    <IconMessageCircle class="w-5 h-5" />
                    <span> Comment </span>
                </Button>
            </div>
        </div>
    </div>
    
    <div v-else class="flex flex-col w-full md:max-w-[690px] gap-3 md:gap-4">
        <div
            v-for="post in posts"
            :key="post.id"
            class="flex flex-col items-center self-stretch bg-white shadow-toast rounded-2xl"
            @click="viewPost(post)"
        >
            <div class="flex px-4 pt-4 md:px-6 md:pt-6 justify-between items-center self-stretch">
                <div class="flex items-center gap-3 cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: post.user.id }))">
                    <div class="w-9 h-9 md:w-[42px] md:h-[42px] rounded-full overflow-hidden grow-0 shrink-0">
                        <template v-if="post.user.profile_photo">
                            <img :src="post.user.profile_photo" alt="profile_photo">
                        </template>
                        <template v-else>
                            <DefaultProfilePhoto />
                        </template>
                    </div>
                    <div class="flex flex-col justify-center items-start">
                        <div class="text-gray-950 font-semibold">{{ post.user.name }}</div>
                        <div class="text-gray-500 text-xs">{{ post.formatted_created_at}}</div>
                    </div>
                </div>
                <PostTieredMenu :post="post" @edit="editPost" @delete="deletePost"/>                
            </div>
            <div class="flex px-4 md:px-6 py-3 flex-col items-start gap-2 self-stretch">
                <div 
                    :class="{ 'line-clamp-3': !expandedPosts[post.id] }" 
                    v-html="renderHashtags(post.content)" 
                    @click.stop="clickHashtag">
                </div>
                <div 
                    v-if="showMoreButton(post.id, post.content)"
                    @click.stop="toggleExpand(post.id)" 
                    class="text-primary-500 text-sm font-medium cursor-pointer"
                > 
                    {{ expandedPosts[post.id] ? 'See less' : 'See more' }}
                </div>
            </div>
            <!-- Images -->
            <div v-if="post.media && post.media.length" class="w-full" >
                <!-- 1 Image -->
                <div v-if="post.media.length === 1">
                    <img
                        :src="post.media[0].original_url"
                        :class="['flex w-full bg-slate-200', imageClasses[post.id] || '']"
                        @load="(e) => checkImage(e, post.id)"
                    />
                </div>

                <!-- 2 Images -->
                <div v-else-if="post.media.length === 2" class="grid grid-cols-2 gap-[2px]">
                    <img
                    v-for="(img, idx) in post.media"
                    :key="idx"
                    :src="img.original_url"
                    class="flex w-full h-auto object-cover aspect-square overflow-hidden"
                    />
                </div>

                <!-- 3 Images: 1 big on top, 2 small below -->
                <div v-else-if="post.media.length === 3" class="flex flex-col gap-[2px] aspect-square">
                    <div class="h-[66%]">
                        <img
                            :src="post.media[0].original_url"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div class="grid grid-cols-2 gap-[2px] h-[33%]">
                        <img
                            v-for="(img, idx) in post.media.slice(1)"
                            :key="idx"
                            :src="img.original_url"
                            class="w-full h-full object-cover overflow-hidden"
                        />
                    </div>
                </div>

                <!-- 4 Images: 1 big on top, 3 small below -->
                <div v-else-if="post.media.length === 4" class="flex flex-col gap-[2px] h-full aspect-square">
                    <div class="h-[66%]">
                        <img
                            :src="post.media[0].original_url"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div class="grid grid-cols-3 gap-[2px] h-[33%]"> 
                        <img
                            v-for="(img, idx) in post.media.slice(1)"
                            :key="idx"
                            :src="img.original_url" 
                            class="w-full h-full object-cover overflow-hidden"
                        />
                    </div>
                </div>
                <!-- More than 4 Images: 1 big, 3 small, and last one shows overlay -->
                <div v-else-if="post.media.length > 4" class="flex flex-col gap-[2px] h-full aspect-square">
                    <div class="h-[66%]">
                        <img
                            :src="post.media[0].original_url"
                            class="w-full h-full object-cover"
                        />
                    </div>
                    <div class="grid grid-cols-3 gap-[2px] h-[33%]">
                        <img
                            v-for="(img, idx) in post.media.slice(1, 3)"
                            :key="idx"
                            :src="img.original_url"
                            class="w-full h-full object-cover overflow-hidden"
                        />

                        <!-- 5th image with overlay -->
                        <div class="relative w-full h-full overflow-hidden">
                            <img
                                :src="post.media[3].original_url"
                                class="w-full h-full object-cover"
                            />
                            <div
                                class="absolute inset-0 bg-black/60 flex items-center justify-center text-white text-2xl font-medium"
                            >
                                +{{ post.media.length - 3 }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex px-4 md:px-6 py-3 justify-between items-center self-stretch">
                <div class="flex items-center gap-2">
                    <img src="/img/member/post_like.svg" class="w-6 h-6">
                    <div class="text-sm text-gray-500">{{ post.likes_count }}</div>
                </div>
                <div class="text-sm text-gray-500">{{ post.comments_count }} Comments</div>
            </div>

            <PostAction :post="post" :user="user" :showComments="false" @view-post="viewPost" :active-post-id="activeCommentPostId" @comment="handlePostComment"/>
        </div>
    </div>


    <Dialog
        v-model:visible="showEditDialog"
        modal
        :header="$t('public.edit_post')"
        class="dialog-xs md:dialog-lg"
        :dismissableMask="true"
        >
        <EditPost
            :user="user"
            :post="activePost"
            @update:visible="showEditDialog = $event"
            @post-updated="handlePostUpdated"
        />
    </Dialog>

    <ViewPost
        :user="user"
        :post="selectedPost"
        :renderHashtags="renderHashtags"
        :clickHashtag="clickHashtag"
        :visible="showViewDialog"
        @close="showViewDialog = false"
    />
</template>