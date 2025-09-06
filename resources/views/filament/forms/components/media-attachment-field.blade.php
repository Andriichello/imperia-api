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
                <div class="border border-gray-200 rounded p-3 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <input type="file" :multiple="multiple" @change="onFileChange($event)" :accept="accepted.join(',')" />
                        </div>
                        <div class="text-sm text-gray-500" x-text="infoText()"></div>
                    </div>
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <template x-for="(m, idx) in media" :key="m.id">
                            <div class="border rounded p-2 bg-gray-50 flex flex-col gap-2">
                                <template x-if="preview && isImage(m)">
                                    <img :src="m.url" alt="" class="w-full h-24 object-cover rounded" />
                                </template>
                                <template x-if="!isImage(m)">
                                    <div class="text-xs break-all" x-text="m.name"></div>
                                </template>
                                <div class="flex items-center justify-between text-xs">
                                    <button type="button" class="text-red-600 hover:underline" @click="remove(idx)">Remove</button>
                                    <div class="flex gap-1">
                                        <button type="button" class="px-1 border rounded" @click="move(idx, -1)">↑</button>
                                        <button type="button" class="px-1 border rounded" @click="move(idx, 1)">↓</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
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

                remove(idx) {
                    this.media.splice(idx, 1);
                    this.sync();
                },

                move(idx, dir) {
                    const ni = idx + dir;
                    if (ni < 0 || ni >= this.media.length) { return; }
                    const temp = this.media[idx];
                    this.media[idx] = this.media[ni];
                    this.media[ni] = temp;
                    this.sync();
                },
            }
        }
    </script>
</div>
