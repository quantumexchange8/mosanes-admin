<script setup>
import {onMounted, ref, watch} from 'vue';
import { XIcon } from "@/Components/Icons/outline";
import Button from "@/Components/Button.vue";
import 'vue3-emoji-picker/css'
import PostAction from './PostAction.vue';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import Galleria from 'primevue/galleria';
import { router } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';

const props = defineProps({
    user: Object,
    post: Object,
    renderHashtags: Function,
    clickHashtag: Function,
    visible: Boolean,
});

const emit = defineEmits(['close']);

const comments = ref();
const loading = ref(false);
const images = ref([]);
const activeIndex = ref(0);
const displayCustom = ref(false);
const responsiveOptions = ref([
    {
        breakpoint: '1300px',
        numVisible: 4
    },
    {
        breakpoint: '575px',
        numVisible: 1
    }
]);

const imageClick = (index) => {
    activeIndex.value = index;
    displayCustom.value = true;
};

const getComments = async (postId) => {
    loading.value = true;
    try {
        const response = await axios.get(route('member.getComments', postId));
        comments.value = response.data.comments;
    }catch (error){
        console.error(error);
    }finally{
        loading.value = false; 
    }
};

watch(
    () => props.post,
    (newPost) => {
        if (newPost && newPost.media && newPost.media.length) {
            images.value = newPost.media.map((media, idx) => ({
                itemImageSrc: media.original_url,
                thumbnailImageSrc: media.original_url,
                alt: `Image ${idx + 1}`
            }));
        } else {
            images.value = [];
        }
        if (newPost && newPost.id) {
            getComments(newPost.id);
        }
    },
    { immediate: true }
);

const handleCommentDeleted = (response) => {
    
    comments.value = comments.value.filter(comment => comment.id !== response.commentId);
    if (props.post && props.post.comments_count > 0) {
        props.post.comments_count = response.newCommentsCount;
    }
};

</script>

<template>
    <Dialog
        :visible="props.visible"
        @update:visible="val => { if (!val) emit('close') }"
        modal
        class="dialog-xs sm:dialog-lg"
        :showHeader="false"
        :dismissableMask="true"
        :pt="{
            content: {
            class: 'p-0 '  // Remove default padding
            }
        }"
    >
        <!-- post with img -->
        <div
            v-if="post && post.media && post.media.length"
            class="flex flex-col md:flex-row h-full relative justify-center items-center self-stretch"
        >
            <!-- Images -->
            <div class="flex w-full md:w-1/2 relative items-center justify-center cursor-pointer">
                <Galleria
                    v-if="images"
                    v-model:activeIndex="activeIndex"
                    :value="images"
                    :numVisible="3"
                    :circular="true"
                    containerStyle="width:100% height:100%;"
                    :showItemNavigators="images.length > 1"
                    :showThumbnails="false"
                    :showIndicators="true"
                    :showIndicatorsOnItem="true"
                    thumbnailsPosition="bottom"
                >
                    <template #item="slotProps">
                        <img
                            :key="slotProps.index"
                            :src="slotProps.item.itemImageSrc"
                            :alt="slotProps.item.alt"
                            @click="imageClick(slotProps.index)"
                            class="w-[320px] h-40 md:w-[400px] md:h-[576px] bg-gray-950 object-contain rounded-t-3xl md:rounded-none md:rounded-l-3xl"
                        />
                    </template>
                    <template #thumbnail="slotProps">
                        <img
                            :key="slotProps.index"
                            :src="slotProps.item.thumbnailImageSrc"
                            :alt="slotProps.item.alt"
                            class="cursor-pointer object-cover rounded w-10 h-10 md:w-16 md:h-16"
                        />
                    </template>
                </Galleria>
                <Galleria
                    v-if="images && images.length"
                    v-model:activeIndex="activeIndex"
                    v-model:visible="displayCustom"
                    :value="images"
                    :circular="true"
                    :showItemNavigators="images.length > 1"
                    :showThumbnails="false"
                    :showIndicators="true"
                    :showIndicatorsOnItem="false"
                    :fullScreen="true"
                    :showItemNavigatorsOnHover="true"
                >
                    <template #item="slotProps">
                        <img
                            :key="slotProps.index"
                            :src="slotProps.item.itemImageSrc"
                            :alt="slotProps.item.alt"
                            class="h-[80vh] w-[90vw] md:h-[800px] md:w-[780px] object-contain"
                        />
                    </template>
                    <template #thumbnail="slotProps">
                        <img
                            :key="slotProps.index"
                            :src="slotProps.item.thumbnailImageSrc"
                            :alt="slotProps.item.alt"
                            class="object-cover rounded w-8 h-8 md:w-16 md:h-16"
                        />
                    </template>
                </Galleria>
            </div>
            
            <!-- sm screen close button  -->
            <div class="md:hidden absolute top-5 right-5 z-30 ">
                <Button
                    external
                    variant="gray-text"
                    type="button"
                    pill
                    iconOnly
                    @click="emit('close')"
                >
                    <XIcon class="text-white"/>
                </Button>
            </div>
        
            <!-- Post Content -->
            <div class="w-full md:w-1/2 relative ">
                <div class="absolute rounded-lg top-0 left-0 right-0 px-6 pt-6 pb-3">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3 cursor-pointer" @click.stop="router.visit(route('member.communityProfile', { user_id: post.user.id }))">
                            <div class="w-[42px] h-[42px] rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                                <template v-if="post.user.profile_photo">
                                    <img :src="post.user.profile_photo" alt="profile_photo">
                                </template>
                                <template v-else>
                                    <DefaultProfilePhoto />
                                </template>
                            </div>
                            <div class="flex flex-col justify-center items-start">
                                <div class="text-gray-950 font-semibold ">{{ post.user.name }}</div>
                                <div class="text-gray-500 text-xs">{{ post.formatted_created_at }}</div>
                            </div>
                        </div>
                        <div class="hidden md:flex p-[10px] justify-center items-center cursor-pointer" @click="emit('close')">
                            <XIcon class="text-gray-500 hover:text-gray-400"/>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-y-auto h-[40vh] md:h-[498px] mt-[78px]">
                    <div class="flex px-6 py-3 flex-col items-start gap-2 self-stretch">
                        <div v-html="renderHashtags(post.content)" @click.stop="clickHashtag"></div>
                    </div>
                    <div class="flex px-6 py-3 justify-between items-center self-stretch">
                        <div class="flex items-center gap-2">
                            <img src="/img/member/post_like.svg" class="w-6 h-6">
                            <div class="text-sm text-gray-500">{{post.likes_count}}</div>
                        </div>
                        <div class="text-sm text-gray-500">{{ post.comments_count }} Comments</div>
                    </div>

                    <PostAction :post="post" :user="user" :comments="comments" :loading="loading" :showComments="true" @comment-deleted="handleCommentDeleted"/>
                </div>
            </div>
        </div>

        <!-- without img -->
        <div v-else
            class="flex flex-row justify-center items-center self-stretch"
        >
            <!-- Post Content -->
            <div class="w-full relative bg-white rounded-3xl">
                <div class="absolute rounded-lg top-0 left-0 right-0 px-6 pt-6 pb-3">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <div class="w-[42px] h-[42px] rounded-full aspect-square overflow-hidden grow-0 shrink-0">
                                <template v-if="post.user.profile_photo">
                                    <img :src="post.user.profile_photo" alt="profile_photo">
                                </template>
                                <template v-else>
                                    <DefaultProfilePhoto />
                                </template>
                            </div>
                            <div class="flex flex-col justify-center items-start">
                                <div class="text-gray-950 font-semibold ">{{ post.user.name }}</div>
                                <div class="text-gray-500 text-xs">{{ post.formatted_created_at }}</div>
                            </div>
                        </div>
                        <div class="flex p-[10px] justify-center items-center cursor-pointer" @click="emit('close')">
                            <XIcon class="text-gray-500 hover:text-gray-400"/>
                        </div>
                    </div>
                </div>     
                
                <div class="overflow-y-auto h-[65vh] md:h-[45vh] mt-[78px]">
                    <div class="flex px-6 py-3 flex-col items-start gap-2 self-stretch">
                        <div v-html="renderHashtags(post.content)" @click.stop="clickHashtag"></div>
                    </div>
                    <div class="flex px-6 py-3 justify-between items-center self-stretch">
                        <div class="flex items-center gap-2">
                            <img src="/img/member/post_like.svg" class="w-6 h-6">
                            <div class="text-sm text-gray-500">{{post.likes_count}}</div>
                        </div>
                        <div class="text-sm text-gray-500">{{ post.comments_count }} Comments</div>
                    </div>
                    <PostAction :post="post" :user="user" :comments="comments" :loading="loading" :showComments="true" @comment-deleted="handleCommentDeleted"/>
                </div>
            </div>
        </div>
</Dialog>   
</template>
