import PermissionGroupEdit from '../../../../pages/enso/system/permissionGroups/Edit.vue';

export default {
    name: 'system.permissionGroups.edit',
    path: ':id/edit',
    component: PermissionGroupEdit,
    meta: {
        breadcrumb: 'edit',
        title: 'Edit Permission Group',
    },
};
