<script setup>
import {ref, nextTick, computed, watch, onMounted} from 'vue';
import { IconDots, IconMessageCircle, IconMoodHappy, IconThumbUp, IconThumbUpFilled } from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import axios from 'axios';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css'
import Comments from './Comments.vue';
import OverlayPanel from 'primevue/overlaypanel';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';

const props = defineProps({
    user: Object,
    post: Object,
    comments: Object,
    viewPostDialog: Boolean,
    activePostId: Number,
    showComments: {
        type: Boolean,
        default: true
    },
    loading: Boolean,
    postLoading: Boolean,
})

const tiptapRefs = ref({});
const commentInput = ref('');
const emojiPanel = ref();

const emit = defineEmits(['like', 'comment', 'view-post', 'comment-deleted'])

const handleLike = (post) => {
    const originalLiked = post.liked;
    post.liked = !originalLiked;
    post.likes_count += post.liked ? 1 : -1;
    
    axios.post(route('member.likeCommunityPost'), {
        post_id: post.id 
    })
    .catch((error) => {
        console.error('Like failed:', error);
        // Revert if request fails
        post.liked = originalLiked;
        post.likes_count += post.liked ? 1 : -1;
    });
};

const setTipTapRef = (el, postId) => {
    if (el) {
        tiptapRefs.value[postId] = el
    }
}

const onSelectEmoji = (emoji) => {
    const postId = activeCommentPostId.value;
    if (postId) {
        const ref = tiptapRefs.value[postId]
        if (ref?.editor?.commands) {
            ref.editor.commands.insertContent(emoji.i)
        }
    }
};

// Keep local state for compatibility with ViewPost
const localActiveCommentPostId = ref(null);
const activeCommentPostId = computed(() => 
    props.activePostId !== undefined ? props.activePostId : localActiveCommentPostId.value
);

const handleComment = (post) => {
    // If parent is managing state, tell it to toggle
    if (props.activePostId !== undefined) {
        emit('comment', post.id)
    } else {
        // Otherwise toggle locally (for ViewPost compatibility)
        localActiveCommentPostId.value = 
            localActiveCommentPostId.value === post.id ? null : post.id
    }
    
    // Only focus if this becomes the active post
    if (activeCommentPostId.value === post.id) {
        nextTick(() => {
            const ref = tiptapRefs.value[post.id]
            if (ref?.editor) {
                ref.editor.commands.focus()
                const editorElement = ref.editor.view.dom
                editorElement?.scrollIntoView({ behavior: 'smooth', block: 'center' })
            }
        })
    }
}

const postComment = async (postId, comment) => {
    if (!comment || comment.trim() === '' || comment === '<p></p>') {
        return; // Don't post empty comments
    }

    try {
        const response = await axios.post(route('member.createComment'), {
            post_id: postId,
            comment: comment,
            parent_id: null,
        });
        
        // Clear the comment input
        if (props.post) {
            commentInput.value = '';
            // Also clear the TipTap editor content
            const ref = tiptapRefs.value[postId];
            if (ref?.editor) {
                ref.editor.commands.setContent('');
            }
        }

        // Add the new comment to the post's comments array
        if (response.data.success && response.data.comment && props.post) {
            if (props.activePostId !== undefined) { // in Post
                props.post.comments_count++;
                emit('comment', null);
                emit('view-post', props.post);
            } else { // in viewPost
                props.comments.unshift(response.data.comment);
                props.post.comments_count++;
                localActiveCommentPostId.value = null;
            }
        }
    } catch (error) {
        console.error('Failed to post comment:', error);
    }
};


const handleCommentDeleted = (responseData) => {
    emit('comment-deleted', responseData);
};

const handleReplySubmit = (reply) => {
    props.post.comments_count++;
}


</script>

<template>
    <div class="flex px-4 md:px-6 py-3 items-center gap-3 self-stretch border-t border-t-gray-200">
        <!-- Like Post -->
        <Button
            variant="gray-text"
            type="button"
            iconOnly
            class="flex-1 py-2 px-3 !gap-2"
            @click.stop="handleLike(post)"
        >
            <component
                :is="post?.liked ? IconThumbUpFilled : IconThumbUp"
                class="w-5 h-5"
                :class="post?.liked ? 'text-primary-500' : 'text-gray-500'"
            />
            <span :class="post?.liked ? 'text-primary-500' : 'text-gray-500'">Like</span>
        </Button>

        <!-- Comment Button -->
        <Button
            variant="gray-text"
            size="sm"
            type="button"
            iconOnly
            class="flex-1 py-2 px-3 !gap-2"
            @click.stop="handleComment(post)"
        >
            <IconMessageCircle class="w-5 h-5" />
            <span> Comment </span>
        </Button>
    </div>

    <!-- comment input at ViewPost -->
    <div v-if="showComments" class="flex px-6 py-3 gap-3 self-stretch">
        <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0">
            <template v-if="user.profile_photo">
                <img :src="user.profile_photo" alt="profile_photo">
            </template>
            <template v-else>
                <DefaultProfilePhoto />
            </template>
        </div>
        
        <div v-if="post && activeCommentPostId === post.id" class="relative w-full items-center gap-3 self-stretch">
            <TipTapEditor
                :ref="(el) => setTipTapRef(el, post.id)"
                v-model="commentInput"
                class="w-full h-[120px] overflow-auto"
                placeholder="Add a comment..."
                @click.stop
            />
            <div class="absolute flex bottom-2 right-2 items-center gap-3">
                <Button
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    pill
                    @click.stop="emojiPanel.toggle($event)"
                >
                    <IconMoodHappy size="16" stroke-width="1.25" color="#667085" />
                </Button>
                <OverlayPanel ref="emojiPanel">
                    <EmojiPicker
                        :native="true"
                        :disable-skin-tones="false"
                        :display-recent="true"
                        :hide-group-names="false"
                        @select="onSelectEmoji"
                    />
                </OverlayPanel>
                <Button
                    type="button"
                    variant="primary-flat"
                    size="sm"
                    @click.stop="postComment(post.id, commentInput)"
                >
                    Post
                </Button>
            </div>
        </div>
        <div v-else class="w-full" @click="handleComment(post)">
            <TipTapEditor
                class="w-full max-h-[44px] "
                placeholder="Add a comment..."
            />
        </div>
    </div>
    <!-- comment input at Post -->
    <div v-else class="flex self-stretch">
        <div v-if="post && activeCommentPostId === post.id" class="px-6 py-3 relative w-full items-center gap-3 self-stretch">
            <TipTapEditor
                :ref="(el) => setTipTapRef(el, post.id)"
                v-model="commentInput"
                class="w-full h-[120px] overflow-auto"
                placeholder="Add a comment..."
                @click.stop
            />
            <div class="absolute flex bottom-5 right-8 items-center gap-3">
                <Button
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    pill
                    @click.stop="emojiPanel.toggle($event)"
                >
                    <IconMoodHappy size="16" stroke-width="1.25" color="#667085" />
                </Button>
                <OverlayPanel ref="emojiPanel" class="absolute -left-40 top-full mt-2 z-50 text-xs">
                    <EmojiPicker
                        :native="true"
                        :disable-skin-tones="false"
                        :display-recent="true"
                        :hide-group-names="false"
                        @select="onSelectEmoji"
                    />
                </OverlayPanel>
                        
                <Button
                    type="button"
                    variant="primary-flat"
                    size="sm"
                    @click.stop="postComment(post.id, commentInput)"
                >
                    Post
                </Button>
            </div>
        </div>
    </div>

    <!-- comments  -->
    <template v-if="showComments">
        <Comments :user="user" :post="post" :comments="comments" :loading="loading" @comment-deleted="handleCommentDeleted" @reply-submitted="handleReplySubmit"/>
    </template>

</template>