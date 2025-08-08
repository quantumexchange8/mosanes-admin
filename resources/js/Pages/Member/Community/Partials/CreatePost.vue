<script setup>
import { useForm } from "@inertiajs/vue3";
import { IconMoodHappy, IconPhoto, IconTrash } from "@tabler/icons-vue";
import Button from '@/Components/Button.vue';
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css'
import {ref } from 'vue';
import TipTapEditor from "@/Components/TipTapEditor.vue";
import Dialog from "primevue/dialog";
import OverlayPanel from 'primevue/overlaypanel';
import toast from "@/Composables/toast";
import { trans } from "laravel-vue-i18n";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const props = defineProps({
    user: Object,
})

const emit = defineEmits(['post-created']);
const processing = ref(false);
const visible = ref(false);
const tiptapRef = ref()
const emojiPanel = ref();

const captions = ref('');
const imageInput = ref([]);
const selectedFiles = ref([]);

const closeDialog = () => {
    visible.value = false;
}

const onSelectEmoji = (emoji) => {
    if (tiptapRef.value?.editor?.commands) {
        tiptapRef.value.editor.commands.insertContent(emoji.i)
    }
}

const handleImageInput = (event) => {
    const files = event.target.files;

    for (let file of files) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageInput.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    }
    selectedFiles.value = [...selectedFiles.value, ...Array.from(files)];

};

const removeImage = (index) => {
    imageInput.value.splice(index, 1);
    selectedFiles.value.splice(index, 1);
};

const extractHashtags = (html) => {
    const text = html.replace(/<[^>]+>/g, ' '); // strip HTML tags
    const hashtags = text.match(/#([\u4e00-\u9fa5_a-zA-Z0-9]+)/gu) || [];
    return hashtags.map(tag => tag.replace('#', '').replace('#', '').toLowerCase());
};

const handleSubmitPost = async () => {
    if (!captions.value || captions.value === '<p></p>') {
        return; // Don't post empty captions
    }
    try {
        processing.value = true;

        const formData = new FormData();
        formData.append('captions', captions.value);
        selectedFiles.value.forEach((file, index) => {
            formData.append(`community_post[${index}]`, file);
        });

        const hashtags = extractHashtags(captions.value);
        formData.append('hashtags', JSON.stringify(hashtags));
        const response = await axios.post(route('member.createCommunityPost'), formData);
        
        if (response.data.success) {
            emit('post-created', response.data.post);
        }
        // Reset form
        captions.value = '';
        imageInput.value = [];
        selectedFiles.value = [];
        closeDialog();

        toast.add({
            title: trans('public.toast_create_community_post_success'),
            type: 'success'
        });
        
    } catch (error) {
        console.error('Failed to post:', error);
    } finally{
        processing.value = false;
    }
    
}

</script>

<template>
    <div class="flex p-4 md:p-6 items-center gap-3 self-stretch bg-white shadow-toast rounded-2xl cursor-pointer"  @click="visible = true">       
        <div class="w-[36px] md:w-[42px] h-[36px] md:h-[42px] rounded-full aspect-square overflow-hidden grow-0 shrink-0">
            <template v-if="user.profile_photo">
                <img :src="user.profile_photo" alt="profile_photo">
            </template>
            <template v-else>
                <DefaultProfilePhoto />
            </template>
        </div>
        <div class="flex-1 text-sm text-gray-400">Share a trading insight or ask a question...</div>
    </div>
  
    <Dialog
        v-model:visible="visible"
        modal
        :header="$t('public.new_post')"
        class="dialog-xs md:dialog-lg"
        :dismissableMask="true"
    >
        <div class="flex flex-col item-center gap-5 self-stretch">
            <div class="flex item-center gap-3 self-stretch">
                <div class="w-[42px] h-[42px]  rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                    <template v-if="user.profile_photo">
                        <img :src="user.profile_photo" alt="profile_photo">
                    </template>
                    <template v-else>
                        <DefaultProfilePhoto />
                    </template>
                </div>
                <div class="flex flex-col justify-center items-start">
                    <div class="text-md text-gray-950 font-semibold">{{ user.name }}</div>
                    <div class="text-xs text-gray-500">Post to anyone</div>
                </div>
            </div>
            <div class="self-stretch min-h-60 max-h-[500px] overflow-auto md:max-h-none md:overflow-visible">
                <div>
                    <TipTapEditor
                        ref="tiptapRef"
                        class="border-none min-h-24 max-h-60 overflow-y-auto"
                        v-model="captions"
                        placeholder="What's on your mind?"
                    />
                </div>

                <div v-if="imageInput && imageInput.length" >
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-1 mt-2 auto-rows-[minmax(0,_1fr)] self-stretch">
                        <div
                        v-for="(src, idx) in imageInput"
                        :key="idx"
                        class="relative group w-full aspect-square overflow-hidden"
                        >
                        <img
                            :src="src"
                            class="w-full h-full object-cover"
                            alt="Preview"
                        />
                        <!-- Trash Icon -->
                        <button
                            type="button"
                            class="absolute inset-0 bg-black/40 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition"
                            @click="removeImage(idx)"
                        >
                            <IconTrash size="16" stroke-width="1.25" color="white" />
                        </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-1 self-stretch">
                <Button
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    pill
                    @click="emojiPanel.toggle($event)"
                >
                    <IconMoodHappy size="16" stroke-width="1.25" color="#667085" />
                </Button>
                <OverlayPanel ref="emojiPanel">
                    <EmojiPicker 
                        :native="true"
                        :disable-skin-tones	="false"
                        :display-recent="true"
                        :hide-group-names="false"
                        @select="onSelectEmoji"
                    />
                </OverlayPanel>
                <input
                    ref="ImageInput"
                    id="image"
                    type="file"
                    class="hidden"
                    accept="image/*"
                    @change="handleImageInput"
                    multiple
                />
                <Button
                    variant="gray-text"
                    size="sm"
                    type="button"
                    iconOnly
                    pill
                    @click="$refs.ImageInput.click()"
                >
                    <IconPhoto size="16" stroke-width="1.25" color="#667085" />
                </Button>
            </div>
        </div>
        <div class="flex justify-end items-center gap-4 self-stretch">
            <Button
                variant="primary-flat"
                @click="handleSubmitPost()"
                size="base"
                :disabled="processing"
            >
                Post
            </Button>
        </div>
    </Dialog>
    
</template>