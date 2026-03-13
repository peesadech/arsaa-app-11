<template>
    <div class="role-manager py-4">
        <div class="row">
            <!-- Roles Section -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-dark font-weight-bold">Role Management</h4>
                        <div class="badge badge-primary px-3 py-2 rounded-pill">{{ roles.length }} Roles</div>
                    </div>
                    <div class="card-body p-4">
                        <form @submit.prevent="createRole" class="mb-4 bg-light p-3 rounded-lg">
                            <h6 class="font-weight-bold text-muted small text-uppercase mb-3">Create New Role</h6>
                            <div class="form-group">
                                <label class="small font-weight-bold">Role Name</label>
                                <input v-model="newRole.name" class="form-control rounded-pill border-0 shadow-sm" placeholder="e.g. editor" required />
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Assign Permissions</label>
                                <select v-model="newRole.selectedPermissions" multiple class="form-control rounded-lg border-0 shadow-sm" style="height: 120px;">
                                    <option v-for="permission in permissions" :key="permission.id" :value="permission.name">
                                        {{ permission.name }}
                                    </option>
                                </select>
                                <small class="form-text text-muted mt-2">Hold Ctrl/Cmd to select multiple.</small>
                            </div>
                            <button class="btn btn-primary btn-block rounded-pill font-weight-bold shadow-sm" type="submit">
                                Create Role
                            </button>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="small text-muted text-uppercase font-weight-bold">
                                    <tr>
                                        <th>Name</th>
                                        <th>Permissions</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="role in roles" :key="role.id">
                                        <td class="align-middle font-weight-bold">{{ role.name }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex flex-wrap">
                                                <span
                                                    v-for="p in role.permissions"
                                                    :key="p.id"
                                                    class="badge badge-info mr-1 mb-1 px-2 py-1 rounded-pill"
                                                >
                                                    {{ p.name }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="align-middle text-right">
                                            <button class="btn btn-sm btn-icon-only text-danger bg-light-danger border-0 rounded-circle" @click="deleteRole(role)">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users & Roles Section -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm rounded-lg h-100">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h4 class="mb-0 text-dark font-weight-bold">User Assignments</h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="small text-muted text-uppercase font-weight-bold">
                                    <tr>
                                        <th>User</th>
                                        <th>Roles Assignment</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="user in users" :key="user.id">
                                        <td class="align-middle">
                                            <div class="font-weight-bold">{{ user.name }}</div>
                                            <div class="small text-muted">{{ user.email }}</div>
                                        </td>
                                        <td class="align-middle">
                                            <select
                                                multiple
                                                class="form-control rounded-lg border-0 bg-light shadow-sm"
                                                style="height: 100px; font-size: 0.85rem;"
                                                v-model="user.selectedRoles"
                                                @change="updateUserRoles(user)"
                                            >
                                                <option v-for="role in roles" :key="role.id" :value="role.name">
                                                    {{ role.name }}
                                                </option>
                                            </select>
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
    name: 'RoleManager',
    data() {
        return {
            roles: [],
            permissions: [],
            users: [],
            newRole: {
                name: '',
                selectedPermissions: [],
            },
            newPermissionName: '',
        };
    },
    created() {
        this.fetchData();
    },
    methods: {
        async fetchData() {
            const [rolesRes, usersRes, permissionsRes] = await Promise.all([
                axios.get('/api/roles'),
                axios.get('/api/users-with-roles'),
                axios.get('/api/permissions'),
            ]);

            this.roles = rolesRes.data.roles;
            this.permissions = permissionsRes.data;
            this.users = usersRes.data.map((u) => ({
                ...u,
                selectedRoles: u.roles.map((r) => r.name),
            }));
        },
        async createPermission() {
            await axios.post('/api/permissions', {
                name: this.newPermissionName,
            });
            this.newPermissionName = '';
            await this.fetchData();
        },
        async deletePermission(permission) {
            if (!confirm('Delete permission ' + permission.name + '?')) return;
            await axios.delete(`/api/permissions/${permission.id}`);
            await this.fetchData();
        },
        async createRole() {
            await axios.post('/api/roles', {
                name: this.newRole.name,
                permissions: this.newRole.selectedPermissions,
            });

            this.newRole.name = '';
            this.newRole.selectedPermissions = [];
            await this.fetchData();
        },
        async deleteRole(role) {
            if (!confirm('Delete role ' + role.name + '?')) return;
            await axios.delete(`/api/roles/${role.id}`);
            await this.fetchData();
        },
        async updateUserRoles(user) {
            await axios.put(`/api/users/${user.id}/roles`, {
                roles: user.selectedRoles,
            });
        },
    },
};
</script>

