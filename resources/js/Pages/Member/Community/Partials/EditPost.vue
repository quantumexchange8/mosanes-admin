<script setup>
import { useForm } from "@inertiajs/vue3";
import { IconMoodHappy, IconPhoto, IconTrash } from "@tabler/icons-vue"; 
import Button from '@/Components/Button.vue';
import EmojiPicker from 'vue3-emoji-picker';
import 'vue3-emoji-picker/css'
import { ref, watch } from 'vue';
import TipTapEditor from "@/Components/TipTapEditor.vue";
import OverlayPanel from 'primevue/overlaypanel';
import toast from "@/Composables/toast";
import { trans } from "laravel-vue-i18n";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";

const props = defineProps({
    user: Object,
    post: Object,
})

const emit = defineEmits(['update:visible', 'post-updated'])
const tiptapRef = ref()
const emojiPanel = ref();
const removedImages = ref([]);

const form = useForm({
    id: props.post.id,
    captions: props.post.content,
    community_post: [],
    clear_images: [] 
})

const closeDialog = () => {
    emit('update:visible', false)
}

const onSelectEmoji = (emoji) => {
    if (tiptapRef.value?.editor?.commands) {
        tiptapRef.value.editor.commands.insertContent(emoji.i)
    }
}

const imageInput = ref([]);

// Initialize imageInput with existing images
watch(() => props.post, (newPost) => {
        if (newPost && newPost.media) {
            imageInput.value = newPost.media.map(img => ({
                id: img.id,
                src: img.original_url,
                isNew: false,
            }));
        } else {
            imageInput.value = [];
        }
    },
    { immediate: true }
);

const handleImageInput = (event) => {
    const files = event.target.files;
    for (let file of files) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageInput.value.push({
                src: e.target.result,
                file: file,
                isNew: true,
            });
        };
        reader.readAsDataURL(file);
    }
    form.community_post = [...form.community_post, ...Array.from(files)];
};

const removeImage = (index) => {
    const img = imageInput.value[index];
    if (!img.isNew && img.id) {
        removedImages.value.push(img.id); // mark for deletion
    }
    imageInput.value.splice(index, 1); // remove from preview
};

const handleSubmitPost = async () => {
    try {
        // Create FormData object for multipart submission
        const formData = new FormData();
        formData.append('id', props.post.id);
        formData.append('captions', form.captions);
        
        // Add image IDs to be removed
        removedImages.value.forEach((mediaId, index) => {
            formData.append(`clear_images[${index}]`, mediaId);
        });
        
        // Add new image files
        if (form.community_post.length) {
            form.community_post.forEach((file, index) => {
                formData.append(`community_post[${index}]`, file);
            });
        }
        
        // Submit with axios
        const response = await axios.post(route('member.updateCommunityPost'), formData);
        
        if (response.data.success) {
            emit('post-updated', response.data.post);
                        
            // Reset form and close dialog
            form.reset();
            imageInput.value = [];
            removedImages.value = [];
            closeDialog();      
              
            toast.add({
                title: trans('public.toast_edit_post_success'),
                type: 'success'
            });
        }
    } catch (error) {
        console.error('Failed to update post:', error);
    }
};
</script>

<template>
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
                    v-model="form.captions"
                    placeholder="What's on your mind?"
                />
            </div>

            <!-- Image preview for both existing and new images -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-1 mt-2 auto-rows-[minmax(0,_1fr)] self-stretch">
                <div
                    v-for="(img, idx) in imageInput"
                    :key="img.src"
                    class="relative group w-full aspect-square overflow-hidden"
                >
                    <img
                        :src="img.src"
                        class="w-full h-full object-cover"
                    />
                    <!-- Remove Photo -->
                    <button
                        type="button"
                        class="hidden md:flex absolute inset-0 bg-black/40 text-white items-center justify-center opacity-0 group-hover:opacity-100 transition"
                        @click="removeImage(idx)"
                    >
                        <IconTrash size="16" stroke-width="1.25" color="white" />
                    </button>
                    <Button
                        variant="gray-tonal"
                        size="sm"
                        type="button"
                        iconOnly
                        pill
                        @click="removeImage(idx)"
                        class="flex md:hidden absolute top-1 right-1 z-10"

                    >
                        <IconTrash size="16" stroke-width="1.25" color="#667085" />
                    </Button>
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
                    :disable-skin-tones="false"
                    :hide-group-names="false"
                    :display-recent="true"
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
            size="base"
            @click="handleSubmitPost()"
        >
            Post
        </Button>
    </div>
</template>