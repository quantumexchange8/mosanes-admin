<script setup>
import {ref, h, nextTick, computed, watch, onMounted} from 'vue';
import { IconDots,  IconMoodHappy, IconPencilMinus, IconTrash } from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import axios from 'axios';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css'
import CommentsAction from './CommentsAction.vue';
import OverlayPanel from 'primevue/overlaypanel';
import { usePage } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';
import CommentReply from './CommentReply.vue';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    comments: Object,
    loading: Boolean,
})

const authUser = usePage().props.auth.user;

const emit = defineEmits(['comment-deleted', 'reply-submitted']);

const editingCommentId = ref(null);
const editedContent = ref('');
const tiptapRefs = ref({});
const showEmojiPicker = ref(false);
const emojiPanels = ref({});
const activeReplyId = ref(null);

const setTipTapRef = (el, commentId) => {
  if (el) {
    tiptapRefs.value[commentId] = el;
  }
};

const toggleEmojiPanel = (event, commentId) => {
    const panel = emojiPanels.value[commentId];
    if (panel) {
        panel.toggle(event);
    }
};
// Store panel references properly
const registerEmojiPanel = (el, commentId) => {
    if (el) {
        emojiPanels.value[commentId] = el;
    }
};

const onSelectEmoji = (emoji) => {
    const commentId = editingCommentId.value;
    if (commentId) {
        const ref = tiptapRefs.value[commentId]
        if (ref?.editor?.commands) {
            ref.editor.commands.insertContent(emoji.i)
        }
    }
    showEmojiPicker.value = false;
};

const editing = (item) => {
    editingCommentId.value = item.id;
    editedContent.value = item.content;
    
    // Focus the editor after it's rendered
    nextTick(() => {
        const ref = tiptapRefs.value[item.id];
        if (ref?.editor) {
            ref.editor.commands.focus();
        }
    });
};

// Save the edited comment
const saveComment = async (commentId) => {
    if (!editedContent.value || editedContent.value.trim() === '' || editedContent.value === '<p></p>') {
        return; // Don't save empty comments
    }

    try {
        const response = await axios.post(route('member.updateComment'), {
            id: commentId,
            content: editedContent.value,
        });
        
        // Find if this is a top-level comment
        const commentIndex = props.comments.findIndex(c => c.id === commentId);
        if (commentIndex !== -1) {
            // Update the comment content
            props.comments[commentIndex].content = editedContent.value;
        } else {
            // This might be a reply, search through all comments' replies
            for (const comment of props.comments) {
                if (comment.all_replies && comment.all_replies.length) {
                    const replyIndex = comment.all_replies.findIndex(r => r.id === commentId);
                    if (replyIndex !== -1) {
                        // Update the reply content
                        comment.all_replies[replyIndex].content = editedContent.value;
                        break;
                    }
                }
            }
        }
        
        // Exit edit mode
        editingCommentId.value = null;
        editedContent.value = '';
        
    } catch (error) {
        console.error('Failed to update comment:', error);
    }
};

const handleCommentDeleted = (responseData) => {
    // Check if it's a top-level comment
    const commentId = responseData.commentId || responseData;
    const commentIndex = props.comments.findIndex(c => c.id === commentId);
    
    if (commentIndex !== -1) {
        // Remove the comment from the array
        props.comments.splice(commentIndex, 1);
    } else {
        // Check if it's a reply
        for (const comment of props.comments) {
            if (comment.all_replies && comment.all_replies.length) {
                const replyIndex = comment.all_replies.findIndex(r => r.id === commentId);
                if (replyIndex !== -1) {
                    // Remove the reply from the array
                    comment.all_replies.splice(replyIndex, 1);
                    break;
                }
            }
        }
    }
    
    // Emit event to update comment count in parent component
    // Pass the full response data to parent
    emit('comment-deleted', responseData);
};;

const toggleReply = (item) => {
    if (activeReplyId.value === item.id) {
        activeReplyId.value = null;
    } else {
        activeReplyId.value = item.id;
    }
};

const handleReplySubmit = (reply) => {
    activeReplyId.value = null //close tiptap editor
    // Find the parent comment this reply belongs to
    const parentComment = props.comments.find(c => c.id === reply.parent_id);
    
    if (parentComment) {
        // Initialize all_replies array if it doesn't exist
        if (!parentComment.all_replies) {
            parentComment.all_replies = [];
        }
        
        // Add the new reply to the beginning of the replies list
        parentComment.all_replies.push(reply);
        
        // Emit event to update comment count
        emit('reply-submitted', reply);
    } else {
        // If replying to a reply, find the original parent comment
        for (const comment of props.comments) {
            if (comment.all_replies && comment.all_replies.length) {
                const replyParent = comment.all_replies.find(r => r.id === reply.parent_id);
                if (replyParent) {
                    // Add to the parent comment's all_replies
                    if (!comment.all_replies) {
                        comment.all_replies = [];
                    }
                    comment.all_replies.push(reply);
                    
                    // Emit event to update comment count
                    emit('reply-submitted', reply);
                    return;
                }
            }
        }
        console.error('Parent comment not found for reply:', reply);
    }
};


</script>

<template>
    <div v-if="loading">
        <div v-for="comment in 4" class="flex p-6 flex-col items-center gap-6 self-stretch">
            <div class="flex items-start gap-3 self-stretch">
                <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                    <DefaultProfilePhoto />
                </div>
                <div class="flex flex-col justify-center items-start gap-3 flex-[1_0_0]">
                    <div class="flex flex-col items-start gap-2 self-stretch">
                        <div class="flex justify-between items-start self-stretch">
                            <div class="flex flex-col justify-center items-start">
                                <div class="flex flex-col justify-center items-start">
                                    <Skeleton width="9rem" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                                    <Skeleton width="9rem" height="0.5rem" class="my-1" borderRadius="2rem"></Skeleton>
                                </div>
                            </div>
                        </div>
                        <Skeleton width="100%" height="0.6rem" class="my-1" borderRadius="2rem"></Skeleton>
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <div v-else>
        <div v-if="comments && comments.length > 0" class="flex p-6 flex-col items-center gap-6 self-stretch">
            <div v-for="comment in comments" :key="comment.id" class="flex flex-col items-start gap-3 self-stretch">
                <div class="flex items-start gap-3 self-stretch ">
                    <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0 cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: comment.user.id }))">
                        <template v-if="comment.user.profile_photo">
                            <img :src="comment.user.profile_photo" alt="profile_photo">
                        </template>
                        <template v-else>
                            <DefaultProfilePhoto />
                        </template>
                    </div>
                    <template v-if="editingCommentId !== comment.id">
                        <div class="flex flex-col justify-center items-start gap-3 flex-[1_0_0]">
                            <div class="flex flex-col items-start gap-2 self-stretch">
                                <div class="flex justify-between items-start self-stretch">
                                    <div class="flex flex-col justify-center items-start">
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="text-gray-950 text-sm font-semibold cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: comment.user.id }))">
                                                {{ comment.user.name }}
                                            </div>
                                            <div class="text-gray-500 text-xs">{{ comment.formatted_created_at }}</div>
                                        </div>
                                    </div>
                                    <CommentsAction v-if="authUser.id === comment.user.id" :comment="comment" @edit="editing" @comment-deleted="handleCommentDeleted"/>
                                    <div v-if="authUser.id !== comment.user.id" 
                                        class="text-gray-400 text-center text-xs font-medium cursor-pointer hover:text-gray-700"
                                        @click="toggleReply(comment)"
                                        >
                                        {{ activeReplyId === comment.id ? 'Cancel' : 'Reply' }}
                                    </div>
                                </div>
                                <div v-html="comment.content" class="flex-[1_0_0] text-sm text-gray-950" ></div>
                                <CommentReply :authUser="authUser" :comment="comment" :activeReplyId="activeReplyId" @reply-submitted="handleReplySubmit"/>
                            </div>
                            
                        </div>
                    </template>
                    <!-- edit comment -->
                    <template v-else>
                        <div class="flex flex-col w-full relative">
                            <TipTapEditor
                                :ref="(el) => setTipTapRef(el, comment.id)"
                                v-model="editedContent"
                                class="w-full h-[120px] overflow-auto"
                            />
                            <div class="absolute flex bottom-2 right-2 items-center gap-3">
                                <Button
                                    variant="gray-text"
                                    size="sm"
                                    type="button"
                                    iconOnly
                                    pill
                                    @click="(e) => toggleEmojiPanel(e, comment.id)"
                                >
                                    <IconMoodHappy size="16" stroke-width="1.25" color="#667085" />
                                </Button>
                                <OverlayPanel :ref="(el) => registerEmojiPanel(el, comment.id)" class="absolute -left-40 top-full mt-2 z-50 text-xs">
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
                                    @click.stop="saveComment(comment.id)"
                                >
                                    Save
                                </Button>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- Reply -->
                <div v-if="comment.all_replies && comment.all_replies.length" class="flex flex-col items-center pl-11 gap-3 self-stretch">
                    <div v-for="reply in comment.all_replies" :key="reply.id" class="flex flex-col items-start gap-3 self-stretch">
                        <div v-if="editingCommentId !== reply.id" class="flex items-start gap-3 self-stretch">
                            <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0 cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: reply.user.id }))">
                                <template v-if="reply.user.profile_photo">
                                    <img :src="reply.user.profile_photo" alt="profile_photo">
                                </template>
                                <template v-else>
                                    <DefaultProfilePhoto />
                                </template>
                            </div>
                            <div class="flex flex-col justify-center items-start gap-3 flex-[1_0_0]">
                                <div class="flex flex-col items-start gap-2 self-stretch">
                                    <div class="flex justify-between items-start self-stretch">
                                        <div class="flex flex-col justify-center items-start">
                                            <div class="flex flex-col justify-center items-start">
                                                <div class="text-gray-950 text-sm font-semibold cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: reply.user.id }))">
                                                    {{ reply.user.name }}
                                                </div>
                                                <div class="text-gray-500 text-xs">{{ reply.formatted_created_at }}</div>
                                            </div>
                                        </div>
                                        <CommentsAction v-if="authUser.id === reply.user.id" :comment="reply" @edit="editing" @comment-deleted="handleCommentDeleted"/>
                                        <div v-if="authUser.id !== reply.user.id" 
                                            class="text-gray-400 text-center text-xs font-medium cursor-pointer hover:text-gray-700"
                                            @click="toggleReply(reply)"
                                            > 
                                            {{ activeReplyId === reply.id ? 'Cancel' : 'Reply' }}
                                        </div>
                                    </div>
                                    <div v-html="reply.content" class="flex-[1_0_0] text-sm text-gray-950" ></div>
                                    
                                </div>
                            </div>
                        </div>

                        <!-- Edit reply view -->
                        <div v-else class="flex items-start gap-3 self-stretch w-full">
                            <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                                <template v-if="reply.user.profile_photo">
                                    <img :src="reply.user.profile_photo" alt="profile_photo">
                                </template>
                                <template v-else>
                                    <DefaultProfilePhoto />
                                </template>
                            </div>
                            <div class="flex flex-col w-full relative">
                                <TipTapEditor
                                    :ref="(el) => setTipTapRef(el, reply.id)"
                                    v-model="editedContent"
                                    class="w-full h-[120px] overflow-auto"
                                />
                                <div class="absolute flex bottom-2 right-2 items-center gap-3">
                                    <Button
                                        variant="gray-text"
                                        size="sm"
                                        type="button"
                                        iconOnly
                                        pill
                                        @click="(e) => toggleEmojiPanel(e, reply.id)"
                                    >
                                        <IconMoodHappy size="16" stroke-width="1.25" color="#667085" />
                                    </Button>
                                    <OverlayPanel :ref="(el) => registerEmojiPanel(el, reply.id)" class="absolute -left-40 top-full mt-2 z-50 text-xs">
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
                                        @click.stop="saveComment(reply.id)"
                                    >
                                        Save
                                    </Button>
                                </div>
                            </div>
                        </div>
                        <CommentReply :authUser="authUser" :comment="reply" :activeReplyId="activeReplyId" @reply-submitted="handleReplySubmit"/>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</template>