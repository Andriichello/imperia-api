@php
    /** @var array $mediaAttachment */
    $modelType = $mediaAttachment['modelType'] ?? null;
    $accepted = $mediaAttachment['acceptedFileTypes'] ?? [];
    $maxFiles = (int) ($mediaAttachment['maxFiles'] ?? 0);
    $maxSize = (int) ($mediaAttachment['maxSize'] ?? 0); // KB
    $preview = (bool) ($mediaAttachment['preview'] ?? true);
    $multiple = (bool) ($mediaAttachment['multiple'] ?? true);
    $disk = $mediaAttachment['disk'] ?? null;

    $recordId = null;
    if (isset($getRecord) && is_callable($getRecord)) {
        $record = $getRecord();
        $recordId = $record?->getKey();
        // Default model type fallback to slugClass if not configured
        if (!$modelType && $record) {
            $modelType = slugClass($record);
        }
    }
@endphp

<div x-data="mediaAttachmentField({
        modelId: {{ $recordId ? (int) $recordId : 'null' }},
        modelType: @js($modelType),
        accepted: @js($accepted),
        maxFiles: {{ $maxFiles }},
        maxSize: {{ $maxSize }},
        preview: {{ $preview ? 'true' : 'false' }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        disk: @js($disk),
    })"
    x-init="init()"
    class="fi-fo-field-wrp">
    <div class="flex items-start gap-4">
        <template x-if="!modelId">
            <div class="text-sm text-gray-500">Save the record first to attach media.</div>
        </template>
        <template x-if="modelId">
            <div class="w-full">
                <div class="border border-gray-200 rounded p-2 bg-white">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div>
                            <input type="file" :multiple="multiple" @change="onFileChange($event)" :accept="accepted.join(',')" class="text-sm" />
                        </div>
                    </div>
                  <div class="flex items-center justify-end gap-2 mb-2">
                    <div class="text-xs text-gray-500" x-text="infoText()"></div>
                  </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2">
                        <template x-for="(m, idx) in media" :key="m.id">
                            <div class="border border-gray-200 dark:border-gray-600 rounded p-1.5 bg-gray-50 dark:bg-gray-700 flex flex-col cursor-move"
                                 draggable="true"
                                 @dragstart="dragStart(idx, $event)"
                                 @dragover.prevent="dragOver(idx, $event)"
                                 @drop.prevent="drop(idx, $event)"
                                 @dragend="dragEnd($event)"
                                 :class="{ 'opacity-50': draggingIndex === idx, 'ring-2 ring-blue-400': dragOverIndex === idx }">
                                <template x-if="preview && isImage(m)">
                                    <div class="w-full aspect-square flex items-center justify-center bg-gray-100 rounded overflow-hidden mb-1.5 pointer-events-none">
                                        <img :src="m.url" alt="" class="max-w-full max-h-full object-contain" />
                                    </div>
                                </template>
                                <template x-if="!isImage(m)">
                                    <div class="text-xs break-all mb-1.5 pointer-events-none" x-text="m.name"></div>
                                </template>
                                <div class="mt-auto flex items-center justify-center gap-1">
                                    <button type="button" class="text-red-600 hover:bg-red-50 px-2 py-1 rounded text-xs font-medium transition" @click="remove(idx)">Remove</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <!-- Remove Confirmation Modal -->
    <div x-show="showRemoveModal"
         x-cloak
         @click.self="cancelRemove()"
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color: rgba(0, 0, 0, 0.75);">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Remove Media</h3>
            <p class="text-sm text-gray-600 dark:text-gray-200 mb-6">Are you sure you want to remove this media? This action cannot be undone.</p>
            <div class="flex justify-end gap-3">
                <button type="button"
                        @click="cancelRemove()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 transition">
                    Cancel
                </button>
                <button type="button"
                        @click="remove()"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                    Remove
                </button>
            </div>
        </div>
    </div>

    <script>
        function mediaAttachmentField(cfg) {
            return {
                modelId: cfg.modelId,
                modelType: cfg.modelType,
                accepted: cfg.accepted || [],
                maxFiles: cfg.maxFiles || 0,
                maxSize: cfg.maxSize || 0,
                preview: cfg.preview !== false,
                multiple: cfg.multiple !== false,
                disk: cfg.disk || null,
                media: [],
                loading: false,
                errors: [],
                draggingIndex: null,
                dragOverIndex: null,
                showRemoveModal: false,
                itemToRemove: null,

                csrfToken() {
                    const el = document.querySelector('meta[name="csrf-token"]');
                    return el ? el.getAttribute('content') : null;
                },

                async init() {
                    if (!this.modelId || !this.modelType) {
                        return;
                    }
                    await this.fetchAttached();
                },

                infoText() {
                    const parts = [];
                    if (this.maxFiles) { parts.push(`Max files: ${this.maxFiles}`); }
                    if (this.maxSize) { parts.push(`Max size: ${this.maxSize} KB`); }
                    return parts.join(' · ');
                },

                async fetchAttached() {
                    try {
                        this.loading = true;
                        const res = await fetch(`/api/model-media?model_id=${this.modelId}&model_type=${encodeURIComponent(this.modelType)}`, {
                            headers: { 'Accept': 'application/json' },
                            credentials: 'same-origin',
                        });
                        const json = await res.json();
                        this.media = (json?.data) || [];
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                async onFileChange(e) {
                    const files = Array.from(e.target.files || []);
                    if (!files.length) { return; }

                    // validate count
                    if (this.maxFiles && (this.media.length + files.length) > this.maxFiles) {
                        alert('Too many files selected.');
                        return;
                    }

                    for (const file of files) {
                        if (this.accepted.length && !this.accepted.includes(file.type)) {
                            alert(`File type not allowed: ${file.type}`);
                            continue;
                        }
                        if (this.maxSize && file.size > this.maxSize * 1024) {
                            alert(`File too large: ${file.name}`);
                            continue;
                        }
                        await this.uploadFile(file);
                    }

                    await this.sync();
                    e.target.value = '';
                },

                isImage(m) {
                    return (m?.mime || '').startsWith('image/');
                },

                async uploadFile(file) {
                    const form = new FormData();
                    form.append('file', file);
                    if (this.disk) { form.append('disk', this.disk); }

                    try {
                        const res = await fetch('/api/media', {
                            method: 'POST',
                            body: form,
                            headers: { 'X-CSRF-TOKEN': this.csrfToken() },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err?.message || 'Upload failed');
                        }
                        const json = await res.json();
                        if (json?.data) {
                            this.media.push(json.data);
                        }
                    } catch (e) {
                        console.error(e);
                        alert(e.message || 'Upload failed');
                    }
                },

                async sync() {
                    try {
                        const payload = {
                            model_id: this.modelId,
                            model_type: this.modelType,
                            media: this.media.map(m => ({ id: m.id }))
                        };
                        const res = await fetch('/api/model-media', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': this.csrfToken(),
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        });
                        if (!res.ok) {
                            const err = await res.json().catch(() => ({}));
                            throw new Error(err?.message || 'Failed to attach media');
                        }
                        const json = await res.json();
                        this.media = (json?.data) || this.media;
                    } catch (e) {
                        console.error(e);
                        alert(e.message || 'Failed to attach media');
                    }
                },

                confirmRemove(idx) {
                    this.itemToRemove = idx;
                    this.showRemoveModal = true;
                },

                remove() {
                    if (this.itemToRemove !== null) {
                        this.media.splice(this.itemToRemove, 1);
                        this.sync();
                    }
                    this.showRemoveModal = false;
                    this.itemToRemove = null;
                },

                cancelRemove() {
                    this.showRemoveModal = false;
                    this.itemToRemove = null;
                },

                dragStart(idx, e) {
                    this.draggingIndex = idx;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/html', e.target);
                },

                dragOver(idx, e) {
                    this.dragOverIndex = idx;
                    e.dataTransfer.dropEffect = 'move';
                },

                drop(idx, e) {
                    if (this.draggingIndex !== null && this.draggingIndex !== idx) {
                        const draggedItem = this.media[this.draggingIndex];
                        this.media.splice(this.draggingIndex, 1);
                        this.media.splice(idx, 0, draggedItem);
                        this.sync();
                    }
                    this.dragOverIndex = null;
                },

                dragEnd(e) {
                    this.draggingIndex = null;
                    this.dragOverIndex = null;
                },
            }
        }
    </script>
</div>
