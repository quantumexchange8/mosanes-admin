<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Breadcrumb from 'primevue/breadcrumb';
import {h, ref, computed, watch, onMounted, onBeforeUnmount } from 'vue';
import Posts from './Partials/Posts.vue';
import ProfileHeader from './Partials/ProfileHeader.vue';

const posts = ref([]);
const filteredPosts = ref([]); 
const morePost = ref('');
const loadMore = ref(false);

const props = defineProps({
    postCounts: Number,
    likeCounts: Number,
    commentCounts: Number,
    user: Object,
})
const loading = ref(false);
const fetchPosts = async (isInitialLoad = false) => {
    if (!morePost.value || (isInitialLoad ? loading.value : loadMore.value)) return;

    if (isInitialLoad) {
        loading.value = true;
    } else {
        loadMore.value = true;
    }
    
    try {
        const response = await axios.get(morePost.value);
        const newPosts = response.data.data;
        
        if (newPosts.length > 0) {
            posts.value.push(...newPosts);
            filteredPosts.value.push(...newPosts);
        }

        // Extract the next page number
        const nextPage = response.data.current_page + 1;
        
        // Check if we've reached the last page
        if (nextPage > response.data.last_page) {
            morePost.value = null; // No more pages
        } else {
            // Create a new URL with all original params plus the new page number
            const url = new URL(morePost.value);
            url.searchParams.set('page', nextPage);
            morePost.value = url.toString();
        }
    } catch (error) {
        console.error('Error fetching posts:', error);
    } finally {
        if (isInitialLoad) {
            loading.value = false;
        } else {
            loadMore.value = false;
        }
    }
};


const handleScroll = () => {
    const scrollBottom = window.innerHeight + window.scrollY;
    const documentHeight = document.documentElement.offsetHeight;

    if (scrollBottom + 50 >= documentHeight && !loading.value && !loadMore.value && morePost.value) {
        fetchPosts(false); 
    }
};

onMounted(() => {
    // Set initial URL with user_id filter
    morePost.value = route('member.getCommunityPosts', { 
        user_id: props.user.id 
    });
    
    fetchPosts(true);
    
    window.addEventListener('scroll', handleScroll);
});

onBeforeUnmount(() => {
    window.removeEventListener('scroll', handleScroll);
});

const breadcrumb = ref([
    { label: 'Community', href: route('member.community') },
    { label: `${props.user.name} - View Community Profile` }
]);

const handlePostUpdated = (updatedPost) => {
    // Find and replace the updated post in the posts array
    const index = posts.value.findIndex(p => p.id === updatedPost.id);
    if (index !== -1) {
        posts.value[index] = updatedPost;
    }
};

const handlePostDeleted = (postId) => {
    posts.value = posts.value.filter(post => post.id !== postId);
};

const renderHashtags = (html) => {
    // 1. Replace <span data-hashtag="tag">#tag</span> with <a ...>
    let result = html.replace(
        /<span[^>]*data-hashtag="([^"]+)"[^>]*>(#[^<]+)<\/span>/g,
        (match, tag, text) =>
            `<span class="text-primary hover:underline cursor-pointer hashtag-span" data-hashtag="${tag.toLowerCase()}">${text}</span>`
    );
    // 2. Replace plain #hashtag (not inside a tag) with <a ...>
    result = result.replace(
        /(^|[>\s])#([\u4e00-\u9fa5_a-zA-Z0-9]+)/gu,
        (match, prefix, tag) =>
            `${prefix}<span class="text-primary hover:underline cursor-pointer hashtag-span" data-hashtag="${tag.toLowerCase()}">#${tag}</span>`
    );
    return result;
}

</script>

<template>
    <AuthenticatedLayout :title="$t('public.community')">
        <div class="flex flex-col items-center gap-5 justify-center">
            <div class="flex max-w-[680px] md:w-[680px] flex-col items-center gap-5 self-stretch flex-[1_0_0] mx-auto">
                <div class="hidden md:flex items-center self-stretch ">
                    <Breadcrumb class="text-sm text-center py-2" :model="breadcrumb">
                        <template #item="{ item }">
                            <a :href="item.href">
                                <span
                                    class="text-sm font-medium"
                                    :class="item.label === 'Community' ? 'text-primary' : 'text-gray-400'"
                                >
                                    {{ item.label }}
                                </span>                            
                            </a>
                        </template>
                        <template #separator>  &gt;  </template>
                    </Breadcrumb>
                </div>

                <ProfileHeader 
                    :user="props.user" 
                    :posts="posts" 
                    :postCounts="postCounts" 
                    :likeCounts="likeCounts" 
                    :commentCounts="commentCounts"
                />                
                <!-- posts -->
                <template
                    v-if="postCounts === 0 && !posts.length"
                    class="flex flex-col items-center justify-center w-full"
                >
                    <div class="pt-20 self-stretch text-sm text-gray-700 text-center">
                        Community feed is quiet.
                    </div>
                </template>
                
                <template v-else class="flex flex-col w-full gap-4">
                    <Posts 
                        :user="user" 
                        :posts="posts" 
                        :loading="loading"
                        :renderHashtags="renderHashtags"
                        @post-updated="handlePostUpdated" 
                        @post-deleted="handlePostDeleted"
                    />
                    <div v-if="loadMore" class="flex justify-center py-4 mt-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-4 border-primary-500"></div>
                    </div>
                </template>
            </div>
        </div>
    </AuthenticatedLayout>        
</template>
