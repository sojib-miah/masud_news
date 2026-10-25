<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
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
            Link.configure({ openOnClick: false }),

            TextAlign.configure({ types: ["heading", "paragraph"] }),
            TextStyle,
            Color,
            Image,

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
    });
});

onBeforeUnmount(() => editor.value?.destroy());

watch(
    () => props.modelValue,
    (value) => {
        if (editor.value && value !== editor.value.getHTML()) {
            editor.value.commands.setContent(value, false);
        }
    },
);

const cmd = (action) => editor.value?.chain().focus()[action]().run();
const align = (pos) => editor.value?.chain().focus().setTextAlign(pos).run();
const active = (name) => (editor.value?.isActive(name) ? "active" : "");

const setColor = (e) =>
    editor.value?.chain().focus().setColor(e.target.value).run();

const addLink = () => {
    const url = prompt("Enter URL");
    if (url) editor.value?.chain().focus().setLink({ href: url }).run();
};

const addImage = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () =>
        editor.value?.chain().focus().setImage({ src: reader.result }).run();
    reader.readAsDataURL(file);
};

const addTable = () =>
    editor.value?.chain().focus().insertTable({ rows: 3, cols: 3 }).run();

const toggleFullscreen = () => {
    isFullscreen.value = !isFullscreen.value;
    document.body.style.overflow = isFullscreen.value ? "hidden" : "auto";
};
</script>

<template>
    <div class="summernote" :class="{ fullscreen: isFullscreen }">
        <div class="sn-toolbar">
            <button
                type="button"
                @click="cmd('toggleBold')"
                :class="active('bold')"
            >
                <b>B</b>
            </button>
            <button
                type="button"
                @click="cmd('toggleItalic')"
                :class="active('italic')"
            >
                <i>I</i>
            </button>
            <button
                type="button"
                @click="cmd('toggleUnderline')"
                :class="active('underline')"
            >
                <u>U</u>
            </button>

            <input type="color" @input="setColor" />

            <button type="button" @click="align('left')">⯇</button>
            <button type="button" @click="align('center')">≡</button>
            <button type="button" @click="align('right')">⯈</button>
            <button type="button" @click="align('justify')">☰</button>

            <button type="button" @click="cmd('toggleBulletList')">
                • List
            </button>
            <button type="button" @click="cmd('toggleOrderedList')">
                1. List
            </button>

            <button type="button" @click="addTable">Table</button>

            <label class="file-btn">
                Image
                <input type="file" hidden @change="addImage" />
            </label>

            <button type="button" @click="addLink">Link</button>
            <button type="button" @click="cmd('undo')">↺</button>
            <button type="button" @click="cmd('redo')">↻</button>
            <button type="button" @click="toggleFullscreen">⛶</button>
        </div>

        <EditorContent :editor="editor" class="sn-editor" />

        <p v-if="error" class="sn-error">{{ error }}</p>
    </div>
</template>

<style scoped>
.summernote {
    border: 1px solid #ced4da;
    border-radius: 4px;
    background: #fff;
}

.sn-toolbar {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    padding: 6px;
    background: #f8f9fa;
    border-bottom: 1px solid #ced4da;
}

.sn-toolbar button,
.sn-toolbar input[type="color"],
.file-btn {
    border: 1px solid #ced4da;
    background: #fff;
    padding: 4px 8px;
    font-size: 13px;
    border-radius: 3px;
    cursor: pointer;
}

.sn-toolbar button.active {
    background: #0d6efd;
    color: #fff;
}

.file-btn {
    display: inline-flex;
    align-items: center;
}

.sn-editor {
    min-height: 300px;
    padding: 12px;
}

.fullscreen {
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: #fff;
}

.sn-error {
    color: red;
    font-size: 12px;
    padding: 4px;
}
</style>

<style>
.ProseMirror {
    font-family: 'Roboto', sans-serif;
    font-size: 14px;
    line-height: 1.6;
    color: #212529;
    min-height: 200px;
    border: 1px solid rgb(194, 194, 194);
    padding: 10px;
    border-radius: 5px;
}

.ProseMirror p {
    margin: 0 0 10px;
}

.ProseMirror h1 {
    font-size: 26px;
}
.ProseMirror h2 {
    font-size: 22px;
}
.ProseMirror h3 {
    font-size: 18px;
}

.ProseMirror table {
    width: 100%;
    border-collapse: collapse;
}

.ProseMirror th,
.ProseMirror td {
    border: 1px solid #ced4da;
    padding: 6px;
}

.ProseMirror img {
    max-width: 100%;
}
</style>
