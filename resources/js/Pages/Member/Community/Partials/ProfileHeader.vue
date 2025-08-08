<script setup>
import Button from '@/Components/Button.vue';
import DescriptionInput from '@/Components/DescriptionInput.vue';
import {h, ref, computed, watch } from 'vue';
import { Edit01Icon } from '@/Components/Icons/outline';
import axios from 'axios';
import AdjustCover from './AdjustCover.vue';
import Dialog from 'primevue/dialog';
import DefaultProfilePhoto from '@/Components/DefaultProfilePhoto.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    posts: Array,
    postCounts: Number,
    likeCounts: Number,
    commentCounts: Number,
    marginSize: {
        type: String,
        default: 'md:-mt-20'
    },
    avatarSize: {
        type: String,
        default: 'md:w-40 md:h-40 md:border-[12px]'
    },
    nameTextSize: {
        type: String,
        default: 'text-md md:text-xl'
    },
    introTextSize: {
        type: String,
        default: 'text-sm md:text-md'
    },
    numberSize: {
        type: String,
        default: 'text-md md:text-2xl'
    },
    labelSize: {
        type: String,
        default: 'text-xs md:text-md'
    },
    showEditCoverButton: {
        type: Boolean,
        default: true
    },
});

const coverInput = ref(null);
const showAdjustImage = ref(false);
const community_cover = ref();
const currentUser = usePage().props.auth.user;
const isCurrentUserOwner = currentUser.id === props.user.id;

const saveCommunityIntro = async () => {
    try {
        const response = await axios.post(route('member.updateCommunityIntro'), {
            community_intro: props.user.community_intro
            });
        if (response.data.user) {
            Object.assign(user, response.data.user);
        }
    } catch(error) {
        console.error('Failed to update community intro:', error);
    }
}

const handleCoverSelected = (event) => {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = () => {
            community_cover.value = reader.result;
            showAdjustImage.value = true;
        };
        reader.readAsDataURL(file);
    }
};

const handleCoverUpdate = (cover) => {
    // Update the user's cover image URL
    props.user.community_cover = cover;   
};

</script>

<template>
    <div class="w-full">
        <div class="flex flex-col items-center self-stretch bg-white shadow-toast rounded-2xl">
            <div class="self-stretch aspect-[4/1] relative">
                <img  
                    :src="user.community_cover || '/img/member/community_cover.svg'" 
                    :key="user.community_cover"
                    class="w-full self-stretch aspect-[4/1] rounded-t-2xl"
                >
                <Button
                    v-if="isCurrentUserOwner && showEditCoverButton"
                    class="absolute top-3 right-[11.5px]"
                    variant="primary-tonal"
                    size="sm"
                    type="button"
                    iconOnly
                    pill
                    @click="coverInput.click()"
                >
                    <Edit01Icon size="16" stroke-width="1.25"  />
                </Button>
                <input
                    ref="coverInput"
                    type="file"
                    accept="image/*"
                    class="hidden"
                    @change="handleCoverSelected"
                />
            </div>
            <div :class="`${props.marginSize} -mt-[30px] flex px-4 md:px-6 flex-col items-start gap-2 self-stretch`">
                <div :class="`${props.avatarSize} w-[60px] h-[60px] border-[4.5px] rounded-full border-white z-10 aspect-square overflow-hidden grow-0 shrink-0`">
                    <template v-if="user.profile_photo">
                        <img :src="user.profile_photo" alt="profile_photo">
                    </template>
                    <template v-else>
                        <DefaultProfilePhoto />
                    </template>
                </div>
                <div :class="`${props.nameTextSize} font-semibold  text-gray-950`">
                    {{ user.name }}
                </div>
                
                <!-- For other users' profiles -->
                <div v-if="!isCurrentUserOwner"
                    :class="`w-full cursor-default ${props.introTextSize} text-gray-400`"
                >
                    {{ user.community_intro || "No profile added yet" }}
                </div>
                <DescriptionInput
                    v-else
                    v-model="user.community_intro"
                    type="text"
                    :class="`w-full cursor-pointer ${props.introTextSize}`"
                    placeholder="Introduce yourself..."
                    @blur="saveCommunityIntro"
                    @click.stop
                />
            </div>
            
            <div class="flex px-4 py-3 md:p-6 items-start self-stretch">
                <div class="flex flex-col items-start flex-[1_0_0]">
                    <div :class="`${props.numberSize} font-semibold text-gray-950 text-center`">{{ postCounts }}</div>
                    <div :class="`${props.labelSize} text-gray-500`">Posts</div>
                </div>
                <div class="flex flex-col items-start flex-[1_0_0]">
                    <div :class="`${props.numberSize} font-semibold text-gray-950 text-center`">{{ likeCounts }}</div>
                    <div :class="`${props.labelSize} text-gray-500`">Likes</div>
                </div>
                <div class="flex flex-col items-start flex-[1_0_0]">
                    <div :class="`${props.numberSize} font-semibold text-gray-950 text-center`">{{ commentCounts }}</div>
                    <div :class="`${props.labelSize} text-gray-500`">Comments</div>
                </div>
            </div>
        
        </div>
        <Dialog
            v-model:visible="showAdjustImage"
            modal
            :header="$t('public.adjust_image')"
            class="dialog-xs xs:dialog-md"
            :dismissableMask="true"
        >
            <AdjustCover
                :user="user"
                :community_cover="community_cover"
                @update:visible="showAdjustImage = false"
                @upload-cover="handleCoverUpdate"
            />
        </Dialog>
    </div>
</template>
