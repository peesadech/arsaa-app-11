<template>
    <div class="permission-type-manager py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header Card -->
                <div class="card border-0 shadow-sm rounded-lg mb-4">
                    <div class="card-body p-4 bg-gradient-success text-white rounded-lg">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h2 class="h4 mb-1 font-weight-bold">Permission Types</h2>
                                <p class="mb-0 opacity-75">Categorize your permissions with custom types.</p>
                            </div>
                            <div class="badge badge-light px-3 py-2 rounded-pill text-success font-weight-bold">
                                {{ permissionTypes.length }} Total
                            </div>
                        </div>
                    </div>
                </div>

                <!-- List Card -->
                <div class="card border-0 shadow-sm rounded-lg mt-4">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-dark font-weight-bold ls-1 text-uppercase small text-muted">Master List</h5>
                        <div class="text-right">
                             <span class="badge badge-light px-3 py-2 rounded-pill text-success h6 mb-0">{{ permissionTypes.length }} Entries</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light text-muted small text-uppercase font-weight-bold">
                                    <tr>
                                        <th class="px-4 py-3 border-0">ID</th>
                                        <th class="px-4 py-3 border-0">Type Name</th>
                                        <th class="px-4 py-3 border-0 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="type in permissionTypes" :key="type.permissionType_id" class="transition-all">
                                        <td class="px-4 py-3 align-middle text-muted">{{ type.permissionType_id }}</td>
                                        <td class="px-4 py-3 align-middle">
                                            <div class="d-flex align-items-center">
                                                <div class="icon-box mr-3 rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                    <i class="fas fa-layer-group text-success small"></i>
                                                </div>
                                                <span class="font-weight-600 text-dark">{{ type.permissionType_name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 align-middle text-right">
                                            <div class="btn-group">
                                                <button
                                                    class="btn btn-sm btn-icon-only rounded-circle border-0 text-success bg-light-success mr-2"
                                                    title="Edit"
                                                    @click="editType(type)"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-icon-only rounded-circle border-0 text-danger bg-light-danger"
                                                    title="Delete"
                                                    @click="deleteType(type)"
                                                >
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!permissionTypes.length && !loading">
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                                <p>No types found. Create your first one above!</p>
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
    name: 'PermissionTypeManager',
    data() {
        return {
            permissionTypes: [],
            loading: false,
            form: {
                id: null,
                name: '',
            },
        };
    },
    created() {
        this.fetchTypes();
    },
    methods: {
        async fetchTypes() {
            this.loading = true;
            try {
                const res = await axios.get('/api/permission-types');
                this.permissionTypes = res.data;
            } catch (err) {
                console.error('Failed to fetch types', err);
            } finally {
                this.loading = false;
            }
        },
        editType(type) {
            this.form.id = type.permissionType_id;
            this.form.name = type.permissionType_name;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        resetForm() {
            this.form.id = null;
            this.form.name = '';
        },
        async saveType() {
            this.loading = true;
            try {
                if (this.form.id) {
                    await axios.put(`/api/permission-types/${this.form.id}`, {
                        permissionType_name: this.form.name,
                    });
                } else {
                    await axios.post('/api/permission-types', {
                        permissionType_name: this.form.name,
                    });
                }
                this.resetForm();
                await this.fetchTypes();
            } catch (err) {
                alert(err.response?.data?.message || 'Something went wrong');
            } finally {
                this.loading = false;
            }
        },
        async deleteType(type) {
            if (!confirm('Delete type "' + type.permissionType_name + '"?')) return;
            
            this.loading = true;
            try {
                await axios.delete(`/api/permission-types/${type.permissionType_id}`);
                await this.fetchTypes();
            } catch (err) {
                alert(err.response?.data?.message || 'Failed to delete type');
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.bg-gradient-success {
    background: linear-gradient(135deg, #2dce89 0%, #2dcecc 100%);
}
.bg-light-success { background-color: #e6fffa; }
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
