<script setup>
import {ref, h, nextTick, computed, watch, onMounted} from 'vue';
import { IconDots, IconMoodHappy, IconPencilMinus, IconTrash } from "@tabler/icons-vue";
import Button from "@/Components/Button.vue";
import axios from 'axios';
import TipTapEditor from '@/Components/TipTapEditor.vue';
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css'
import { useConfirm } from "primevue/useconfirm";
import { trans } from 'laravel-vue-i18n';
import TieredMenu from "primevue/tieredmenu";
import CommentsAction from './CommentsAction.vue';
import OverlayPanel from 'primevue/overlaypanel';
import { usePage } from '@inertiajs/vue3';
import Skeleton from 'primevue/skeleton';
import toast from '@/Composables/toast';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';

const props = defineProps({
    authUser: Object,
    comment: Object,
    activeReplyId: Number,
});

const emit = defineEmits(['reply-submitted']);

const tiptapRefs = ref({});
const commentInput = ref('');
const emojiPanel = ref();

const setTipTapRef = (el, commentId) => {
    if (el) {
        tiptapRefs.value[commentId] = el;
    }
};

// Fixed to use comment.id from props directly
const onSelectEmoji = (emoji) => {
    const ref = tiptapRefs.value[props.comment.id];
    if (ref?.editor?.commands) {
        ref.editor.commands.insertContent(emoji.i);
    }
};

const postComment = async (parentId, content) => {
    if (!content || content === '<p></p>') {
        return; // Don't submit empty replies
    }
    
    try {
        const response = await axios.post(route('member.createComment'), {
            post_id: props.comment.post_id,
            comment: content,
            parent_id: props.comment.id
        });
        
        if (response.data.success) {
            // Clear input
            commentInput.value = '';
            // Reset TipTap editor
            const ref = tiptapRefs.value[props.comment.id];
            if (ref?.editor) {
                ref.editor.commands.setContent('');
            }
            
            emit('reply-submitted', response.data.comment);
        }
    } catch (error) {
        console.error('Failed to post reply:', error);
    }
};

watch(() => props.activeReplyId, async (newVal) => {
    if (newVal === props.comment.id && props.comment.user?.name) {
        await nextTick(); // ensure editor is ready
        const editor = tiptapRefs.value[props.comment.id]?.editor;
        if (editor) {
            const currentContent = editor.getHTML();
            const mentionName = props.comment.user.name || props.comment.replies.user.name;
            const userId = props.comment.user.id || props.comment.replies.user.id;
            const profileUrl = route('member.communityProfile', { user_id: userId });

            // Only insert @mention if editor is empty (avoid overwriting or duplicating)
            if (mentionName && (!currentContent || currentContent === '<p></p>')) {
            editor.commands.setContent(`<p><a href="${profileUrl}">@${mentionName}</a>&nbsp;</p>`);}
        }
    }
});
</script>

<template>
    <div v-if="activeReplyId === comment.id" class="flex w-full">
        <div class="flex w-full items-start gap-3 self-stretch">
            <div class="w-8 h-8 rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                <template v-if="authUser.profile_photo">
                    <img :src="authUser.profile_photo" alt="profile_photo">
                </template>
                <template v-else>
                    <DefaultProfilePhoto />
                </template>
            </div>
            <div class="relative w-full items-center gap-3 self-stretch">
                <TipTapEditor
                    :ref="(el) => setTipTapRef(el, comment.id)"
                    v-model="commentInput"
                    class="h-[120px] overflow-auto"
                    placeholder="Write your reply..."
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
                        @click.stop="postComment(comment.id, commentInput)"
                    >
                        Reply
                    </Button>
                </div>
            </div>
        </div>
    </div> 
    
</template>