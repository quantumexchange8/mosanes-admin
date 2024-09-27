<script setup>
import Button from "@/Components/Button.vue";
import {
    IconEdit,
    IconSearch,
    IconCircleXFilled
} from "@tabler/icons-vue";
import {ref} from "vue";
import DefaultProfilePhoto from "@/Components/DefaultProfilePhoto.vue";
import dayjs from "dayjs";
import Image from 'primevue/image';

const posts = ref([]);
const loading = ref(false);

const getResults = async () => {
    loading.value = true;

    try {
        let url = '/member/getPosts';

        const response = await axios.get(url);
        posts.value = response.data;
    } catch (error) {
        console.error('Error changing locale:', error);
    } finally {
        loading.value = false;
    }
};

getResults();


</script>

<template>
    <div class="flex flex-col self-stretch bg-white rounded-2xl shadow-toast w-full max-h-[80vh] overflow-y-auto">
        <div
            v-for="post in posts"
            class="border-b border-gray-200 last:border-transparent p-6 flex flex-col gap-5 items-center self-stretch"
        >
            <div class="flex justify-between items-start self-stretch">
                <div class="flex items-start gap-1 w-full">
                    <div class="relative w-[38px] h-[38px]">
                        <div class="w-7 h-7 shrink-0 grow-0 rounded-full overflow-hidden">
                            <div v-if="post.profile_photo">
                                <img :src="post.profile_photo" alt="Profile Photo" />
                            </div>
                            <div v-else>
                                <DefaultProfilePhoto />
                            </div>
                        </div>
                        <div class="absolute bottom-0.5 right-0.5 w-5 h-5">
                            <img :src="post.display_avatar" alt="avatar" />
                        </div>
                    </div>
                    <div class="flex flex-col items-start text-sm">
                        <span class="text-gray-950 font-bold">{{ post.user.name }}</span>
                        <span class="text-gray-500">@{{ post.display_name }}</span>
                    </div>
                </div>
                <span class="text-gray-700 text-xs">{{ dayjs(post.created_at).format('HH:mm') }}</span>
            </div>

<!--            content -->
            <div class="flex flex-col gap-5 items-start self-stretch pl-10">
                <Image
                    v-if="post.post_attachment"
                    :src="post.post_attachment"
                    alt="Image"
                    image-class="w-[250px] h-[160px] object-contain"
                    preview
                />
                <div class="flex flex-col gap-3 items-start self-stretch text-sm text-gray-950">
                    <span class="font-semibold">{{ post.subject }}</span>
                    <span>{{ post.message }}</span>
                </div>
                <span class="text-primary font-medium text-xs">{{ 'see more' }}</span>
            </div>
        </div>
    </div>
</template>
