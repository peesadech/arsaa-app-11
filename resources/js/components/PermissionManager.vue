<template>
    <div class="permission-manager py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header Card -->
                <div class="card border-0 shadow-sm rounded-lg mb-4">
                    <div class="card-body p-4 bg-gradient-primary text-white rounded-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h4 mb-1 font-weight-bold">Permissions Center</h2>
                                <p class="mb-0 opacity-75Section">Manage system-wide access controls and capabilities.</p>
                            </div>
                            <div class="badge badge-light px-3 py-2 rounded-pill text-primary font-weight-bold">
                                {{ permissions.length }} Total
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Card -->
                <div class="card border-0 shadow-sm rounded-lg mb-4">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="mb-0 text-dark">{{ form.id ? 'Edit Permission' : 'Create New Permission' }}</h5>
                    </div>
                    <div class="card-body p-4">
                        <form @submit.prevent="savePermission">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <div class="form-group mb-0">
                                        <label class="small text-uppercase font-weight-bold text-muted mb-2">Permission Name</label>
                                        <div class="input-group input-group-lg bg-light rounded-pill px-3 py-1">
                                            <div class="input-group-prepend border-0">
                                                <span class="input-group-text bg-transparent border-0 text-muted">
                                                    <i class="fas fa-shield-alt"></i>
                                                </span>
                                            </div>
                                            <input
                                                v-model="form.name"
                                                class="form-control border-0 bg-transparent shadow-none"
                                                placeholder="e.g. edit-posts"
                                                required
                                            />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3 mt-md-0 d-flex justify-content-md-end">
                                    <button 
                                        class="btn btn-primary px-4 py-2 rounded-pill font-weight-bold shadow-sm transition-all" 
                                        :class="{'btn-success': form.id}"
                                        type="submit"
                                        :disabled="loading"
                                    >
                                        <span v-if="loading" class="spinner-border spinner-border-sm mr-2"></span>
                                        {{ form.id ? 'Update Changes' : 'Add Permission' }}
                                    </button>
                                    <button
                                        v-if="form.id"
                                        type="button"
                                        class="btn btn-outline-secondary px-4 py-2 rounded-pill ml-2 font-weight-bold border-0"
                                        @click="resetForm"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- List Card -->
                <div class="card border-0 shadow-sm rounded-lg">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                    <tr>
                                        <th class="px-4 py-3 border-0">ID</th>
                                        <th class="px-4 py-3 border-0">Permission Name</th>
                                        <th class="px-4 py-3 border-0 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(permission, index) in permissions" :key="permission.id" class="transition-all">
                                        <td class="px-4 py-3 align-middle text-muted">{{ permission.id }}</td>
                                        <td class="px-4 py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="permission-icon mr-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-lock-open text-primary small"></i>
                                                </div>
                                                <span class="font-weight-600 text-dark">{{ permission.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-right">
                                            <div class="btn-group">
                                                <button
                                                    class="btn btn-sm btn-icon-only rounded-circle border-0 text-primary bg-light-primary mr-2"
                                                    title="Edit"
                                                    @click="editPermission(permission)"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-icon-only rounded-circle border-0 text-danger bg-light-danger"
                                                    title="Delete"
                                                    @click="deletePermission(permission)"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!permissions.length && !loading">
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-ghost fa-3x mb-3 opacity-25"></i>
                                                <p>No permissions found. Create your first one above!</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
    name: 'PermissionManager',
    data() {
        return {
            permissions: [],
            loading: false,
            form: {
                id: null,
                name: '',
            },
        };
    },
    created() {
        this.fetchPermissions();
    },
    methods: {
        async fetchPermissions() {
            this.loading = true;
            try {
                const res = await axios.get('/api/permissions');
                this.permissions = res.data;
            } catch (err) {
                console.error('Failed to fetch permissions', err);
            } finally {
                this.loading = false;
            }
        },
        editPermission(permission) {
            this.form.id = permission.id;
            this.form.name = permission.name;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        resetForm() {
            this.form.id = null;
            this.form.name = '';
        },
        async savePermission() {
            this.loading = true;
            try {
                if (this.form.id) {
                    await axios.put(`/api/permissions/${this.form.id}`, {
                        name: this.form.name,
                    });
                } else {
                    await axios.post('/api/permissions', {
                        name: this.form.name,
                    });
                }
                this.resetForm();
                await this.fetchPermissions();
            } catch (err) {
                alert(err.response?.data?.message || 'Something went wrong');
            } finally {
                this.loading = false;
            }
        },
        async deletePermission(permission) {
            if (!confirm('Delete permission "' + permission.name + '"? This action cannot be undone.')) return;
            
            this.loading = true;
            try {
                await axios.delete(`/api/permissions/${permission.id}`);
                await this.fetchPermissions();
            } catch (err) {
                alert(err.response?.data?.message || 'Failed to delete permission');
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.bg-light-primary { background-color: #eef2ff; }
.bg-light-danger { background-color: #fff1f2; }
.transition-all { transition: all 0.3s ease; }
.rounded-lg { border-radius: 1rem !important; }
.font-weight-600 { font-weight: 600; }
.btn-icon-only {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-icon-only:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}
.table-hover tbody tr:hover {
    background-color: #f8fafc;
}
.shadow-none:focus {
    box-shadow: none !important;
}
</style>

