<script setup>
import {ref } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Placeholder from '@tiptap/extension-placeholder'
import Emoji from '@tiptap/extension-emoji'
import { useAttrs } from 'vue'
import Link from '@tiptap/extension-link'
import Mention from '@tiptap/extension-mention'
import axios from 'axios'
import tippy from 'tippy.js'
import HashtagDropdown from './HashtagDropdown.vue';
import { VueRenderer } from '@tiptap/vue-3'

const attrs = useAttrs()

const props = defineProps({
    modelValue: String,
    placeholder: {
        type: String,
    },
})

const emit = defineEmits(['update:modelValue']);
const hashtagSuggestions = ref([])

const fetchHashtags = async (query) => {
    const { data } = await axios.get('/member/getHashtags', { params: { q: query } })
    hashtagSuggestions.value = data.map(name => ({ id: name, label: name }))
}
const editor = useEditor({
    content: props.modelValue,
    onUpdate: ({editor}) => {
        emit('update:modelValue', editor.getHTML())
    },
    editorProps: {
        attributes: {
            class: `caret-primary-500 text-sm py-3 px-4 rounded-md text-gray-950 placeholder:text-gray-400 bg-surface-0 border border-surface-300 hover:border-gray-500 focus:outline-none focus:ring-0 focus:border-primary-500 appearance-none shadow-input transition-colors duration-200 prose prose-p:my-0 prose-ul:my-0 ${attrs.class || ''}`,
        },
    },
    extensions: [
        StarterKit,
        Placeholder.configure({
            placeholder: props.placeholder,
        }),
        Emoji,
        Link.configure({
            openOnClick: true,  
            HTMLAttributes: {
                class: 'text-primary-500 font-medium',
            },
        }),
        Mention.configure({
            deleteTriggerWithBackspace: true,
            HTMLAttributes: {
                class: 'text-primary-500 font-medium cursor-pointer',
            },
            renderHTML({ node }) {
                const tag = node.attrs.label ?? node.attrs.id
                return [
                    'span',
                    {
                        'data-hashtag': tag,
                        class: 'hashtag text-primary hover:underline cursor-pointer',
                    },
                    `#${tag}`,
                ]
            },
            renderText({ options, node }) {
                return `${options.suggestion.char}${node.attrs.label ?? node.attrs.id}`
            },
            suggestion: {
                char: '#',
                items: async ({ query }) => {
                    await fetchHashtags(query)
                    let suggestions = hashtagSuggestions.value
                    // If the query is not in suggestions, add it as a new option
                    if (
                        query &&
                        !suggestions.some(item => item.label.toLowerCase() === query.toLowerCase())
                    ) {
                        suggestions = [
                            { id: query, label: query, isNew: true },
                            ...suggestions,
                        ]
                    }
                    return suggestions
                },
                render: () => {
                    let component
                    let popup

                    return {
                    onStart: (props) => {
                        component = new VueRenderer(HashtagDropdown, {
                        props,
                        editor: props.editor,
                        })

                        popup = tippy('body', {
                        getReferenceClientRect: props.clientRect,
                        appendTo: () => document.body,
                        content: component.element,
                        showOnCreate: true,
                        interactive: true,
                        trigger: 'manual',
                        placement: 'bottom-start',
                        })
                    },
                    onUpdate(props) {
                        component.updateProps(props)
                        popup[0].setProps({
                        getReferenceClientRect: props.clientRect,
                        })
                    },
                    onKeyDown(props) {
                        if (props.event.key === 'Escape') {
                        popup[0].hide()
                        return true
                        }
                        return false
                    },
                    onExit() {
                        popup[0].destroy()
                        component.destroy()
                    },
                    }
                },
            },
        }),
    ],

});
defineExpose({
    editor,
})
</script>

<template>
    <div class=" w-full">
        <EditorContent :editor="editor" class="w-full"/>
    </div>
</template>
