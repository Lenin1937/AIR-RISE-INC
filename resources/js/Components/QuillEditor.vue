<template>
    <div class="rich-text-editor-container">
        <div class="toolbar bg-gray-700 border border-gray-600 rounded-t-md p-2 flex flex-wrap gap-2">
            <button 
                type="button"
                @click="execCommand('bold')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Bold"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M5 4a2 2 0 012-2h5a3 3 0 010 6 3 3 0 010 6H7a2 2 0 01-2-2V4zM9 6V4h3a1 1 0 010 2H9zM9 10v2h4a1 1 0 010-2H9z"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="execCommand('italic')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Italic"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 1a1 1 0 00-1 1v1H6a1 1 0 000 2h1v10H6a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2H9V5h1a1 1 0 100-2H9V2a1 1 0 00-1-1z"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="execCommand('underline')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Underline"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M4 18h12a1 1 0 100-2H4a1 1 0 100 2zM5 8a5 5 0 1110 0v2a1 1 0 11-2 0V8a3 3 0 10-6 0v2a1 1 0 11-2 0V8z"/>
                </svg>
            </button>
            <div class="border-l border-gray-600 mx-2"></div>
            <button 
                type="button"
                @click="execCommand('formatBlock', 'H1')"
                class="px-3 py-2 rounded hover:bg-gray-600 transition-colors font-semibold text-gray-300"
                title="Heading 1"
            >
                H1
            </button>
            <button 
                type="button"
                @click="execCommand('formatBlock', 'H2')"
                class="px-3 py-2 rounded hover:bg-gray-600 transition-colors font-semibold text-gray-300"
                title="Heading 2"
            >
                H2
            </button>
            <button 
                type="button"
                @click="execCommand('formatBlock', 'H3')"
                class="px-3 py-2 rounded hover:bg-gray-600 transition-colors font-semibold text-gray-300"
                title="Heading 3"
            >
                H3
            </button>
            <button 
                type="button"
                @click="execCommand('formatBlock', 'P')"
                class="px-3 py-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Paragraph"
            >
                P
            </button>
            <div class="border-l border-gray-600 mx-2"></div>
            <button 
                type="button"
                @click="execCommand('insertUnorderedList')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Bullet List"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 16a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="execCommand('insertOrderedList')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Numbered List"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 16a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="execCommand('formatBlock', 'BLOCKQUOTE')"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Blockquote"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z"/>
                </svg>
            </button>
            <div class="border-l border-gray-600 mx-2"></div>
            <button 
                type="button"
                @click="insertLink"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Insert Link"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd"/>
                </svg>
            </button>
            <div class="border-l border-gray-600 mx-2"></div>
            <button 
                type="button"
                @click="undo"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Undo"
                :disabled="!canUndo"
                :class="{ 'opacity-50 cursor-not-allowed': !canUndo }"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
            <button 
                type="button"
                @click="redo"
                class="p-2 rounded hover:bg-gray-600 transition-colors text-gray-300"
                title="Redo"
                :disabled="!canRedo"
                :class="{ 'opacity-50 cursor-not-allowed': !canRedo }"
            >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
        <div 
            ref="editor"
            class="editor border border-t-0 border-gray-600 bg-gray-800 rounded-b-md p-4 min-h-[200px] focus:outline-none focus:ring-2 focus:ring-[#d4a02f] text-white"
            contenteditable="true"
            @input="onInput"
            @paste="onPaste"
        ></div>
    </div>
</template>

<script>
export default {
    name: 'QuillEditor',
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: 'Write your content here...'
        }
    },
    emits: ['update:modelValue'],
    data() {
        return {
            isInternalChange: false,
            history: [],
            historyStep: -1,
            isUndoRedo: false,
            saveDebounce: null
        }
    },
    computed: {
        canUndo() {
            return this.historyStep > 0
        },
        canRedo() {
            return this.historyStep < this.history.length - 1
        }
    },
    methods: {
        execCommand(command, value = null) {
            document.execCommand(command, false, value)
            this.$refs.editor.focus()
            this.onInput()
        },
        onInput() {
            this.isInternalChange = true
            const content = this.$refs.editor.innerHTML
            this.$emit('update:modelValue', content)
            
            // Save to history with debounce (only if not undoing/redoing)
            if (!this.isUndoRedo) {
                this.saveToHistory(content)
            }
            
            this.$nextTick(() => {
                this.isInternalChange = false
            })
        },
        saveToHistory(content) {
            // Debounce saving to history
            clearTimeout(this.saveDebounce)
            this.saveDebounce = setTimeout(() => {
                // Remove any forward history when making a new change
                if (this.historyStep < this.history.length - 1) {
                    this.history = this.history.slice(0, this.historyStep + 1)
                }
                
                // Only save if content is different from last history item
                if (this.history.length === 0 || this.history[this.history.length - 1] !== content) {
                    this.history.push(content)
                    this.historyStep++
                    
                    // Limit history size to 50 items
                    if (this.history.length > 50) {
                        this.history.shift()
                        this.historyStep--
                    }
                }
            }, 500)
        },
        undo() {
            if (this.canUndo) {
                this.historyStep--
                this.isUndoRedo = true
                const content = this.history[this.historyStep]
                const range = this.saveSelection()
                this.$refs.editor.innerHTML = content
                this.$emit('update:modelValue', content)
                this.$nextTick(() => {
                    this.restoreSelection(range)
                    this.isUndoRedo = false
                })
            }
        },
        redo() {
            if (this.canRedo) {
                this.historyStep++
                this.isUndoRedo = true
                const content = this.history[this.historyStep]
                const range = this.saveSelection()
                this.$refs.editor.innerHTML = content
                this.$emit('update:modelValue', content)
                this.$nextTick(() => {
                    this.restoreSelection(range)
                    this.isUndoRedo = false
                })
            }
        },
        insertLink() {
            const url = prompt('Enter the URL:')
            if (url) {
                document.execCommand('createLink', false, url)
                this.$refs.editor.focus()
                this.onInput()
            }
        },
        onPaste(event) {
            event.preventDefault()
            const text = event.clipboardData.getData('text/plain')
            document.execCommand('insertText', false, text)
        },
        saveSelection() {
            const selection = window.getSelection()
            if (selection.rangeCount > 0) {
                return selection.getRangeAt(0)
            }
            return null
        },
        restoreSelection(range) {
            if (range) {
                const selection = window.getSelection()
                selection.removeAllRanges()
                selection.addRange(range)
            }
        },
        updateContent(newValue) {
            if (this.$refs.editor && this.$refs.editor.innerHTML !== newValue) {
                const range = this.saveSelection()
                this.$refs.editor.innerHTML = newValue || ''
                this.$nextTick(() => {
                    this.restoreSelection(range)
                })
            }
        }
    },
    watch: {
        modelValue(newValue) {
            // Only update if change came from external source (not from typing)
            if (!this.isInternalChange) {
                this.updateContent(newValue)
            }
        }
    },
    mounted() {
        // Set initial content
        if (this.$refs.editor) {
            this.$refs.editor.innerHTML = this.modelValue || ''
            // Initialize history with the initial content
            if (this.modelValue) {
                this.history.push(this.modelValue)
                this.historyStep = 0
            }
        }
        if (!this.modelValue && this.placeholder) {
            this.$refs.editor.setAttribute('data-placeholder', this.placeholder)
        }
    }
}
</script>

<style scoped>
.editor:empty:before {
    content: attr(data-placeholder);
    color: #6B7280;
    pointer-events: none;
}

.editor:focus:before {
    content: '';
}

/* Rich Text Editor Content Styling */
.editor :deep(h1) {
    font-size: 2rem;
    font-weight: 700;
    line-height: 1.2;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    color: #FFFFFF;
}

.editor :deep(h2) {
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1.3;
    margin-top: 1.25rem;
    margin-bottom: 0.875rem;
    color: #F3F4F6;
}

.editor :deep(h3) {
    font-size: 1.25rem;
    font-weight: 600;
    line-height: 1.4;
    margin-top: 1rem;
    margin-bottom: 0.75rem;
    color: #E5E7EB;
}

.editor :deep(p) {
    font-size: 1rem;
    line-height: 1.75;
    margin-bottom: 1rem;
    color: #D1D5DB;
}

.editor :deep(ul) {
    list-style-type: disc;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.editor :deep(ol) {
    list-style-type: decimal;
    padding-left: 1.5rem;
    margin-bottom: 1rem;
}

.editor :deep(li) {
    margin-bottom: 0.5rem;
    line-height: 1.75;
    color: #D1D5DB;
}

.editor :deep(strong),
.editor :deep(b) {
    font-weight: 700;
    color: #FFFFFF;
}

.editor :deep(em),
.editor :deep(i) {
    font-style: italic;
    color: #E5E7EB;
}

.editor :deep(u) {
    text-decoration: underline;
    color: #D1D5DB;
}

.editor :deep(a) {
    color: #60A5FA;
    text-decoration: underline;
}

.editor :deep(a:hover) {
    color: #93C5FD;
}

.editor :deep(blockquote) {
    border-left: 4px solid #6B7280;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #9CA3AF;
}

.editor :deep(code) {
    background-color: #374151;
    color: #F9FAFB;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}

.editor :deep(pre) {
    background-color: #1F2937;
    color: #F9FAFB;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1rem 0;
}

.editor :deep(pre code) {
    background-color: transparent;
    padding: 0;
    color: inherit;
}
</style>