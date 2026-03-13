<template>
    <div class="rich-text-editor">
        <QuillEditor
            v-model="content"
            :placeholder="placeholder"
            @update:modelValue="updateValue"
        />
    </div>
</template>

<script>
import QuillEditor from './QuillEditor.vue'

export default {
    name: 'RichTextEditor',
    components: {
        QuillEditor
    },
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Start writing your article...'
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            content: this.modelValue
        }
    },
    methods: {
        updateValue(value) {
            this.content = value
            this.$emit('update:modelValue', value)
        }
    },
    watch: {
        modelValue(newValue) {
            this.content = newValue
        }
    }
}
</script>

<style scoped>
.rich-text-editor {
    @apply w-full;
}
</style>