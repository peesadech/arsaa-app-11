<template>
    <div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-xl mx-auto">
            <!-- Breadcrumb / Header -->
            <div class="flex items-center space-x-4 mb-8">
                <a href="/admin/permission-types" 
                   class="group flex items-center justify-center w-10 h-10 rounded-full bg-white shadow-sm border border-gray-200 text-gray-500 hover:text-indigo-600 hover:border-indigo-300 transition-all duration-200">
                    <i class="fas fa-arrow-left group-hover:-translate-x-0.5 transition-transform"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Add New Category</h1>
                    <p class="text-sm text-gray-500 font-medium px-1">Permission Types Setup</p>
                </div>
            </div>

            <!-- Main Card -->
            <div class="bg-white rounded-3xl shadow-xl shadow-gray-200/50 border border-gray-100 overflow-hidden transform transition-all">
                <!-- Decorative Top Border -->
                <div class="h-2 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500"></div>
                
                <div class="p-8 sm:p-10">
                    <!-- Visual Identity Section -->
                    <div class="flex flex-col items-center text-center mb-10">
                        <div class="relative">
                            <div class="absolute inset-0 bg-indigo-500/20 blur-2xl rounded-full"></div>
                            <div class="relative w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mb-4 transform rotate-3 border border-indigo-100 shadow-inner">
                                <i class="fas fa-layer-group text-3xl text-indigo-600 -rotate-3"></i>
                            </div>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800 mb-2">Type Definition</h2>
                        <p class="text-sm text-gray-500 max-w-xs mx-auto text-pretty">
                            Define a new classification and upload a representative icon for your permissions.
                        </p>
                    </div>

                    <!-- Form -->
                    <form @submit.prevent="saveType" class="space-y-8">
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                                Classification Name
                            </label>
                            <div class="group relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                    <i class="fas fa-terminal text-sm"></i>
                                </div>
                                <input
                                    v-model="name"
                                    type="text"
                                    class="block w-full pl-10 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0 focus:border-indigo-500 focus:bg-white transition-all duration-200"
                                    placeholder="e.g. Content Management"
                                    required
                                    autofocus
                                />
                            </div>
                        </div>

                        <!-- Image Upload Section -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest px-1">
                                Category Image (300x200)
                            </label>
                            <div 
                                @click="$refs.fileInput.click()"
                                class="relative group cursor-pointer"
                            >
                                <div v-if="!previewImage" class="flex flex-col items-center justify-center w-full py-12 bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl group-hover:border-indigo-300 group-hover:bg-indigo-50/30 transition-all duration-200">
                                    <div class="w-16 h-16 rounded-2xl bg-white shadow-sm border border-gray-100 flex items-center justify-center text-gray-400 group-hover:text-indigo-500 transition-colors mb-4">
                                        <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Click to browse image</p>
                                    <p class="text-xs text-gray-400 mt-1">Files up to 2MB supported</p>
                                </div>
                                <div v-else class="relative w-full rounded-2xl overflow-hidden border-2 border-indigo-100 shadow-lg">
                                    <img :src="previewImage" class="w-full h-48 object-cover">
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                        <span class="px-4 py-2 bg-white rounded-xl text-xs font-bold text-gray-900 shadow-xl">
                                            <i class="fas fa-sync-alt mr-2 text-indigo-500"></i> Change Image
                                        </span>
                                    </div>
                                    <div class="absolute top-3 right-3 px-3 py-1 bg-emerald-500 text-white text-[10px] font-bold uppercase tracking-widest rounded-full shadow-lg">
                                        <i class="fas fa-check mr-1"></i> Resized
                                    </div>
                                </div>
                                <input type="file" ref="fileInput" class="hidden" @change="onFileChange" accept="image/*">
                            </div>
                            <canvas ref="canvas" style="display:none;"></canvas>
                        </div>

                        <!-- Actions -->
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4 pt-4">
                            <button 
                                type="submit"
                                :disabled="loading"
                                class="flex-1 group relative flex items-center justify-center px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 disabled:opacity-50 active:scale-95 transition-all duration-200 shadow-lg shadow-indigo-200 overflow-hidden"
                            >
                                <span class="relative z-10 flex items-center">
                                    <i v-if="loading" class="fas fa-circle-notch fa-spin mr-2"></i>
                                    <i v-else class="fas fa-check-circle mr-2 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                                    Publish Category
                                </span>
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"></div>
                            </button>
                            
                            <a href="/admin/permission-types" 
                               class="flex-1 flex items-center justify-center px-8 py-4 bg-white text-gray-700 font-bold rounded-2xl border-2 border-gray-100 hover:border-gray-200 hover:bg-gray-50 active:scale-95 transition-all duration-200">
                                Dismiss
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Footer Help -->
                <div class="bg-gray-50/50 p-6 border-t border-gray-100">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-white flex items-center justify-center border border-gray-200 shadow-sm text-indigo-500">
                            <i class="fas fa-lightbulb text-xs"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-gray-700 uppercase mb-1">System Help</h4>
                            <p class="text-xs text-gray-400 leading-relaxed">
                                Images are automatically resized and optimized to 300x200 pixels to ensure consistent display across the dashboard.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'PermissionTypeCreate',
    data() {
        return {
            name: '',
            imageFile: null,
            previewImage: null,
            loading: false,
        };
    },
    methods: {
        onFileChange(e) {
            const file = e.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = this.$refs.canvas;
                    const ctx = canvas.getContext('2d');
                    
                    // Set target dimensions
                    const targetWidth = 300;
                    const targetHeight = 200;
                    
                    canvas.width = targetWidth;
                    canvas.height = targetHeight;
                    
                    // Draw image with resizing logic (cover fit)
                    const scale = Math.max(targetWidth / img.width, targetHeight / img.height);
                    const x = (targetWidth - img.width * scale) / 2;
                    const y = (targetHeight - img.height * scale) / 2;
                    
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, targetWidth, targetHeight);
                    ctx.drawImage(img, x, y, img.width * scale, img.height * scale);
                    
                    canvas.toBlob((blob) => {
                        this.imageFile = blob;
                        this.previewImage = URL.createObjectURL(blob);
                    }, 'image/jpeg', 0.9);
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        },
        async saveType() {
            if (this.loading) return;
            this.loading = true;
            
            try {
                const formData = new FormData();
                formData.append('permissionType_name', this.name);
                if (this.imageFile) {
                    formData.append('image', this.imageFile, 'type_image.jpg');
                }

                await axios.post('/api/permission-types', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                
                window.location.href = '/admin/permission-types';
            } catch (err) {
                const errorMsg = err.response?.data?.errors 
                    ? Object.values(err.response.data.errors).flat().join('\n')
                    : (err.response?.data?.message || 'Something went wrong');
                alert(errorMsg);
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
/* Scoped styles kept to a minimum as we use Tailwind */
.bg-gray-50\/50 { background-color: rgba(249, 250, 251, 0.5); }
.ls-1 { letter-spacing: 0.05rem; }
</style>
