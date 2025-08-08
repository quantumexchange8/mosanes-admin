<script setup>
import Button from '@/Components/Button.vue';
import { ref } from 'vue';
import Slider from "primevue/slider";
import toast from "@/Composables/toast";
import { trans } from "laravel-vue-i18n";

const props = defineProps({
    user: Object,
    community_cover: String // should be a string (URL/base64)
});
const emit = defineEmits(['update:visible', 'upload-cover']);

const closeDialog = () => {
    emit('update:visible', false);
};

const processing = ref(false);
// Drag logic
const dragging = ref(false);
const position = ref({ x: 0, y: 0 });
const start = ref({ x: 0, y: 0 });

const containerWidth = 544; // px
const containerHeight = 136; // px
const imageNaturalWidth = ref(0);
const imageNaturalHeight = ref(0);

const imageRef = ref(null); 

const scale = ref(1);

const clamp = (value, min, max) => Math.min(Math.max(value, min), max);

const onMouseDown = (e) => {
    dragging.value = true;
    start.value = {
        x: e.clientX - position.value.x,
        y: e.clientY - position.value.y,
    };
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
};

const onMouseMove = (e) => {
    if (!dragging.value) return;

    // Calculate scaled image size
    const scaledWidth = containerWidth * scale.value;
    const scaledHeight = (imageNaturalHeight.value / imageNaturalWidth.value) * containerWidth * scale.value;

    // Calculate bounds
    const minX = Math.min(0, containerWidth - scaledWidth);
    const maxX = 0;
    const minY = Math.min(0, containerHeight - scaledHeight);
    const maxY = 0;

    // Calculate new position
    let newX = e.clientX - start.value.x;
    let newY = e.clientY - start.value.y;

    // Clamp position
    position.value = {
        x: clamp(newX, minX, maxX),
        y: clamp(newY, minY, maxY),
    };
};

const onMouseUp = () => {
    dragging.value = false;
    document.removeEventListener('mousemove', onMouseMove);
    document.removeEventListener('mouseup', onMouseUp);
};

const onImageLoad = () => {
    if (!imageRef.value) return;
    imageNaturalWidth.value = imageRef.value.naturalWidth;
    imageNaturalHeight.value = imageRef.value.naturalHeight;
};

// --- CROP AND UPLOAD ---
const handleCoverUpdate = async () => {
    // 1. Create a canvas with the container size
    const canvas = document.createElement('canvas');
    canvas.width = containerWidth;
    canvas.height = containerHeight;
    const ctx = canvas.getContext('2d');

    // 2. Draw the image at the correct position and scale
    const img = imageRef.value;
    if (!img) return;

    // Calculate the scale ratio between the natural image and displayed image
    const displayToNaturalScale = imageNaturalWidth.value / containerWidth;

    // Calculate the crop position and size in the natural image
    const sx = -position.value.x * displayToNaturalScale / scale.value;
    const sy = -position.value.y * displayToNaturalScale / scale.value;
    const sWidth = containerWidth * displayToNaturalScale / scale.value;
    const sHeight = containerHeight * displayToNaturalScale / scale.value;

    ctx.drawImage(
        img,
        sx, sy, sWidth, sHeight, // source crop
        0, 0, canvas.width, canvas.height // destination
    );

    // 3. Convert canvas to blob and send to backend
    try {
        processing.value=true
        const blob = await new Promise((resolve, reject) => {
            canvas.toBlob(resolve, "image/jpeg", 0.95);
        });
        
        if (!blob) throw new Error("Failed to create image");
        
        const formData = new FormData();
        formData.append('community_cover', new File([blob], "cover.jpg", { type: "image/jpeg" }));
        
        const response = await axios.post(route('member.updateCoverImage'), formData);
        
        if (response.data.success) {
            // Update the UI with the new cover
            emit('upload-cover', response.data.cover_url);

            toast.add({
                title: trans('public.toast_update_cover_success'),
                type: 'success'
            });
            closeDialog();
        }
    } catch (error) {
        console.error('Failed to upload image:', error);
        // Handle error
    } finally {
        processing.value = false;
    }
};
</script>

<template>
    <div class="flex flex-col item-center gap-7 self-stretch">
        <div class="flex flex-col items-center gap-8 self-stretch">
            <div
                class="w-full aspect-[4/1] h-[72px] md:w-[544px] md:h-[136px] overflow-hidden relative bg-gray-100 rounded-lg user-select-none"
                style="user-select:none;"
            >
                <img
                    ref="imageRef"
                    :src="community_cover"
                    class="absolute cursor-grab active:cursor-grabbing select-none"
                    :style="{
                        left: 0,
                        top: 0,
                        width: containerWidth + 'px',
                        height: 'auto',
                        transform: `translate(${position.x}px, ${position.y}px) scale(${scale})`,
                        transformOrigin: 'top left',
                    }"
                    @load="onImageLoad"
                    @mousedown="onMouseDown"
                    draggable="false"
                    alt="Community Banner"
                />
            </div>
            <div class="flex items-center gap-2 w-[200px]">
                <Slider v-model="scale" :min="1" :max="3" :step="0.01" style="width: 180px;" />
            </div> 
        </div>
        <div class="flex justify-end items-center gap-4 self-stretch">
            <Button
                type="button"
                variant="gray-tonal"
                @click="closeDialog"
                size="base"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                variant="primary-flat"
                size="base"
                @click="handleCoverUpdate()"
                :disabled="processing"
            >
                OK
            </Button>
        </div>
    </div>
</template>