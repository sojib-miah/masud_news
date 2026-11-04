<script setup>
import { ref, onMounted, onBeforeUnmount, watch, computed } from "vue";
import { Editor, EditorContent } from "@tiptap/vue-3";
import { StarterKit } from "@tiptap/starter-kit";
import { Underline } from "@tiptap/extension-underline";
import { Link } from "@tiptap/extension-link";
import { TextAlign } from "@tiptap/extension-text-align";
import { TextStyle } from "@tiptap/extension-text-style";
import { Color } from "@tiptap/extension-color";
import { Image } from "@tiptap/extension-image";
import { Table } from "@tiptap/extension-table";
import { TableRow } from "@tiptap/extension-table-row";
import { TableCell } from "@tiptap/extension-table-cell";
import { TableHeader } from "@tiptap/extension-table-header";
import { Heading } from "@tiptap/extension-heading";
import { CodeBlock } from "@tiptap/extension-code-block";

const props = defineProps({
    modelValue: { type: String, default: "" },
    error: { type: String, default: "" },
});

const emit = defineEmits(["update:modelValue"]);

const editor = ref(null);
const isFullscreen = ref(false);
const showColorPicker = ref(false);
const showTableMenu = ref(false);
const isLinkPopupOpen = ref(false);
const linkUrl = ref("");

const colors = [
    "#000000",
    "#374151",
    "#6b7280",
    "#9ca3af",
    "#dc2626",
    "#ea580c",
    "#d97706",
    "#ca8a04",
    "#16a34a",
    "#059669",
    "#0891b2",
    "#0284c7",
    "#2563eb",
    "#4f46e5",
    "#7c3aed",
    "#c026d3",
];

onMounted(() => {
    editor.value = new Editor({
        content: props.modelValue,
        extensions: [
            StarterKit.configure({
                underline: false,
                link: false,
                heading: false,
                codeBlock: false,
            }),
            Underline,
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    class: "editor-link",
                    rel: "noopener noreferrer",
                },
            }),
            TextAlign.configure({ types: ["heading", "paragraph"] }),
            TextStyle,
            Color,
            Image.configure({
                HTMLAttributes: { class: "editor-img" },
            }),
            Table.configure({ resizable: true }),
            TableRow,
            TableCell,
            TableHeader,
            Heading.configure({ levels: [1, 2, 3] }),
            CodeBlock,
        ],
        onUpdate: ({ editor }) => {
            emit("update:modelValue", editor.getHTML());
        },
        editorProps: {
            attributes: {
                class: "prose-editor focus:outline-none",
            },
        },
    });
});

onBeforeUnmount(() => {
    editor.value?.destroy();
    document.body.style.overflow = "auto";
});

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, false);
        }
    },
);

// ============= COMMANDS =============
const cmd = (action) => editor.value?.chain().focus()[action]().run();
const align = (pos) => editor.value?.chain().focus().setTextAlign(pos).run();
const active = (name) => (editor.value?.isActive(name) ? "is-active" : "");
const can = (name) => editor.value?.can().chain().focus()[name]().run();

const setColor = (color) => {
    editor.value?.chain().focus().setColor(color).run();
    showColorPicker.value = false;
};

const toggleColorPicker = () => {
    showColorPicker.value = !showColorPicker.value;
    showTableMenu.value = false;
};

const toggleTableMenu = () => {
    showTableMenu.value = !showTableMenu.value;
    showColorPicker.value = false;
};

const addLink = () => {
    if (isLinkPopupOpen.value) {
        isLinkPopupOpen.value = false;
        return;
    }
    const prev = editor.value?.getAttributes("link").href || "";
    linkUrl.value = prev;
    isLinkPopupOpen.value = true;
};

const applyLink = () => {
    if (linkUrl.value) {
        editor.value
            ?.chain()
            .focus()
            .extendMarkRange("link")
            .setLink({ href: linkUrl.value })
            .run();
    } else {
        editor.value?.chain().focus().unsetLink().run();
    }
    isLinkPopupOpen.value = false;
};

const removeLink = () => {
    editor.value?.chain().focus().unsetLink().run();
    isLinkPopupOpen.value = false;
};

const addImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        editor.value?.chain().focus().setImage({ src: reader.result }).run();
    };
    reader.readAsDataURL(file);
    e.target.value = "";
};

const addTable = (rows = 3, cols = 3) => {
    editor.value
        ?.chain()
        .focus()
        .insertTable({ rows, cols, withHeaderRow: true })
        .run();
    showTableMenu.value = false;
};

const addTableRow = () => editor.value?.chain().focus().addRowAfter().run();
const addTableCol = () => editor.value?.chain().focus().addColumnAfter().run();
const deleteTable = () => editor.value?.chain().focus().deleteTable().run();
const isInTable = computed(() => editor.value?.isActive("table"));

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    document.body.style.overflow = isFullscreen.value ? "hidden" : "auto";
};

// Word / character counters
const wordCount = computed(() => {
    if (!editor.value) return 0;
    const text = editor.value.getText().trim();
    return text ? text.split(/\s+/).length : 0;
});
const charCount = computed(() => {
    if (!editor.value) return 0;
    return editor.value.getText().length;
});

// Close popovers when clicking outside
function handleClickOutside(e) {
    const target = e.target;
    if (!target.closest(".editor-toolbar")) {
        showColorPicker.value = false;
        showTableMenu.value = false;
        isLinkPopupOpen.value = false;
    }
}

onMounted(() => document.addEventListener("mousedown", handleClickOutside));
onBeforeUnmount(() =>
    document.removeEventListener("mousedown", handleClickOutside),
);
</script>

<template>
    <div class="premium-editor" :class="{ 'is-fullscreen': isFullscreen }">
        <!-- ==================== TOOLBAR ==================== -->
        <div class="editor-toolbar">
            <!-- Undo / Redo -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    :disabled="!editor"
                    @click="cmd('undo')"
                    title="Undo (Ctrl+Z)"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :disabled="!editor"
                    @click="cmd('redo')"
                    title="Redo (Ctrl+Y)"
                >
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>

            <!-- Heading / Paragraph -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('paragraph')"
                    @click="cmd('setParagraph')"
                    title="Paragraph"
                >
                    <i class="fa-solid fa-paragraph"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('heading', { level: 1 })"
                    @click="cmd('toggleHeading', { level: 1 })"
                    title="Heading 1"
                >
                    H1
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('heading', { level: 2 })"
                    @click="cmd('toggleHeading', { level: 2 })"
                    title="Heading 2"
                >
                    H2
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('heading', { level: 3 })"
                    @click="cmd('toggleHeading', { level: 3 })"
                    title="Heading 3"
                >
                    H3
                </button>
            </div>

            <!-- Bold / Italic / Underline / Strike -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('bold')"
                    @click="cmd('toggleBold')"
                    title="Bold (Ctrl+B)"
                >
                    <i class="fa-solid fa-bold"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('italic')"
                    @click="cmd('toggleItalic')"
                    title="Italic (Ctrl+I)"
                >
                    <i class="fa-solid fa-italic"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('underline')"
                    @click="cmd('toggleUnderline')"
                    title="Underline (Ctrl+U)"
                >
                    <i class="fa-solid fa-underline"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('strike')"
                    @click="cmd('toggleStrike')"
                    title="Strikethrough"
                >
                    <i class="fa-solid fa-strikethrough"></i>
                </button>
            </div>

            <!-- Color -->
            <div class="tb-group relative">
                <button
                    type="button"
                    class="tb-btn"
                    @click="toggleColorPicker"
                    title="Text color"
                >
                    <i class="fa-solid fa-palette"></i>
                </button>

                <div v-if="showColorPicker" class="tb-popover color-popover">
                    <p class="popover-title">Text Color</p>
                    <div class="color-grid">
                        <button
                            v-for="c in colors"
                            :key="c"
                            type="button"
                            class="color-swatch"
                            :style="{ background: c }"
                            @click="setColor(c)"
                            :title="c"
                        ></button>
                    </div>
                    <div class="color-custom">
                        <label class="text-xs font-medium">Custom</label>
                        <input
                            type="color"
                            @input="setColor($event.target.value)"
                        />
                    </div>
                    <button
                        type="button"
                        class="color-reset"
                        @click="setColor(null)"
                    >
                        Reset color
                    </button>
                </div>
            </div>

            <!-- Alignment -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    :class="active({ textAlign: 'left' })"
                    @click="align('left')"
                    title="Align left"
                >
                    <i class="fa-solid fa-align-left"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active({ textAlign: 'center' })"
                    @click="align('center')"
                    title="Align center"
                >
                    <i class="fa-solid fa-align-center"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active({ textAlign: 'right' })"
                    @click="align('right')"
                    title="Align right"
                >
                    <i class="fa-solid fa-align-right"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active({ textAlign: 'justify' })"
                    @click="align('justify')"
                    title="Justify"
                >
                    <i class="fa-solid fa-align-justify"></i>
                </button>
            </div>

            <!-- Lists -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('bulletList')"
                    @click="cmd('toggleBulletList')"
                    title="Bullet list"
                >
                    <i class="fa-solid fa-list-ul"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('orderedList')"
                    @click="cmd('toggleOrderedList')"
                    title="Numbered list"
                >
                    <i class="fa-solid fa-list-ol"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('blockquote')"
                    @click="cmd('toggleBlockquote')"
                    title="Blockquote"
                >
                    <i class="fa-solid fa-quote-left"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('codeBlock')"
                    @click="cmd('toggleCodeBlock')"
                    title="Code block"
                >
                    <i class="fa-solid fa-code"></i>
                </button>
            </div>

            <!-- Link -->
            <div class="tb-group relative">
                <button
                    type="button"
                    class="tb-btn"
                    :class="active('link')"
                    @click="addLink"
                    title="Insert link"
                >
                    <i class="fa-solid fa-link"></i>
                </button>

                <div v-if="isLinkPopupOpen" class="tb-popover link-popover">
                    <p class="popover-title">Insert Link</p>
                    <input
                        v-model="linkUrl"
                        type="url"
                        placeholder="https://example.com"
                        class="link-input"
                        @keyup.enter="applyLink"
                    />
                    <div class="flex gap-2 mt-2">
                        <button
                            type="button"
                            class="pop-btn primary"
                            @click="applyLink"
                        >
                            Apply
                        </button>
                        <button
                            type="button"
                            class="pop-btn"
                            @click="removeLink"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            <!-- Image -->
            <div class="tb-group">
                <label class="tb-btn" title="Insert image">
                    <i class="fa-regular fa-image"></i>
                    <input
                        type="file"
                        hidden
                        accept="image/*"
                        @change="addImage"
                    />
                </label>
            </div>

            <!-- Table -->
            <div class="tb-group relative">
                <button
                    type="button"
                    class="tb-btn"
                    @click="toggleTableMenu"
                    title="Table"
                >
                    <i class="fa-solid fa-table"></i>
                </button>

                <div v-if="showTableMenu" class="tb-popover table-popover">
                    <p class="popover-title">Table</p>
                    <div class="table-grid">
                        <button
                            v-for="n in 16"
                            :key="n"
                            type="button"
                            class="table-cell-btn"
                            @click="
                                addTable(Math.ceil(n / 4), ((n - 1) % 4) + 1)
                            "
                            :title="`${Math.ceil(n / 4)} × ${((n - 1) % 4) + 1}`"
                        ></button>
                    </div>
                    <div
                        class="pt-2 mt-2 border-t border-slate-200 flex flex-col gap-1"
                    >
                        <button
                            type="button"
                            class="table-action"
                            @click="addTableRow"
                            :disabled="!isInTable"
                        >
                            <i class="fa-solid fa-plus text-xs"></i> Add row
                        </button>
                        <button
                            type="button"
                            class="table-action"
                            @click="addTableCol"
                            :disabled="!isInTable"
                        >
                            <i class="fa-solid fa-plus text-xs"></i> Add column
                        </button>
                        <button
                            type="button"
                            class="table-action text-red-600 hover:bg-red-50"
                            @click="deleteTable"
                            :disabled="!isInTable"
                        >
                            <i class="fa-solid fa-trash text-xs"></i> Delete
                            table
                        </button>
                    </div>
                </div>
            </div>

            <!-- Horizontal rule / clear formatting -->
            <div class="tb-group">
                <button
                    type="button"
                    class="tb-btn"
                    @click="cmd('setHorizontalRule')"
                    title="Horizontal rule"
                >
                    <i class="fa-solid fa-minus"></i>
                </button>
                <button
                    type="button"
                    class="tb-btn"
                    @click="
                        editor
                            ?.chain()
                            .focus()
                            .clearNodes()
                            .unsetAllMarks()
                            .run()
                    "
                    title="Clear formatting"
                >
                    <i class="fa-solid fa-eraser"></i>
                </button>
            </div>

            <!-- Fullscreen -->
            <div class="tb-group ml-auto">
                <button
                    type="button"
                    class="tb-btn"
                    @click="toggleFullscreen"
                    :title="isFullscreen ? 'Exit fullscreen' : 'Fullscreen'"
                >
                    <i
                        :class="
                            isFullscreen
                                ? 'fa-solid fa-compress'
                                : 'fa-solid fa-expand'
                        "
                    ></i>
                </button>
            </div>
        </div>

        <!-- ==================== EDITOR CONTENT ==================== -->
        <div
            class="editor-content-area"
            @click="
                showColorPicker = false;
                showTableMenu = false;
                isLinkPopupOpen = false;
            "
        >
            <EditorContent :editor="editor" class="editor-content" />
        </div>

        <!-- ==================== FOOTER ==================== -->
        <div class="editor-footer">
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span
                    ><i class="fa-regular fa-file-word mr-1"></i
                    >{{ wordCount }} words</span
                >
                <span
                    ><i class="fa-regular fa-keyboard mr-1"></i
                    >{{ charCount }} chars</span
                >
            </div>
            <div class="text-xs text-slate-400">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Rich text supported
            </div>
        </div>

        <p v-if="error" class="editor-error">
            <i class="fa-solid fa-circle-exclamation mr-1"></i>{{ error }}
        </p>
    </div>
</template>

<style scoped>
/* ============ WRAPPER ============ */
.premium-editor {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}

.premium-editor:focus-within {
    border-color: #00930c;
    box-shadow: 0 0 0 3px rgba(0, 147, 12, 0.1);
}

.premium-editor.is-fullscreen {
    position: fixed;
    inset: 0;
    z-index: 9999;
    border-radius: 0;
    display: flex;
    flex-direction: column;
}

.premium-editor.is-fullscreen .editor-content-area {
    flex: 1;
    overflow-y: auto;
}

/* ============ TOOLBAR ============ */
.editor-toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px;
    padding: 8px;
    background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
    border-bottom: 1px solid #e2e8f0;
    position: sticky;
    top: 0;
    z-index: 10;
}

.tb-group {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 2px;
    border-radius: 8px;
    background: #fff;
    border: 1px solid #e2e8f0;
}

.tb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    height: 30px;
    padding: 0 7px;
    border: none;
    background: transparent;
    color: #475569;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition:
        background 0.15s,
        color 0.15s;
    font-family: inherit;
}

.tb-btn:hover {
    background: #f1f5f9;
    color: #00930c;
}

.tb-btn.is-active {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    box-shadow: 0 2px 6px -2px rgba(0, 147, 12, 0.5);
}

.tb-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

.tb-btn i {
    font-size: 13px;
    line-height: 1;
}

/* ============ POPOVERS ============ */
.tb-popover {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 40px -8px rgba(0, 0, 0, 0.2);
    padding: 12px;
    z-index: 100;
    min-width: 220px;
    animation: popIn 0.15s ease;
}

@keyframes popIn {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.popover-title {
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Color popover */
.color-popover {
    min-width: 200px;
}

.color-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 4px;
    border: 1px solid rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: transform 0.15s;
}

.color-swatch:hover {
    transform: scale(1.15);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.color-custom {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.color-custom input[type="color"] {
    width: 34px;
    height: 26px;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    cursor: pointer;
    padding: 2px;
}

.color-reset {
    width: 100%;
    margin-top: 8px;
    padding: 5px;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    cursor: pointer;
}

.color-reset:hover {
    background: #f1f5f9;
}

/* Link popover */
.link-popover {
    min-width: 260px;
}

.link-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s;
}

.link-input:focus {
    border-color: #00930c;
    box-shadow: 0 0 0 2px rgba(0, 147, 12, 0.1);
}

.pop-btn {
    flex: 1;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #475569;
    cursor: pointer;
    transition: background 0.15s;
}

.pop-btn:hover {
    background: #f1f5f9;
}

.pop-btn.primary {
    background: linear-gradient(135deg, #00930c, #00b515);
    color: #fff;
    border-color: #00930c;
}

.pop-btn.primary:hover {
    background: linear-gradient(135deg, #007b0a, #009313);
}

/* Table popover */
.table-popover {
    min-width: 200px;
}

.table-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 3px;
}

.table-cell-btn {
    width: 36px;
    height: 24px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.15s;
}

.table-cell-btn:hover {
    background: #00930c;
    border-color: #00930c;
}

.table-action {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 5px 8px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    border-radius: 5px;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    transition: background 0.15s;
}

.table-action:hover:not(:disabled) {
    background: #f1f5f9;
}

.table-action:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}

/* ============ CONTENT AREA ============ */
.editor-content-area {
    cursor: text;
    background: #fff;
}

.editor-content {
    min-height: 300px;
}

.premium-editor.is-fullscreen .editor-content {
    min-height: calc(100vh - 160px);
}

/* ============ FOOTER ============ */
.editor-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 6px 12px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    font-size: 11px;
}

/* ============ ERROR ============ */
.editor-error {
    color: #dc2626;
    font-size: 12px;
    padding: 8px 12px;
    background: #fef2f2;
    border-top: 1px solid #fecaca;
}

/* ============ RESPONSIVE ============ */
@media (max-width: 640px) {
    .editor-toolbar {
        padding: 6px;
    }
    .tb-btn {
        min-width: 28px;
        height: 28px;
        padding: 0 5px;
        font-size: 12px;
    }
    .tb-group.ml-auto {
        margin-left: 0;
    }
    .tb-popover {
        min-width: 180px;
    }
    .color-grid {
        grid-template-columns: repeat(6, 1fr);
    }
}
</style>

<!-- Global ProseMirror styles (not scoped) -->
<style>
.prose-editor {
    min-height: 260px;
    padding: 16px 18px;
    font-family:
        "Hind Siliguri", "Noto Sans Bengali", "Roboto", system-ui, sans-serif;
    font-size: 15px;
    line-height: 1.7;
    color: #1e293b;
    outline: none;
}

.prose-editor > * + * {
    margin-top: 12px;
}

.prose-editor p {
    margin: 0;
    min-height: 1.5em;
}

.prose-editor h1 {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    margin-top: 20px;
}

.prose-editor h2 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.3;
    margin-top: 18px;
}

.prose-editor h3 {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.35;
    margin-top: 16px;
}

.prose-editor ul,
.prose-editor ol {
    padding-left: 24px;
    margin: 0;
}

.prose-editor ul {
    list-style: disc;
}

.prose-editor ol {
    list-style: decimal;
}

.prose-editor li {
    margin: 4px 0;
}

.prose-editor li > p {
    margin: 0;
}

.prose-editor blockquote {
    border-left: 4px solid #00930c;
    padding: 8px 16px;
    margin: 12px 0;
    background: #f0fdf4;
    border-radius: 0 6px 6px 0;
    color: #334155;
    font-style: italic;
}

.prose-editor pre {
    background: #0f172a;
    color: #e2e8f0;
    padding: 14px 16px;
    border-radius: 8px;
    overflow-x: auto;
    font-family: "JetBrains Mono", "Fira Code", monospace;
    font-size: 13px;
    line-height: 1.6;
}

.prose-editor code {
    background: #f1f5f9;
    color: #db2777;
    padding: 2px 5px;
    border-radius: 4px;
    font-size: 13px;
    font-family: "JetBrains Mono", "Fira Code", monospace;
}

.prose-editor pre code {
    background: transparent;
    color: inherit;
    padding: 0;
}

.prose-editor a,
.prose-editor .editor-link {
    color: #0284c7;
    text-decoration: underline;
    text-underline-offset: 2px;
    cursor: pointer;
    transition: color 0.15s;
}

.prose-editor a:hover {
    color: #0369a1;
}

.prose-editor img,
.prose-editor .editor-img {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    display: block;
}

.prose-editor hr {
    border: none;
    border-top: 2px solid #e2e8f0;
    margin: 20px 0;
}

/* Tables */
.prose-editor table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0;
    border-radius: 8px;
    overflow: hidden;
    table-layout: fixed;
}

.prose-editor th,
.prose-editor td {
    border: 1px solid #e2e8f0;
    padding: 8px 12px;
    vertical-align: top;
    position: relative;
    min-width: 60px;
}

.prose-editor th {
    background: #f8fafc;
    font-weight: 700;
    color: #334155;
    text-align: left;
}

.prose-editor td {
    background: #fff;
}

.prose-editor .selectedCell:after {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 147, 12, 0.1);
    pointer-events: none;
}

.prose-editor .column-resize-handle {
    position: absolute;
    right: -2px;
    top: 0;
    bottom: 0;
    width: 4px;
    background: #00930c;
    pointer-events: none;
}

.prose-editor.resize-cursor {
    cursor: col-resize;
}

/* Placeholder */
.prose-editor p.is-editor-empty:first-child::before {
    content: "Write your content here...";
    color: #94a3b8;
    float: left;
    height: 0;
    pointer-events: none;
}

/* Selection */
.prose-editor ::selection {
    background: rgba(0, 147, 12, 0.2);
}

/* Scrollbar inside fullscreen */
.editor-content-area::-webkit-scrollbar {
    width: 8px;
}
.editor-content-area::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.editor-content-area::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
