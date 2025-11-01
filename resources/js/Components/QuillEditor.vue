<template>
  <div>
    <div ref="quillEditor" class="bg-white dark:bg-slate-700"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import Quill from "quill";
import "quill/dist/quill.snow.css";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  placeholder: {
    type: String,
    default: "Tulis konten di sini...",
  },
});

const emit = defineEmits(["update:modelValue"]);

const quillEditor = ref(null);
let quill = null;

onMounted(() => {
  quill = new Quill(quillEditor.value, {
    theme: "snow",
    placeholder: props.placeholder,
    modules: {
      toolbar: {
        container: [
          [{ header: [1, 2, 3, false] }],
          ["bold", "italic", "underline", "strike"],
          [{ color: [] }, { background: [] }],
          [{ list: "ordered" }, { list: "bullet" }],
          [{ indent: "-1" }, { indent: "+1" }],
          [{ align: [] }],
          ["blockquote", "code-block"],
          ["link", "image"],
          ["clean"],
        ],
        handlers: {
          image: imageHandler,
        },
      },
    },
  });

  // Set initial content
  if (props.modelValue) {
    quill.root.innerHTML = props.modelValue;
  }

  // Listen for text changes
  quill.on("text-change", () => {
    emit("update:modelValue", quill.root.innerHTML);
  });
});

// Custom image handler
function imageHandler() {
  const input = document.createElement("input");
  input.setAttribute("type", "file");
  input.setAttribute("accept", "image/*");
  input.click();

  input.onchange = async () => {
    const file = input.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append("upload", file);

    try {
      const response = await fetch("/admin/artikel/upload-image", {
        method: "POST",
        body: formData,
        headers: {
          "X-CSRF-TOKEN": document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
        },
      });

      const result = await response.json();

      if (result.url) {
        const range = quill.getSelection();
        quill.insertEmbed(range.index, "image", result.url);
      }
    } catch (error) {
      alert("Error uploading image: " + error.message);
    }
  };
}

// Watch for external changes
watch(
  () => props.modelValue,
  (newValue) => {
    if (quill && quill.root.innerHTML !== newValue) {
      quill.root.innerHTML = newValue || "";
    }
  }
);
</script>

<style>
/* Custom Quill styles */
.ql-editor {
  min-height: 300px;
  font-size: 14px;
  line-height: 1.6;
}

.ql-toolbar {
  border-top: 1px solid #ccc;
  border-left: 1px solid #ccc;
  border-right: 1px solid #ccc;
}

.ql-container {
  border-bottom: 1px solid #ccc;
  border-left: 1px solid #ccc;
  border-right: 1px solid #ccc;
}

/* Dark mode support */
.dark .ql-toolbar {
  background: rgb(51 65 85);
  border-color: rgb(71 85 105);
}

.dark .ql-container {
  background: rgb(51 65 85);
  border-color: rgb(71 85 105);
}

.dark .ql-editor {
  color: white;
}
</style>
