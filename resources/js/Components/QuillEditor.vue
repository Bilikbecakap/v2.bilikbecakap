<template>
  <div>
    <div ref="quillEditor" class="bg-white dark:bg-slate-700"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, nextTick } from "vue";
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
  uploadUrl: {
    type: String,
    default: "/admin/artikel/upload-image"
  }
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
          [{ header: [1, 2, 3, 4, 5, 6, false] }],
          ["bold", "italic", "underline", "strike"],
          [{ color: [] }, { background: [] }],
          [{ list: "ordered" }, { list: "bullet" }],
          [{ indent: "-1" }, { indent: "+1" }],
          [{ align: [] }],
          ["blockquote", "code-block"],
          ["link", "image", "video"],
          ["clean"],
        ],
        handlers: {
          image: imageHandler,
        },
      },
      history: {
        delay: 1000,
        maxStack: 50,
        userOnly: true
      }
    },
  });

  // Set initial content
  if (props.modelValue) {
    quill.root.innerHTML = props.modelValue;
  }

  // Listen for text changes
  quill.on("text-change", () => {
    const html = quill.root.innerHTML;
    emit("update:modelValue", html === '<p><br></p>' ? '' : html);
    
    // Add resize functionality after content changes
    nextTick(() => {
      addImageResizeHandlers();
    });
  });

  // Add resize handlers after initial load
  nextTick(() => {
    addImageResizeHandlers();
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
      const response = await fetch(props.uploadUrl, {
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
        const range = quill.getSelection(true);
        quill.insertEmbed(range.index, "image", result.url);
        quill.setSelection(range.index + 1);
        
        // Add resize handlers to new image
        nextTick(() => {
          addImageResizeHandlers();
        });
      }
    } catch (error) {
      alert("Error uploading image: " + error.message);
    }
  };
}

// Enhanced image resize functionality with text wrapping
function addImageResizeHandlers() {
  if (!quill) return;
  
  const images = quill.root.querySelectorAll('img');
  
  images.forEach(img => {
    // Remove existing listeners to avoid duplicates
    img.removeEventListener('click', handleImageClick);
    img.removeEventListener('dblclick', handleImageDoubleClick);
    
    // Add click handlers
    img.addEventListener('click', handleImageClick);
    img.addEventListener('dblclick', handleImageDoubleClick);
    
    // Add hover effect
    img.style.cursor = 'pointer';
  });
}

function handleImageClick(e) {
  e.preventDefault();
  const img = e.target;
  
  // Remove selection from other images
  const allImages = quill.root.querySelectorAll('img');
  allImages.forEach(i => {
    i.classList.remove('image-selected');
  });
  
  // Select this image
  img.classList.add('image-selected');
  
  // Show enhanced resize tooltip
  showEnhancedResizeTooltip(img);
}

function handleImageDoubleClick(e) {
  e.preventDefault();
  const img = e.target;
  
  // Prompt for new width
  const currentWidth = img.style.width || '100%';
  const newWidth = prompt(`Ukuran gambar saat ini: ${currentWidth}\n\nMasukkan lebar baru (contoh: 300px, 50%, 25%):`, currentWidth);
  
  if (newWidth !== null && newWidth.trim() !== '') {
    img.style.width = newWidth.trim();
    img.style.height = 'auto';
    
    // Trigger content change
    quill.emitter.emit('text-change');
  }
}

function showEnhancedResizeTooltip(img) {
  // Remove existing tooltip
  const existingTooltip = document.querySelector('.image-resize-tooltip');
  if (existingTooltip) {
    existingTooltip.remove();
  }
  
  // Create enhanced tooltip
  const tooltip = document.createElement('div');
  tooltip.className = 'image-resize-tooltip';
  tooltip.innerHTML = `
    <div class="tooltip-content">
      <div class="tooltip-section">
        <span class="tooltip-label">Ukuran:</span>
        <div class="size-buttons">
          <button onclick="resizeImageWithAlign('25%')" class="resize-btn size-btn">25%</button>
          <button onclick="resizeImageWithAlign('50%')" class="resize-btn size-btn">50%</button>
          <button onclick="resizeImageWithAlign('75%')" class="resize-btn size-btn">75%</button>
          <button onclick="resizeImageWithAlign('100%')" class="resize-btn size-btn">100%</button>
        </div>
      </div>
      
      <div class="tooltip-section">
        <span class="tooltip-label">Posisi:</span>
        <div class="align-buttons">
          <button onclick="alignImage('left')" class="resize-btn align-btn" title="Float Left - Text di kanan">
            <svg width="16" height="12" fill="currentColor">
              <rect x="0" y="0" width="6" height="12" rx="1"/>
              <rect x="8" y="1" width="8" height="1" rx="0.5"/>
              <rect x="8" y="3" width="8" height="1" rx="0.5"/>
              <rect x="8" y="5" width="8" height="1" rx="0.5"/>
              <rect x="8" y="7" width="8" height="1" rx="0.5"/>
              <rect x="8" y="9" width="8" height="1" rx="0.5"/>
            </svg>
          </button>
          <button onclick="alignImage('center')" class="resize-btn align-btn" title="Center - Block">
            <svg width="16" height="12" fill="currentColor">
              <rect x="3" y="0" width="10" height="8" rx="1"/>
              <rect x="0" y="9" width="16" height="1" rx="0.5"/>
              <rect x="0" y="11" width="16" height="1" rx="0.5"/>
            </svg>
          </button>
          <button onclick="alignImage('right')" class="resize-btn align-btn" title="Float Right - Text di kiri">
            <svg width="16" height="12" fill="currentColor">
              <rect x="10" y="0" width="6" height="12" rx="1"/>
              <rect x="0" y="1" width="8" height="1" rx="0.5"/>
              <rect x="0" y="3" width="8" height="1" rx="0.5"/>
              <rect x="0" y="5" width="8" height="1" rx="0.5"/>
              <rect x="0" y="7" width="8" height="1" rx="0.5"/>
              <rect x="0" y="9" width="8" height="1" rx="0.5"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  `;
  
  // Position tooltip
  const rect = img.getBoundingClientRect();
  tooltip.style.position = 'fixed';
  tooltip.style.top = (rect.bottom + 10) + 'px';
  tooltip.style.left = Math.max(10, rect.left) + 'px';
  tooltip.style.zIndex = '1000';
  
  document.body.appendChild(tooltip);
  
  // Global resize function with alignment
  window.resizeImageWithAlign = (size) => {
    img.style.width = size;
    img.style.height = 'auto';
    quill.emitter.emit('text-change');
  };
  
  // Global align function
  window.alignImage = (align) => {
    // Remove existing alignment classes
    img.classList.remove('img-left', 'img-right', 'img-center');
    img.style.float = '';
    img.style.display = '';
    img.style.margin = '';
    
    switch(align) {
      case 'left':
        img.classList.add('img-left');
        img.style.float = 'left';
        img.style.margin = '0 15px 10px 0';
        break;
      case 'right':
        img.classList.add('img-right');
        img.style.float = 'right';
        img.style.margin = '0 0 10px 15px';
        break;
      case 'center':
        img.classList.add('img-center');
        img.style.display = 'block';
        img.style.margin = '10px auto';
        break;
    }
    
    quill.emitter.emit('text-change');
    tooltip.remove();
    img.classList.remove('image-selected');
  };
  
  // Remove tooltip when clicking elsewhere
  setTimeout(() => {
    const handleOutsideClick = (e) => {
      if (!tooltip.contains(e.target) && e.target !== img) {
        tooltip.remove();
        img.classList.remove('image-selected');
        document.removeEventListener('click', handleOutsideClick);
      }
    };
    document.addEventListener('click', handleOutsideClick);
  }, 100);
}

// Watch for external changes
watch(
  () => props.modelValue,
  (newValue) => {
    if (quill && quill.root.innerHTML !== newValue) {
      quill.root.innerHTML = newValue || "";
      nextTick(() => {
        addImageResizeHandlers();
      });
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
  border-top-left-radius: 0.5rem;
  border-top-right-radius: 0.5rem;
}

.ql-container {
  border-bottom: 1px solid #ccc;
  border-left: 1px solid #ccc;
  border-right: 1px solid #ccc;
  border-bottom-left-radius: 0.5rem;
  border-bottom-right-radius: 0.5rem;
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

.dark .ql-toolbar .ql-stroke {
  stroke: #f1f5f9;
}

.dark .ql-toolbar .ql-fill {
  fill: #f1f5f9;
}

.dark .ql-toolbar .ql-picker-label {
  color: #f1f5f9;
}

/* Enhanced image styling with text wrapping */
.ql-editor img {
  max-width: 100%;
  height: auto;
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 4px;
}

.ql-editor img:hover {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

.ql-editor img.image-selected {
  outline: 3px solid #3b82f6;
  outline-offset: 3px;
  box-shadow: 0 0 0 1px white;
}

/* Image alignment classes */
.ql-editor img.img-left {
  float: left;
  margin: 0 15px 10px 0;
  clear: left;
}

.ql-editor img.img-right {
  float: right;
  margin: 0 0 10px 15px;
  clear: right;
}

.ql-editor img.img-center {
  display: block;
  margin: 10px auto;
  float: none;
}

/* Clear floats after paragraphs with floated images */
.ql-editor p:after {
  content: "";
  display: table;
  clear: both;
}

/* Enhanced image resize tooltip */
.image-resize-tooltip {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  padding: 12px;
  min-width: 280px;
}

.tooltip-content {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.tooltip-section {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.tooltip-label {
  font-size: 11px;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.size-buttons, .align-buttons {
  display: flex;
  gap: 4px;
}

.resize-btn {
  padding: 6px 10px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.resize-btn:hover {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
  transform: translateY(-1px);
}

.size-btn {
  min-width: 50px;
  font-weight: 500;
}

.align-btn {
  min-width: 40px;
  padding: 8px;
}

.align-btn svg {
  opacity: 0.7;
}

.align-btn:hover svg {
  opacity: 1;
}

/* Better toolbar button styles */
.ql-toolbar button {
  border-radius: 4px;
  margin: 1px;
}

.ql-toolbar button:hover {
  background-color: #e2e8f0;
}

.ql-toolbar button.ql-active {
  background-color: #3b82f6;
  color: white;
}

.dark .ql-toolbar button:hover {
  background-color: #475569;
}

.dark .ql-toolbar button.ql-active {
  background-color: #3b82f6;
  color: white;
}

/* Better blockquote styling */
.ql-editor blockquote {
  border-left: 4px solid #3b82f6;
  padding-left: 16px;
  margin-left: 0;
  margin-right: 0;
  background-color: #f8fafc;
  padding: 12px 16px;
  border-radius: 0 8px 8px 0;
}

.dark .ql-editor blockquote {
  background-color: #1e293b;
  border-left-color: #60a5fa;
}

/* Code block styling */
.ql-editor .ql-code-block-container {
  background-color: #f1f5f9;
  border-radius: 8px;
  padding: 12px;
  margin: 8px 0;
  font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
}

.dark .ql-editor .ql-code-block-container {
  background-color: #1e293b;
}

/* Video responsive */
.ql-editor .ql-video {
  max-width: 100%;
  height: auto;
}

/* Link styling */
.ql-editor a {
  color: #3b82f6;
  text-decoration: underline;
}

.ql-editor a:hover {
  color: #2563eb;
}

.dark .ql-editor a {
  color: #60a5fa;
}

.dark .ql-editor a:hover {
  color: #93c5fd;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .ql-editor img.img-left,
  .ql-editor img.img-right {
    float: none;
    display: block;
    margin: 10px auto;
    max-width: 100%;
  }
  
  .image-resize-tooltip {
    min-width: 260px;
    left: 10px !important;
    right: 10px;
    width: auto;
  }
}
</style>